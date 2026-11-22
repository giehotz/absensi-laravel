<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ujian\ImportJadwalExcelRequest;
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
use App\Services\Ujian\JadwalExcelImportService;
use App\Services\Ujian\JadwalExcelTemplateService;
use App\Services\Ujian\JadwalLevelSyncService;
use App\Services\Ujian\JadwalPdfParserService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
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

        $pemetaanConfigs = $activePeriode?->kelompokConfigs->keyBy('kelompok') ?? collect();

        return view('ujian.jadwal.index', compact('periodes', 'activePeriode', 'allJadwals', 'jadwals', 'subjects', 'classes', 'classLevels', 'sesis', 'bentukUjianList', 'selectedTingkat', 'coverageData', 'pemetaanConfigs'));
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
        $legendMapel = [];
        $roomCodes = [];
        $ketuaPanitia = null;
        $ttdSetting = null;

        if ($activePeriode) {
            $jadwalsQuery = UjianJadwal::with(['subject', 'schoolClass', 'pengawas.ruang.schoolClass', 'pengawas.pengawas1.user'])
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
                $legendMapel = $rekapData['legend_mapel'] ?? [];
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

            $sekretarisPanitia = UjianPanitia::with('teacher.user')
                ->where('ujian_periode_id', $activePeriode->id)
                ->where(function ($q) {
                    $q->where('jabatan', 'sekretaris')
                        ->orWhere('jabatan', 'Sekretaris')
                        ->orWhere('jabatan', 'like', '%sekretaris%');
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

        $namaSekretaris = $sekretarisPanitia?->teacher?->user?->name ?? '....................................................';
        $nipSekretaris = $sekretarisPanitia?->teacher?->nip;

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
            'legendMapel',
            'roomCodes',
            'namaKepala',
            'nipKepala',
            'namaKetua',
            'nipKetua',
            'namaSekretaris',
            'nipSekretaris',
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
                return strtoupper(preg_replace('/\s+/', '', str_ireplace('kelas', '', $r->schoolClass->name)));
            }

            return strtoupper(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $r->nama_ruang ?: $r->kode_ruang)));
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

        // Petakan roomCode ke ID SchoolClass agar pencocokan jadwal per-rombel tepat sasaran
        $roomClassMap = [];
        foreach ($ruangs as $r) {
            $code = $r->schoolClass
                ? strtoupper(preg_replace('/\s+/', '', str_ireplace('kelas', '', $r->schoolClass->name)))
                : strtoupper(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $r->nama_ruang ?: $r->kode_ruang)));
            if ($code && $r->school_class_id) {
                $roomClassMap[$code] = $r->school_class_id;
            }
        }
        $allClasses = SchoolClass::all();
        foreach ($allClasses as $c) {
            $code = strtoupper(preg_replace('/\s+/', '', str_ireplace('kelas', '', $c->name)));
            if ($code && ! isset($roomClassMap[$code])) {
                $roomClassMap[$code] = $c->id;
            }
        }

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

        // Hitung berapa baris slot untuk setiap tanggal agar bisa di-rowspan (merge)
        $dateRowCounts = [];
        foreach ($grouped as $key => $slotJadwals) {
            $firstJadwal = $slotJadwals->first();
            $tgl = $firstJadwal->tanggal_ujian instanceof Carbon ? $firstJadwal->tanggal_ujian : Carbon::parse($firstJadwal->tanggal_ujian);
            $tglStr = $tgl->format('Y-m-d');
            $dateRowCounts[$tglStr] = ($dateRowCounts[$tglStr] ?? 0) + 1;
        }

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

            $mapels12 = $slotJadwals->filter(function ($j) {
                $lvls = $j->getTargetLevels();

                return in_array('1', $lvls, true) || in_array('2', $lvls, true);
            })->map(fn ($j) => $j->nama_mapel)->filter()->unique()->values();

            $mapel1 = $slotJadwals->filter(function ($j) {
                return in_array('1', $j->getTargetLevels(), true);
            })->map(fn ($j) => $j->nama_mapel)->filter()->unique()->values()->implode(' / ');

            $mapel2 = $slotJadwals->filter(function ($j) {
                return in_array('2', $j->getTargetLevels(), true);
            })->map(fn ($j) => $j->nama_mapel)->filter()->unique()->values()->implode(' / ');

            $mapel12Text = $mapels12->isEmpty()
                ? null
                : ($mapels12->count() <= 2 ? $mapels12->implode(' / ') : $mapels12->take(2)->implode(' / ').' dll');

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
                $targetClassId = $roomClassMap[$rCode] ?? null;

                // 1. Prioritas utama: jadwal spesifik untuk rombel / school_class ini
                $jRombel = $slotJadwals->first(function ($j) use ($rCode, $targetClassId) {
                    if ($targetClassId && $j->school_class_id && (int) $j->school_class_id === (int) $targetClassId) {
                        return true;
                    }

                    if ($j->schoolClass) {
                        $clean = strtoupper(preg_replace('/\s+/', '', str_ireplace('kelas', '', $j->schoolClass->name)));
                        if ($clean === $rCode) {
                            return true;
                        }
                    }

                    return false;
                });

                // 2. Prioritas kedua: jadwal umum tingkat (HANYA jika jadwal tersebut BUKAN jadwal spesifik rombel lain)
                if (! $jRombel) {
                    $jRombel = $slotJadwals->first(function ($j) use ($level) {
                        if ($j->school_class_id !== null) {
                            return false; // Jangan gunakan jadwal rombel lain sebagai fallback
                        }

                        return in_array((string) $level, array_map('strval', $j->getTargetLevels()), true);
                    });
                }

                if ($showMapelOnly) {
                    // Tampilkan nama mata pelajaran rombel jika terjadwal
                    $roomAssignments[$rCode] = $jRombel ? ($jRombel->nama_mapel ?: '—') : null;
                } else {
                    if (! $jRombel) {
                        $roomAssignments[$rCode] = null;

                        continue;
                    }

                    $kodeMapel = $jRombel->subject?->code
                        ?: ($jRombel->nama_mapel ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jRombel->nama_mapel), 0, 4)) : '—');

                    // Cari penugasan pengawas untuk ruangan / rombel ini
                    $pw = $slotPengawas->first(function ($p) use ($rCode, $targetClassId) {
                        if (! $p->ruang) {
                            return false;
                        }
                        if ($targetClassId && $p->ruang->school_class_id && (int) $p->ruang->school_class_id === (int) $targetClassId) {
                            return true;
                        }

                        $rName = strtoupper(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $p->ruang->nama_ruang ?: '')));
                        $rKode = strtoupper(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $p->ruang->kode_ruang ?: '')));

                        return $rName === $rCode || $rKode === $rCode || str_contains($rName, $rCode) || str_contains($rKode, $rCode);
                    });

                    if ($pw && $pw->pengawas1?->user) {
                        $tId = $pw->pengawas1->id;
                        $code = $teacherCodeMap[$tId] ?? $pw->pengawas1->user->id;
                        $roomAssignments[$rCode] = [
                            'mapel' => $kodeMapel,
                            'pengawas' => (string) $code,
                        ];
                        $legendCounts[$code] = ($legendCounts[$code] ?? 0) + 1;
                        $legendNames[$code] = $pw->pengawas1->user->name;
                    } else {
                        $roomAssignments[$rCode] = [
                            'mapel' => $kodeMapel,
                            'pengawas' => '-',
                        ];
                    }
                }
            }

            $rows[] = [
                'no' => $isNewDate ? $noUrut++ : '',
                'is_new_date' => $isNewDate,
                'date_rowspan' => $dateRowCounts[$tglStr] ?? 1,
                'tanggal' => $tgl,
                'sesi_ke' => $sesiKe,
                'pukul' => "{$jamMulai} - {$jamSelesai}",
                'mapel_1' => $mapel1 ?: '—',
                'mapel_2' => $mapel2 ?: '—',
                'mapel_12' => $mapel12Text,
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

        $distinctMapels = [];
        foreach ($jadwals as $j) {
            $code = $j->subject?->code
                ?: ($j->nama_mapel ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $j->nama_mapel), 0, 4)) : '—');
            $name = $j->nama_mapel ?: '-';
            if ($code && $code !== '—' && ! isset($distinctMapels[$code])) {
                $distinctMapels[$code] = $name;
            }
        }
        ksort($distinctMapels);
        $legendMapel = [];
        foreach ($distinctMapels as $code => $name) {
            $legendMapel[] = [
                'code' => $code,
                'nama' => $name,
            ];
        }

        return [
            'rows' => $rows,
            'legend' => $legend,
            'legend_mapel' => $legendMapel,
            'room_codes' => $roomCodes,
        ];
    }

    public function downloadTemplate(Request $request, JadwalExcelTemplateService $templateService): StreamedResponse
    {
        $kelompok = $request->query('kelompok');

        if (! $kelompok) {
            $spreadsheet = $templateService->buildLegacy();
            $filename = 'template_jadwal_ujian.xlsx';
        } else {
            $periodeId = $request->query('periode_id');
            $periode = $periodeId
                ? UjianPeriode::find($periodeId)
                : (UjianPeriode::where('is_active', true)->first() ?: UjianPeriode::first());

            if (! $periode) {
                abort(404, 'Periode ujian tidak ditemukan.');
            }

            $config = $periode->getKelompokConfig($kelompok);
            $spreadsheet = $templateService->build($periode, $kelompok, $config);
            $filename = $templateService->filename($periode, $kelompok);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
        ]);
    }

    public function import(
        ImportJadwalExcelRequest $request,
        JadwalExcelImportService $importService
    ): RedirectResponse {
        try {
            $result = $importService->import(
                $request->file('file'),
                (int) $request->input('ujian_periode_id')
            );

            if ($result['imported'] > 0) {
                $msg = "Berhasil mengimpor {$result['imported']} slot jadwal ujian ({$result['format']}).";
                if (($result['replaced'] ?? 0) > 0) {
                    $msg .= " Menimpa {$result['replaced']} slot jadwal sebelumnya.";
                }
                if (! empty($result['skipped'])) {
                    $msg .= ' Namun '.count($result['skipped']).' baris dilewati: '.implode('; ', array_slice($result['skipped'], 0, 3));
                }

                return back()->with('success', $msg);
            }

            $errMsg = 'Tidak ada jadwal yang berhasil diimpor.';
            if (! empty($result['skipped'])) {
                $errMsg .= ' Alasan: '.implode('; ', array_slice($result['skipped'], 0, 3));
            }

            return back()->with('error', $errMsg);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }
}
