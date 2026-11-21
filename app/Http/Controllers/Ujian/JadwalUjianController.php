<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use App\Models\Ujian\UjianSesi;
use App\Services\Ujian\JadwalConflictService;
use App\Services\Ujian\JadwalLevelSyncService;
use App\Services\Ujian\JadwalPdfParserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JadwalUjianController extends Controller
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected JadwalLevelSyncService $syncService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);
        $selectedTingkat = $request->query('tingkat', 'all');

        $allJadwals = collect();
        if ($activePeriode) {
            $allJadwals = UjianJadwal::with(['subject', 'schoolClass', 'pengawas.ruang'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();
        }

        $jadwals = $allJadwals;
        if ($selectedTingkat !== 'all' && is_numeric($selectedTingkat)) {
            $jadwals = $jadwals->filter(function ($j) use ($selectedTingkat) {
                return in_array((string) $selectedTingkat, $j->getTargetLevels(), true);
            })->values();
        }

        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::ordered()->get();
        $classLevels = $classes->mapWithKeys(function (SchoolClass $schoolClass): array {
            preg_match('/\d+/', $schoolClass->name, $matches);

            return [$schoolClass->id => (string) ($matches[0] ?? $schoolClass->level)];
        });
        $sesis = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get();
        $bentukUjianList = $allJadwals->pluck('jenis_soal')->filter()->unique()->values();

        $coverageData = [
            'subjects' => $subjects->map(fn (Subject $subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
            ])->values(),
            'classes' => $classes->map(fn (SchoolClass $schoolClass) => [
                'id' => $schoolClass->id,
                'name' => $schoolClass->name,
                'level' => $classLevels[$schoolClass->id] ?? (string) $schoolClass->level,
            ])->values(),
            'jadwals' => $allJadwals->map(fn (UjianJadwal $jadwal) => [
                'id' => $jadwal->id,
                'subject_id' => $jadwal->subject_id,
                'nama_mapel_kustom' => $jadwal->nama_mapel_kustom,
                'school_class_id' => $jadwal->school_class_id,
                'school_class' => $jadwal->schoolClass ? ['name' => $jadwal->schoolClass->name] : null,
                'tingkat' => $jadwal->tingkat,
                'target_levels' => $jadwal->getTargetLevels(),
                'class_level' => $jadwal->school_class_id
                    ? ($classLevels[$jadwal->school_class_id] ?? null)
                    : null,
                'tanggal_ujian' => $jadwal->tanggal_ujian?->format('Y-m-d'),
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'sesi_ke' => $jadwal->sesi_ke,
                'durasi_menit' => $jadwal->durasi_menit,
                'jenis_soal' => $jadwal->jenis_soal,
                'catatan' => $jadwal->catatan,
                'nama_mapel' => $jadwal->nama_mapel,
                'target_kelas' => $jadwal->target_kelas,
            ])->values(),
            'selectedTingkat' => (string) $selectedTingkat,
        ];

        return view('ujian.jadwal.index', compact('periodes', 'activePeriode', 'allJadwals', 'jadwals', 'subjects', 'classes', 'classLevels', 'sesis', 'bentukUjianList', 'selectedTingkat', 'coverageData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tingkatInput = $request->input('tingkat');
        $rawLevels = [];
        if (is_array($tingkatInput)) {
            $rawLevels = array_values(array_filter(array_map('trim', $tingkatInput)));
            sort($rawLevels, SORT_NATURAL);
            $request->merge(['tingkat' => ! empty($rawLevels) ? implode(', ', $rawLevels) : null]);
        } else {
            $cleaned = trim((string) $tingkatInput);
            $rawLevels = $cleaned !== '' ? array_values(array_filter(array_map('trim', explode(',', $cleaned)))) : [];
            $request->merge(['tingkat' => $cleaned ?: null]);
        }

        $resolveJenisSoal = function (?string $val, ?string $kustom = null): string {
            $val = trim($val ?? '');
            if ($val === 'Lainnya' || $val === 'Lainnya...' || empty($val)) {
                return ! empty(trim($kustom ?? '')) ? trim($kustom) : 'Tertulis';
            }

            return $val;
        };

        $bawahLevels = array_values(array_intersect($rawLevels, ['1', '2', '3']));
        $atasLevels = array_values(array_intersect($rawLevels, ['4', '5', '6']));
        $isMixed = ! empty($bawahLevels) && ! empty($atasLevels);

        $jenisSoalBawah = $resolveJenisSoal(
            $request->input('jenis_soal_bawah', $request->input('jenis_soal')),
            $request->input('jenis_soal_bawah_kustom', $request->input('jenis_soal_kustom'))
        );
        $jenisSoalAtas = $resolveJenisSoal(
            $request->input('jenis_soal_atas', $request->input('jenis_soal')),
            $request->input('jenis_soal_atas_kustom', $request->input('jenis_soal_kustom'))
        );
        $jenisSoalDefault = $resolveJenisSoal(
            $request->input('jenis_soal', $jenisSoalBawah),
            $request->input('jenis_soal_kustom')
        );

        $request->merge([
            'jenis_soal' => $jenisSoalDefault,
        ]);

        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'nama_mapel_kustom' => ['nullable', 'string', 'max:150'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'tingkat' => ['nullable', 'string', 'max:30'],
            'tanggal_ujian' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'durasi_menit' => ['required', 'integer', 'min:15'],
            'jenis_soal' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['subject_id']) && empty($validated['nama_mapel_kustom'])) {
            return back()->withInput()->with('error', 'Pilih mata pelajaran atau isi nama mata pelajaran kustom.');
        }

        // Jika tingkat campuran dan bentuk ujian kelas bawah vs atas berbeda -> Split 2 slot otomatis
        if ($isMixed && empty($validated['school_class_id']) && $jenisSoalBawah !== $jenisSoalAtas) {
            $tingkatBawahStr = implode(', ', $bawahLevels);
            $tingkatAtasStr = implode(', ', $atasLevels);

            // Validasi bentrok kelas bawah
            $conflictBawah = $this->conflictService->checkClassScheduleConflict(
                (int) $validated['ujian_periode_id'],
                null,
                $tingkatBawahStr,
                $validated['tanggal_ujian'],
                $validated['jam_mulai'],
                $validated['jam_selesai']
            );
            if ($conflictBawah) {
                return back()->withInput()->with(
                    'error',
                    "Bentrok Jadwal Kelas Bawah (Tingkat {$tingkatBawahStr}): sudah ada jadwal '{$conflictBawah->nama_mapel}' pada slot waktu tersebut."
                );
            }

            // Validasi bentrok kelas atas
            $conflictAtas = $this->conflictService->checkClassScheduleConflict(
                (int) $validated['ujian_periode_id'],
                null,
                $tingkatAtasStr,
                $validated['tanggal_ujian'],
                $validated['jam_mulai'],
                $validated['jam_selesai']
            );
            if ($conflictAtas) {
                return back()->withInput()->with(
                    'error',
                    "Bentrok Jadwal Kelas Atas (Tingkat {$tingkatAtasStr}): sudah ada jadwal '{$conflictAtas->nama_mapel}' pada slot waktu tersebut."
                );
            }

            // Simpan slot Kelas Bawah
            $dataBawah = $validated;
            $dataBawah['tingkat'] = $tingkatBawahStr;
            $dataBawah['jenis_soal'] = $jenisSoalBawah;
            UjianJadwal::create($dataBawah);

            // Simpan slot Kelas Atas
            $dataAtas = $validated;
            $dataAtas['tingkat'] = $tingkatAtasStr;
            $dataAtas['jenis_soal'] = $jenisSoalAtas;
            UjianJadwal::create($dataAtas);

            return back()->with(
                'success',
                "2 Slot Jadwal Ujian berhasil dibuat (Kelas Bawah {$tingkatBawahStr}: {$jenisSoalBawah}, Kelas Atas {$tingkatAtasStr}: {$jenisSoalAtas})."
            );
        }

        // Jika tingkat campuran tapi bentuk ujian sama, gunakan bentuk ujian tersebut
        if ($isMixed && empty($validated['school_class_id'])) {
            $validated['jenis_soal'] = $jenisSoalBawah;
        }

        // Cek Bentrok Jadwal Kelas normal
        $conflict = $this->conflictService->checkClassScheduleConflict(
            (int) $validated['ujian_periode_id'],
            ! empty($validated['school_class_id']) ? (int) $validated['school_class_id'] : null,
            $validated['tingkat'] ?? null,
            $validated['tanggal_ujian'],
            $validated['jam_mulai'],
            $validated['jam_selesai']
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Bentrok Jadwal: Rombel/Tingkat tersebut sudah memiliki jadwal '{$conflict->nama_mapel}' pada tanggal {$conflict->tanggal_ujian->format('d/m/Y')} pukul {$conflict->jam_mulai} - {$conflict->jam_selesai}."
            );
        }

        UjianJadwal::create($validated);

        return back()->with('success', 'Jadwal Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, UjianJadwal $jadwal): RedirectResponse
    {
        $tingkatInput = $request->input('tingkat');
        if (is_array($tingkatInput)) {
            $cleaned = array_filter(array_map('trim', $tingkatInput));
            sort($cleaned, SORT_NATURAL);
            $request->merge(['tingkat' => ! empty($cleaned) ? implode(', ', $cleaned) : null]);
        } else {
            $request->merge(['tingkat' => $tingkatInput ?: null]);
        }

        $val = trim($request->input('jenis_soal') ?? '');
        $kustom = trim($request->input('jenis_soal_kustom') ?? '');
        if ($val === 'Lainnya' || $val === 'Lainnya...' || empty($val)) {
            $resolvedJenisSoal = ! empty($kustom) ? $kustom : 'Tertulis';
        } else {
            $resolvedJenisSoal = $val;
        }
        $request->merge(['jenis_soal' => $resolvedJenisSoal]);

        $validated = $request->validate([
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'nama_mapel_kustom' => ['nullable', 'string', 'max:150'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'tingkat' => ['nullable', 'string', 'max:30'],
            'tanggal_ujian' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'durasi_menit' => ['required', 'integer', 'min:15'],
            'jenis_soal' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['subject_id']) && empty($validated['nama_mapel_kustom'])) {
            return back()->withInput()->with('error', 'Pilih mata pelajaran atau isi nama mata pelajaran kustom.');
        }

        $conflict = $this->conflictService->checkClassScheduleConflict(
            $jadwal->ujian_periode_id,
            ! empty($validated['school_class_id']) ? (int) $validated['school_class_id'] : null,
            $validated['tingkat'] ?? null,
            $validated['tanggal_ujian'],
            $validated['jam_mulai'],
            $validated['jam_selesai'],
            $jadwal->id
        );

        if ($conflict) {
            return back()->withInput()->with(
                'error',
                "Bentrok Jadwal: Rombel/Tingkat tersebut sudah memiliki jadwal '{$conflict->nama_mapel}' pada tanggal {$conflict->tanggal_ujian->format('d/m/Y')} pukul {$conflict->jam_mulai} - {$conflict->jam_selesai}."
            );
        }

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal Ujian berhasil diperbarui.');
    }

    public function copySchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'source_class_id' => ['required', 'exists:school_classes,id'],
            'target_class_ids' => ['required', 'array'],
            'target_class_ids.*' => ['exists:school_classes,id'],
        ]);

        $sourceSchedules = UjianJadwal::where('ujian_periode_id', $validated['ujian_periode_id'])
            ->where('school_class_id', $validated['source_class_id'])
            ->get();

        if ($sourceSchedules->isEmpty()) {
            return back()->with('error', 'Tidak ada jadwal pada rombel sumber yang dipilih.');
        }

        $copiedCount = 0;
        foreach ($validated['target_class_ids'] as $targetClassId) {
            if ($targetClassId == $validated['source_class_id']) {
                continue;
            }

            foreach ($sourceSchedules as $source) {
                // Periksa apakah jadwal sudah ada
                $exists = UjianJadwal::where('ujian_periode_id', $source->ujian_periode_id)
                    ->where('school_class_id', $targetClassId)
                    ->where('subject_id', $source->subject_id)
                    ->where('tanggal_ujian', $source->tanggal_ujian)
                    ->exists();

                if (! $exists) {
                    $newSchedule = $source->replicate();
                    $newSchedule->school_class_id = $targetClassId;
                    $newSchedule->save();
                    $copiedCount++;
                }
            }
        }

        return back()->with('success', "Berhasil menduplikasi {$copiedCount} slot jadwal ke rombel tujuan.");
    }

    /**
     * Preview penyalinan jadwal antar-tingkat (AJAX).
     */
    public function previewCopyLevels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'source_level' => ['required', 'integer', 'between:1,6'],
            'target_levels' => ['required', 'array', 'min:1'],
            'target_levels.*' => ['integer', 'between:1,6'],
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $preview = $this->syncService->getSyncPreview($periode, (int) $validated['source_level'], $validated['target_levels']);

        return response()->json($preview);
    }

    /**
     * Eksekusi penyalinan jadwal antar-tingkat dengan mode skip/replace.
     */
    public function copyLevels(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'source_level' => ['required', 'integer', 'between:1,6'],
            'target_levels' => ['required', 'array', 'min:1'],
            'target_levels.*' => ['integer', 'between:1,6'],
            'mode' => ['required', 'in:skip,replace'],
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $result = $this->syncService->syncFromLevel(
            $periode,
            (int) $validated['source_level'],
            $validated['target_levels'],
            $validated['mode']
        );

        $msg = "Penyalinan jadwal antar-tingkat selesai: {$result['copied']} slot berhasil disalin";
        if ($result['replaced'] > 0) {
            $msg .= ", {$result['replaced']} slot ditimpa";
        }
        if ($result['skipped'] > 0) {
            $msg .= ", {$result['skipped']} slot dilewati";
        }
        $msg .= '.';

        return back()->with('success', $msg);
    }

    public function destroy(UjianJadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal Ujian berhasil dihapus.');
    }

    public function print(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);
        $format = $request->query('format', 'standar');
        $kelompok = $request->query('kelompok', 'all');

        $jadwals = collect();
        $rekapRows = [];
        $legendPengawas = [];
        $roomCodes = [];
        $ketuaPanitia = null;
        $ttdSetting = null;

        if ($activePeriode) {
            $jadwalsQuery = UjianJadwal::with(['subject', 'schoolClass', 'pengawas.ruang', 'pengawas.pengawas1.user'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai');

            if ($kelompok === '1-2') {
                $jadwals = $jadwalsQuery->get()->filter(function ($j) {
                    $lvls = $j->getTargetLevels();

                    return in_array('1', $lvls, true) || in_array('2', $lvls, true);
                })->values();
            } elseif ($kelompok === '3-6') {
                $jadwals = $jadwalsQuery->get()->filter(function ($j) {
                    $lvls = $j->getTargetLevels();

                    return count(array_intersect(['3', '4', '5', '6'], $lvls)) > 0;
                })->values();
            } else {
                $jadwals = $jadwalsQuery->get();
            }

            if (in_array($format, ['rekap', 'matriks_rombel'], true)) {
                $rekapData = $this->buildRekapMatrixData($jadwals, $activePeriode, $kelompok, $format === 'matriks_rombel');
                $rekapRows = $rekapData['rows'];
                $legendPengawas = $rekapData['legend'];
                $roomCodes = $rekapData['room_codes'];
            }

            $ketuaPanitia = UjianPanitia::with('teacher.user')
                ->where('ujian_periode_id', $activePeriode->id)
                ->where(function ($q) {
                    $q->where('jabatan', 'ketua')
                        ->orWhere('jabatan', 'Ketua')
                        ->orWhere('jabatan', 'like', '%ketua%');
                })
                ->where('is_active', true)
                ->first();

            $ttdSetting = UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first();
        }

        $setting = AttendanceSetting::first();

        $namaKepala = $ttdSetting?->nama_kepala
            ?? ($setting?->card_principal_name
            ?? ($setting?->headmasterTeacher?->user?->name ?? 'Sipulloh, M.Pd'));

        $nipKepala = $ttdSetting?->nip_kepala
            ?? ($setting?->card_principal_nip
            ?? ($setting?->headmasterTeacher?->nip ?? '-'));

        $namaKetua = $ketuaPanitia?->teacher?->user?->name ?? '....................................................';
        $nipKetua = $ketuaPanitia?->teacher?->nip ?? '-';

        $tempatCetak = $activePeriode?->tempat_cetak
            ?? ($ttdSetting?->tempat_penetapan
            ?? ($setting?->school_city ?? 'Tanggamus'));

        $tanggalCetak = $activePeriode?->tanggal_cetak_kartu ?? now();

        return view('ujian.jadwal.print', compact(
            'activePeriode',
            'jadwals',
            'setting',
            'format',
            'kelompok',
            'rekapRows',
            'legendPengawas',
            'roomCodes',
            'namaKepala',
            'nipKepala',
            'namaKetua',
            'nipKetua',
            'tempatCetak',
            'tanggalCetak'
        ));
    }

    public function importPdf(Request $request, JadwalPdfParserService $parserService): RedirectResponse
    {
        $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'ujian_periode_id.required' => 'Periode ujian wajib dipilih.',
            'file.required' => 'File PDF wajib diunggah.',
            'file.mimes' => 'Format file harus berupa dokumen PDF (.pdf).',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        try {
            $result = $parserService->parseAndImport(
                $request->file('file'),
                (int) $request->input('ujian_periode_id')
            );

            $msg = "Berhasil mengimpor {$result['imported_jadwal']} slot jadwal dan {$result['imported_pengawas']} penugasan pengawas dari file PDF.";
            if (! empty($result['skipped'])) {
                $msg .= ' Catatan: '.implode('; ', array_slice($result['skipped'], 0, 3));
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file PDF: '.$e->getMessage());
        }
    }

    /**
     * Membentuk struktur data matriks jadwal dinamis (Sheet 1 matriks rombel atau Sheet matriks kode pengawas).
     *
     * @param  Collection<int, UjianJadwal>  $jadwals
     * @return array{rows: array<int, mixed>, legend: array<int, mixed>, room_codes: array<int, string>}
     */
    protected function buildRekapMatrixData($jadwals, ?UjianPeriode $activePeriode = null, string $kelompok = 'all', bool $showMapelOnly = false): array
    {
        $ruangs = UjianRuang::with('schoolClass')->orderBy('kode_ruang')->get();

        $roomCodes = $ruangs->map(function ($r) {
            if ($r->schoolClass) {
                return preg_replace('/\s+/', '', str_ireplace('kelas', '', $r->schoolClass->name));
            }

            return preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $r->nama_ruang ?: $r->kode_ruang));
        })->unique()->filter()->values()->all();

        if (empty($roomCodes)) {
            $roomCodes = ['1A', '1B', '2A', '2B', '3A', '3B', '3C', '4A', '4B', '5A', '5B', '6A', '6B', '6C'];
        }

        if ($kelompok === '1-2') {
            $roomCodes = array_values(array_filter($roomCodes, fn ($rc) => str_starts_with($rc, '1') || str_starts_with($rc, '2')));
        } elseif ($kelompok === '3-6') {
            $roomCodes = array_values(array_filter($roomCodes, fn ($rc) => in_array(substr($rc, 0, 1), ['3', '4', '5', '6'], true)));
        }

        natsort($roomCodes);
        $roomCodes = array_values($roomCodes);

        // Kumpulkan semua pengawas di seluruh jadwal ini untuk memberi nomor kode 1..N
        $allAssignedPengawas = collect();
        foreach ($jadwals as $j) {
            foreach ($j->pengawas as $p) {
                if ($p->pengawas1?->user) {
                    $allAssignedPengawas->push($p->pengawas1);
                }
            }
        }
        $distinctTeachers = $allAssignedPengawas->unique('id')->sortBy(fn ($t) => $t->user?->name ?? '')->values();
        $teacherCodeMap = [];
        foreach ($distinctTeachers as $idx => $t) {
            $teacherCodeMap[$t->id] = $idx + 1; // Kode 1..N
        }

        // Kelompokkan jadwal berdasarkan Tanggal dan Jam Mulai
        $grouped = $jadwals->groupBy(function ($j) {
            $tgl = $j->tanggal_ujian instanceof Carbon ? $j->tanggal_ujian->format('Y-m-d') : (string) $j->tanggal_ujian;

            return $tgl.'_'.substr($j->jam_mulai, 0, 5);
        });

        $rows = [];
        $legendCounts = [];
        $legendNames = [];
        $noUrut = 1;
        $prevDate = null;

        foreach ($grouped as $key => $slotJadwals) {
            $firstJadwal = $slotJadwals->first();
            $tgl = $firstJadwal->tanggal_ujian instanceof Carbon ? $firstJadwal->tanggal_ujian : Carbon::parse($firstJadwal->tanggal_ujian);
            $tglStr = $tgl->format('Y-m-d');
            $isNewDate = ($tglStr !== $prevDate);
            $prevDate = $tglStr;

            $jamMulai = substr($firstJadwal->jam_mulai, 0, 5);
            $jamSelesai = substr($firstJadwal->jam_selesai, 0, 5);
            $sesiKe = $firstJadwal->sesi_ke ?? ($jamMulai === '07:30' ? 1 : 2);

            $jadwal12 = $slotJadwals->first(function ($j) {
                $lvls = $j->getTargetLevels();

                return in_array('1', $lvls, true) || in_array('2', $lvls, true);
            });

            $jadwal36 = $slotJadwals->first(function ($j) {
                $lvls = $j->getTargetLevels();

                return count(array_intersect(['3', '4', '5', '6'], $lvls)) > 0;
            });

            $slotPengawas = collect();
            foreach ($slotJadwals as $sj) {
                foreach ($sj->pengawas as $pw) {
                    $slotPengawas->push($pw);
                }
            }

            $roomAssignments = [];
            foreach ($roomCodes as $rCode) {
                $level = substr($rCode, 0, 1);
                $hasExam = ($level <= 2 && $jadwal12) || ($level >= 3 && $jadwal36);

                if (! $hasExam) {
                    $roomAssignments[$rCode] = null;

                    continue;
                }

                if ($showMapelOnly) {
                    // Tampilkan mata pelajaran untuk rombel ini
                    $jRombel = $slotJadwals->first(function ($j) use ($rCode, $level) {
                        if ($j->schoolClass) {
                            $clean = preg_replace('/\s+/', '', str_ireplace('kelas', '', $j->schoolClass->name));
                            if (strcasecmp($clean, $rCode) === 0) {
                                return true;
                            }
                        }

                        return in_array($level, $j->getTargetLevels(), true);
                    });
                    $roomAssignments[$rCode] = $jRombel?->nama_mapel ?? '-';
                } else {
                    // Cari penugasan pengawas
                    $pw = $slotPengawas->first(function ($p) use ($rCode) {
                        if (! $p->ruang) {
                            return false;
                        }
                        $rName = strtolower(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $p->ruang->nama_ruang ?: '')));
                        $rKode = strtolower(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $p->ruang->kode_ruang ?: '')));
                        $target = strtolower($rCode);

                        return str_contains($rName, $target) || str_contains($rKode, $target);
                    });

                    if ($pw && $pw->pengawas1?->user) {
                        $tId = $pw->pengawas1->id;
                        $code = $teacherCodeMap[$tId] ?? $pw->pengawas1->user->id;
                        $roomAssignments[$rCode] = $code;
                        $legendCounts[$code] = ($legendCounts[$code] ?? 0) + 1;
                        $legendNames[$code] = $pw->pengawas1->user->name;
                    } else {
                        $roomAssignments[$rCode] = '-';
                    }
                }
            }

            $rows[] = [
                'no' => $isNewDate ? $noUrut++ : '',
                'is_new_date' => $isNewDate,
                'tanggal' => $tgl,
                'sesi_ke' => $sesiKe,
                'pukul' => "{$jamMulai} - {$jamSelesai}",
                'mapel_12' => $jadwal12?->nama_mapel,
                'mapel_36' => $jadwal36?->nama_mapel,
                'rooms' => $roomAssignments,
            ];
        }

        $legend = [];
        ksort($legendCounts);
        foreach ($legendCounts as $code => $count) {
            $legend[] = [
                'user_id' => $code,
                'nama' => $legendNames[$code] ?? 'Pengawas',
                'jumlah' => $count,
            ];
        }

        return [
            'rows' => $rows,
            'legend' => $legend,
            'room_codes' => $roomCodes,
        ];
    }

    public function downloadTemplate(Request $request): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Ujian');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Matapelajaran',
            'C1' => 'tanggal(dd-mm-yyyy)',
            'D1' => 'sesi',
            'E1' => 'Kelas',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Header Styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'], // Kuning Neo-Brutalist
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Ambil Data Referensi Mapel & Sesi
        $subjects = Subject::orderBy('name')->pluck('name')->toArray();
        $sesis = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get();

        // Sample Data
        $sampleDate = now()->addDays(7)->format('d-m-Y');
        $sampleRows = [
            [1, $subjects[0] ?? "Al-Qur'an Hadis", $sampleDate, 1, '123'],
            [2, $subjects[1] ?? 'Akidah Akhlak', $sampleDate, 2, '456'],
            [3, $subjects[2] ?? 'Bahasa Indonesia', now()->addDays(8)->format('d-m-Y'), 1, '123456'],
        ];

        $rowIdx = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue("A{$rowIdx}", $row[0]);
            $sheet->setCellValue("B{$rowIdx}", $row[1]);
            $sheet->setCellValue("C{$rowIdx}", $row[2]);
            $sheet->setCellValue("D{$rowIdx}", $row[3]);
            $sheet->setCellValue("E{$rowIdx}", $row[4]);
            $rowIdx++;
        }

        // Apply borders & alignment to sample data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A2:E{$rowIdx}")->applyFromArray($dataStyle);
        $sheet->getStyle("A2:A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:E{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Sheet Referensi Bantuan untuk Dropdown Mapel & Petunjuk
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setCellValue('A1', 'Daftar Mata Pelajaran');
        $refSheet->setCellValue('B1', 'Daftar Sesi Ujian');
        $refSheet->setCellValue('C1', 'Petunjuk Format Kelas / Tingkat');
        $refSheet->getStyle('A1:C1')->getFont()->setBold(true);

        $refRow = 2;
        foreach ($subjects as $sName) {
            $refSheet->setCellValue("A{$refRow}", $sName);
            $refRow++;
        }
        $maxSubjectRow = max(2, $refRow - 1);

        $sesiRow = 2;
        foreach ($sesis as $s) {
            $refSheet->setCellValue("B{$sesiRow}", "Sesi {$s->sesi_ke} (".substr($s->jam_mulai, 0, 5).' - '.substr($s->jam_selesai, 0, 5).')');
            $sesiRow++;
        }

        $petunjuk = [
            '123 : Tingkat 1, 2, 3 (Kelas Rendah)',
            '456 : Tingkat 4, 5, 6 (Kelas Atas)',
            '123456 : Semua Tingkat (Kelas 1 s/d 6)',
            '1, 2, 3 : Format koma juga didukung',
            '6A, 6B : Nama rombel spesifik',
        ];
        foreach ($petunjuk as $pIdx => $pText) {
            $refSheet->setCellValue('C'.($pIdx + 2), $pText);
        }

        foreach (range('A', 'C') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Data Validation untuk Kolom B, D, E
        if (! empty($subjects)) {
            for ($r = 2; $r <= 100; $r++) {
                $validation = $sheet->getCell("B{$r}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Pilihan Mata Pelajaran');
                $validation->setError('Pilih mata pelajaran dari daftar atau ketik nama mapel.');
                $validation->setPromptTitle('Mata Pelajaran');
                $validation->setPrompt('Pilih mata pelajaran dari daftar pilihan.');
                $validation->setFormula1("Referensi!\$A\$2:\$A\${$maxSubjectRow}");

                // Data Validation untuk Kolom D (Sesi)
                $validationSesi = $sheet->getCell("D{$r}")->getDataValidation();
                $validationSesi->setType(DataValidation::TYPE_LIST);
                $validationSesi->setErrorStyle(DataValidation::STYLE_STOP);
                $validationSesi->setShowDropDown(true);
                $validationSesi->setFormula1('"1, 2, 3, 4"');

                // Data Validation untuk Kolom E (Kelas)
                $validationKelas = $sheet->getCell("E{$r}")->getDataValidation();
                $validationKelas->setType(DataValidation::TYPE_LIST);
                $validationKelas->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validationKelas->setShowDropDown(true);
                $validationKelas->setFormula1('"123, 456, 123456, 1, 2, 3, 4, 5, 6"');
            }
        }

        // Auto-width columns di sheet utama
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template_jadwal_ujian.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'ujian_periode_id.required' => 'Periode ujian wajib dipilih.',
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return back()->with('error', 'File Excel kosong atau tidak memiliki baris data jadwal.');
            }

            $periodeId = (int) $request->input('ujian_periode_id');
            $sessions = UjianSesi::all()->keyBy('sesi_ke');
            $subjects = Subject::all();
            $classes = SchoolClass::all();

            $importedCount = 0;
            $skipped = [];

            DB::transaction(function () use (
                $rows,
                $periodeId,
                $sessions,
                $subjects,
                $classes,
                &$importedCount,
                &$skipped
            ) {
                foreach ($rows as $index => $row) {
                    if ($index === 1) {
                        continue; // Lewati header
                    }

                    $mapelName = trim((string) ($row['B'] ?? ''));
                    $tanggalRaw = trim((string) ($row['C'] ?? ''));
                    $sesiRaw = trim((string) ($row['D'] ?? '1'));
                    $kelasRaw = trim((string) ($row['E'] ?? ''));

                    // Lewati baris kosong
                    if ($mapelName === '' && $tanggalRaw === '' && $kelasRaw === '') {
                        continue;
                    }

                    if ($mapelName === '' || $tanggalRaw === '' || $kelasRaw === '') {
                        $skipped[] = "Baris {$index}: Kolom Mata Pelajaran, Tanggal, atau Kelas tidak boleh kosong.";

                        continue;
                    }

                    // 1. Parsing Tanggal
                    $tanggalUjian = null;
                    if (is_numeric($tanggalRaw)) {
                        try {
                            $tanggalUjian = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $tanggalRaw));
                        } catch (\Throwable) {
                            $tanggalUjian = null;
                        }
                    } else {
                        try {
                            $tanggalUjian = Carbon::createFromFormat('d-m-Y', $tanggalRaw);
                        } catch (\Throwable) {
                            try {
                                $tanggalUjian = Carbon::parse($tanggalRaw);
                            } catch (\Throwable) {
                                $tanggalUjian = null;
                            }
                        }
                    }

                    if (! $tanggalUjian) {
                        $skipped[] = "Baris {$index}: Format tanggal '{$tanggalRaw}' tidak valid (gunakan format dd-mm-yyyy).";

                        continue;
                    }

                    // 2. Parsing Sesi & Jam Mulai/Selesai
                    $sesiKe = max(1, (int) preg_replace('/\D/', '', $sesiRaw));
                    $sesiObj = $sessions->get($sesiKe) ?? $sessions->first();
                    $jamMulai = $sesiObj ? substr($sesiObj->jam_mulai, 0, 5) : '07:30';
                    $jamSelesai = $sesiObj ? substr($sesiObj->jam_selesai, 0, 5) : '09:30';
                    $durasiMenit = $sesiObj ? (int) $sesiObj->durasi_menit : 120;

                    // 3. Resolusi Mata Pelajaran
                    $matchedSubject = $subjects->first(function ($s) use ($mapelName) {
                        return strcasecmp($s->name, $mapelName) === 0 || strcasecmp($s->code ?? '', $mapelName) === 0;
                    });
                    $subjectId = $matchedSubject?->id;
                    $namaMapelKustom = $matchedSubject ? null : $mapelName;

                    // 4. Resolusi Kelas / Tingkat
                    $schoolClassId = null;
                    $tingkat = null;

                    $matchedClass = $classes->first(function ($c) use ($kelasRaw) {
                        return strcasecmp($c->name, $kelasRaw) === 0;
                    });

                    if ($matchedClass) {
                        $schoolClassId = $matchedClass->id;
                        $tingkat = null;
                    } else {
                        $cleanStr = strtolower(trim($kelasRaw));
                        if (in_array($cleanStr, ['123456', 'semua', 'semua kelas', '1-6'], true)) {
                            $tingkat = '1, 2, 3, 4, 5, 6';
                        } elseif (in_array($cleanStr, ['123', '1-3', 'rendah'], true)) {
                            $tingkat = '1, 2, 3';
                        } elseif (in_array($cleanStr, ['456', '4-6', 'atas'], true)) {
                            $tingkat = '4, 5, 6';
                        } else {
                            preg_match_all('/[1-6]/', $cleanStr, $digits);
                            $uniqueDigits = array_unique($digits[0] ?? []);
                            sort($uniqueDigits, SORT_NATURAL);
                            $tingkat = ! empty($uniqueDigits) ? implode(', ', $uniqueDigits) : $kelasRaw;
                        }
                    }

                    // 5. Cek Bentrok Jadwal Kelas / Tingkat
                    $conflict = $this->conflictService->checkClassScheduleConflict(
                        $periodeId,
                        $schoolClassId,
                        $tingkat,
                        $tanggalUjian->format('Y-m-d'),
                        $jamMulai,
                        $jamSelesai
                    );

                    if ($conflict) {
                        $targetStr = $conflict->schoolClass?->name ?? ($conflict->tingkat ? "Tingkat {$conflict->tingkat}" : 'Semua');
                        $skipped[] = "Baris {$index}: Bentrok dengan jadwal {$conflict->nama_mapel} ({$targetStr}) pada {$tanggalUjian->format('d/m/Y')} ({$jamMulai}-{$jamSelesai}).";

                        continue;
                    }

                    UjianJadwal::create([
                        'ujian_periode_id' => $periodeId,
                        'subject_id' => $subjectId,
                        'nama_mapel_kustom' => $namaMapelKustom,
                        'school_class_id' => $schoolClassId,
                        'tingkat' => $tingkat,
                        'tanggal_ujian' => $tanggalUjian->format('Y-m-d'),
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                        'sesi_ke' => $sesiKe,
                        'durasi_menit' => $durasiMenit,
                        'jenis_soal' => 'Tertulis',
                        'catatan' => 'Import Template Excel',
                    ]);

                    $importedCount++;
                }
            });

            if ($importedCount > 0) {
                $msg = "Berhasil mengimpor {$importedCount} slot jadwal ujian.";
                if (! empty($skipped)) {
                    $msg .= ' Namun '.count($skipped).' baris dilewati: '.implode('; ', array_slice($skipped, 0, 3));
                }

                return back()->with('success', $msg);
            }

            $errMsg = 'Tidak ada jadwal yang berhasil diimpor.';
            if (! empty($skipped)) {
                $errMsg .= ' Alasan: '.implode('; ', array_slice($skipped, 0, 3));
            }

            return back()->with('error', $errMsg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }
}
