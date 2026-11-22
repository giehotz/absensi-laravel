<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPengawasMapping;
use App\Models\Ujian\UjianPengawasSwapHistory;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use App\Services\Ujian\JadwalConflictService;
use App\Services\Ujian\JadwalPdfParserService;
use App\Services\Ujian\PengawasAutoPlotterService;
use App\Services\Ujian\PengawasMappingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalPengawasController extends Controller
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasAutoPlotterService $autoPlotterService,
        protected PengawasMappingService $mappingService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);
        $kelompok = $request->query('kelompok', 'all');

        $targetLevels = match ($kelompok) {
            '1-2' => ['1', '2'],
            '3-6' => ['3', '4', '5', '6'],
            default => null,
        };

        $jadwals = collect();
        $ruangs = UjianRuang::orderBy('kode_ruang')->get();
        $pengawasList = collect();
        $mappings = collect();
        $panitiaTeacherIds = [];
        $panitiaAllowSuperviseTeacherIds = [];
        $panitiaRoles = [];
        $swapHistories = collect();

        if ($activePeriode) {
            $allJadwals = UjianJadwal::with(['subject', 'schoolClass'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();

            $jadwals = $targetLevels !== null
                ? $allJadwals->filter(fn ($j) => ! empty(array_intersect($j->getTargetLevels(), $targetLevels)))->values()
                : $allJadwals;

            $allPengawas = UjianJadwalPengawas::with(['jadwal.subject', 'jadwal.schoolClass', 'ruang.schoolClass', 'pengawas1.user', 'pengawas2.user'])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->get();

            $pengawasList = $targetLevels !== null
                ? $allPengawas->filter(fn ($p) => $p->jadwal && ! empty(array_intersect($p->jadwal->getTargetLevels(), $targetLevels)))->values()
                : $allPengawas;

            $mappings = UjianPengawasMapping::where('ujian_periode_id', $activePeriode->id)
                ->get()
                ->keyBy('teacher_id');

            $panitiaList = UjianPanitia::where('ujian_periode_id', $activePeriode->id)
                ->where('is_active', true)
                ->get();

            $panitiaTeacherIds = $panitiaList->pluck('teacher_id')->toArray();
            $panitiaAllowSuperviseTeacherIds = $panitiaList->where('ikut_mengawas', true)->pluck('teacher_id')->toArray();
            $panitiaRoles = $panitiaList->pluck('jabatan_label', 'teacher_id')->toArray();

            $swapHistories = UjianPengawasSwapHistory::with([
                'teacher1.user',
                'teacher2.user',
                'jadwal1.subject',
                'jadwal2.subject',
                'sourcePengawas.ruang',
                'targetPengawas.ruang',
                'processedBy',
            ])
                ->where('ujian_periode_id', $activePeriode->id)
                ->latest('id')
                ->get();
        }

        // Generate nomor kode pengawas 1..N spesifik per kelompok
        $assignedTeachers = $pengawasList->pluck('pengawas1')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($t) => $t->user?->name ?? '')
            ->values();

        $pengawasCodeMap = [];
        foreach ($assignedTeachers as $idx => $t) {
            $pengawasCodeMap[$t->id] = $idx + 1;
        }

        $examDates = $jadwals->pluck('tanggal_ujian')
            ->filter()
            ->unique(fn ($d) => $d->format('Y-m-d'))
            ->values();

        $teachers = Teacher::with(['user', 'pengawasMappings'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn ($t) => $t->user?->name ?? '')
            ->values();

        // Statistik Pemetaan Calon Pengawas
        $totalTeachersCount = $teachers->count();
        $panitiaCount = count(array_intersect($teachers->pluck('id')->toArray(), $panitiaTeacherIds));
        $mappedCount = $mappings->count();
        $unmappedCount = max(0, $totalTeachersCount - $panitiaCount - $mappedCount);

        $classes = SchoolClass::ordered()->get();

        return view('ujian.pengawas.index', compact(
            'periodes',
            'activePeriode',
            'jadwals',
            'ruangs',
            'pengawasList',
            'teachers',
            'classes',
            'examDates',
            'mappings',
            'panitiaTeacherIds',
            'panitiaAllowSuperviseTeacherIds',
            'panitiaRoles',
            'swapHistories',
            'totalTeachersCount',
            'panitiaCount',
            'mappedCount',
            'unmappedCount',
            'kelompok',
            'pengawasCodeMap'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_jadwal_id' => ['required', 'exists:ujian_jadwal,id'],
            'ujian_ruang_id' => ['required', 'exists:ujian_ruang,id'],
            'pengawas_1_id' => ['nullable', 'exists:teachers,id'],
            'pengawas_2_id' => ['nullable', 'exists:teachers,id'],
            'status_kehadiran' => ['required', 'in:Terjadwal,Hadir,Digantikan,Izin'],
            'pengawas_pengganti' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['pengawas_1_id']) && ! empty($validated['pengawas_2_id']) && $validated['pengawas_1_id'] === $validated['pengawas_2_id']) {
            return back()->withInput()->with('error', 'Pengawas 1 dan Pengawas 2 tidak boleh orang yang sama.');
        }

        $jadwal = UjianJadwal::with('schoolClass')->findOrFail($validated['ujian_jadwal_id']);
        $ruang = UjianRuang::with('schoolClass')->findOrFail($validated['ujian_ruang_id']);

        if (! $ruang->matchesJadwal($jadwal)) {
            return back()->withInput()->with(
                'error',
                "Ruang {$ruang->nama_ruang} tidak sesuai dengan target kelas jadwal ({$jadwal->target_kelas})."
            );
        }

        $existing = UjianJadwalPengawas::where('ujian_jadwal_id', $jadwal->id)
            ->where('ujian_ruang_id', $ruang->id)
            ->first();

        $roomConflict = $this->conflictService->checkRoomScheduleConflict($ruang->id, $jadwal->id, $existing?->id);
        if ($roomConflict) {
            return back()->withInput()->with(
                'error',
                "Bentrok Ruang: {$ruang->nama_ruang} sudah digunakan oleh jadwal {$roomConflict->jadwal?->nama_mapel} pada jam yang sama."
            );
        }

        foreach (['pengawas_1_id', 'pengawas_2_id'] as $field) {
            if (empty($validated[$field])) {
                continue;
            }

            $teacherId = (int) $validated[$field];
            $eligibility = $this->mappingService->isTeacherEligibleForJadwal($teacherId, $jadwal);
            if (! $eligibility['eligible']) {
                return back()->withInput()->with('error', $eligibility['reason']);
            }

            $conflict = $this->conflictService->checkTeacherSupervisorConflict($teacherId, $jadwal->id, $existing?->id);
            if ($conflict) {
                $namaGuru = Teacher::find($teacherId)?->user?->name;

                return back()->withInput()->with(
                    'error',
                    "Bentrok Pengawas: {$namaGuru} sudah ditugaskan mengawas di {$conflict->ruang?->nama_ruang} pada jam yang sama."
                );
            }
        }

        UjianJadwalPengawas::updateOrCreate(
            [
                'ujian_jadwal_id' => $validated['ujian_jadwal_id'],
                'ujian_ruang_id' => $validated['ujian_ruang_id'],
            ],
            $validated
        );

        return back()->with('success', 'Penugasan Pengawas Ruang berhasil disimpan.');
    }

    public function autoPlot(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'two_supervisors' => ['nullable', 'boolean'],
            'reset_existing' => ['nullable', 'boolean'],
            'kelompok' => ['nullable', 'string', 'in:all,1-2,3-6'],
        ]);

        $kelompok = $validated['kelompok'] ?? 'all';
        $targetLevels = match ($kelompok) {
            '1-2' => [1, 2],
            '3-6' => [3, 4, 5, 6],
            default => null,
        };

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $count = $this->autoPlotterService->autoPlotSupervisors(
            $periode,
            $request->boolean('two_supervisors'),
            $request->boolean('reset_existing', true),
            $targetLevels
        );

        $kelompokLabel = match ($kelompok) {
            '1-2' => 'Kelompok Kelas 1–2',
            '3-6' => 'Kelompok Kelas 3–6',
            default => 'Semua Kelompok',
        };

        return back()->with('success', "Auto-Plotter ({$kelompokLabel}) selesai: {$count} ruang/sesi ujian berhasil dialokasikan pengawas bebas bentrok.");
    }

    /**
     * Mengosongkan / mereset seluruh penugasan pengawas pada periode aktif (bisa difilter per kelompok).
     */
    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'kelompok' => ['nullable', 'string', 'in:all,1-2,3-6'],
        ]);

        $kelompok = $validated['kelompok'] ?? 'all';
        $targetLevels = match ($kelompok) {
            '1-2' => ['1', '2'],
            '3-6' => ['3', '4', '5', '6'],
            default => null,
        };

        $jadwalsQuery = UjianJadwal::where('ujian_periode_id', $validated['ujian_periode_id']);
        $jadwals = $jadwalsQuery->get();
        if ($targetLevels !== null) {
            $jadwals = $jadwals->filter(fn ($j) => ! empty(array_intersect($j->getTargetLevels(), $targetLevels)))->values();
        }

        $deleted = UjianJadwalPengawas::whereIn('ujian_jadwal_id', $jadwals->pluck('id'))->delete();

        $kelompokLabel = match ($kelompok) {
            '1-2' => 'Kelompok Kelas 1–2',
            '3-6' => 'Kelompok Kelas 3–6',
            default => 'Seluruh Data',
        };

        return back()->with('success', "Berhasil mereset data: {$deleted} slot penugasan pengawas ruang ({$kelompokLabel}) telah dikosongkan.");
    }

    /**
     * Menghapus satu penugasan pengawas ruang.
     */
    public function destroy(UjianJadwalPengawas $pengawas): RedirectResponse
    {
        $ruangName = $pengawas->ruang?->nama_ruang ?? 'Ruang';
        $pengawas->delete();

        return back()->with('success', "Penugasan pengawas untuk {$ruangName} berhasil dihapus.");
    }

    /**
     * Import penugasan pengawas & jadwal dari file PDF matriks.
     */
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
     * Menyimpan pemetaan tingkat kelas individual per guru.
     */
    public function storeMapping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'assigned_levels' => ['required', 'array', 'min:1'],
            'assigned_levels.*' => ['in:1,2,3,4,5,6'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->mappingService->saveMapping(
                (int) $validated['ujian_periode_id'],
                (int) $validated['teacher_id'],
                $validated['assigned_levels'],
                $validated['keterangan'] ?? null
            );

            return redirect()->to(route('ujian.pengawas.index', ['periode_id' => $validated['ujian_periode_id']]).'#pemetaan')
                ->with('success', 'Pemetaan kelompok kelas pengawas berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->to(route('ujian.pengawas.index', ['periode_id' => $validated['ujian_periode_id']]).'#pemetaan')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Menetapkan kelompok kelas secara bulk (misal Kelas Rendah 1,2,3 atau Kelas Atas 4,5,6).
     */
    public function bulkMapping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['exists:teachers,id'],
            'assigned_levels' => ['required', 'array', 'min:1'],
            'assigned_levels.*' => ['in:1,2,3,4,5,6'],
        ]);

        $count = $this->mappingService->bulkAssignLevels(
            (int) $validated['ujian_periode_id'],
            $validated['teacher_ids'],
            $validated['assigned_levels']
        );

        return redirect()->to(route('ujian.pengawas.index', ['periode_id' => $validated['ujian_periode_id']]).'#pemetaan')
            ->with('success', "Berhasil memetakan {$count} guru sebagai calon pengawas kelas terpilih (panitia ujian otomatis dikecualikan).");
    }

    /**
     * Menghapus pemetaan kelompok kelas guru terpilih.
     */
    public function destroyMapping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['exists:teachers,id'],
        ]);

        $count = $this->mappingService->bulkDelete(
            (int) $validated['ujian_periode_id'],
            $validated['teacher_ids']
        );

        return redirect()->to(route('ujian.pengawas.index', ['periode_id' => $validated['ujian_periode_id']]).'#pemetaan')
            ->with('success', "Berhasil menghapus pemetaan untuk {$count} guru.");
    }

    /**
     * API untuk mengambil daftar kandidat pertukaran jadwal yang valid secara dua arah.
     */
    public function getSwapCandidates(Request $request): JsonResponse
    {
        $assignmentId = (int) $request->query('assignment_id');
        $slot = $request->query('slot', 'pengawas_1');

        $candidates = $this->mappingService->getEligibleSwapCandidates($assignmentId, $slot);

        return response()->json($candidates);
    }

    /**
     * Mengeksekusi pertukaran jadwal pengawas ujian.
     */
    public function executeSwap(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_assignment_id' => ['required', 'exists:ujian_jadwal_pengawas,id'],
            'source_slot' => ['required', 'in:pengawas_1,pengawas_2'],
            'target_assignment_id' => ['required', 'exists:ujian_jadwal_pengawas,id'],
            'target_slot' => ['required', 'in:pengawas_1,pengawas_2'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $this->mappingService->swapSupervisors(
                (int) $validated['source_assignment_id'],
                $validated['source_slot'],
                (int) $validated['target_assignment_id'],
                $validated['target_slot'],
                (int) auth()->id(),
                $validated['alasan'] ?? null
            );

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function printDaftarHadir(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::with('jenis')->find($periodeId)
            : UjianPeriode::with('jenis')->firstWhere('is_active', true);

        if (! $activePeriode) {
            $activePeriode = UjianPeriode::with('jenis')->latest('id')->first();
        }

        $tanggal = $request->query('tanggal');
        $ruangId = $request->query('ruang_id');
        $jadwalId = $request->query('jadwal_id');
        $kelompok = $request->query('kelompok', 'all');
        $tanggalCetakRaw = $request->query('tanggal_cetak');
        $tanggalCetak = $tanggalCetakRaw ? Carbon::parse($tanggalCetakRaw) : now();

        $targetLevels = match ($kelompok) {
            '1-2' => ['1', '2'],
            '3-6' => ['3', '4', '5', '6'],
            default => null,
        };

        $query = UjianJadwalPengawas::with([
            'jadwal.periode.jenis',
            'jadwal.subject',
            'jadwal.schoolClass',
            'ruang',
            'pengawas1.user',
            'pengawas2.user',
        ])
            ->join('ujian_jadwal', 'ujian_jadwal_pengawas.ujian_jadwal_id', '=', 'ujian_jadwal.id')
            ->join('ujian_ruang', 'ujian_jadwal_pengawas.ujian_ruang_id', '=', 'ujian_ruang.id')
            ->select('ujian_jadwal_pengawas.*');

        if ($activePeriode) {
            $query->where('ujian_jadwal.ujian_periode_id', $activePeriode->id);
        }

        if ($jadwalId) {
            $query->where('ujian_jadwal_pengawas.ujian_jadwal_id', $jadwalId);
        }

        if ($ruangId) {
            $query->where('ujian_jadwal_pengawas.ujian_ruang_id', $ruangId);
        }

        if ($tanggal && $tanggal !== 'all') {
            $query->whereDate('ujian_jadwal.tanggal_ujian', $tanggal);
        }

        // Urutkan berdasarkan tanggal ujian, jam mulai, dan kode ruang
        $pengawas = $query->orderBy('ujian_jadwal.tanggal_ujian')
            ->orderBy('ujian_jadwal.jam_mulai')
            ->orderBy('ujian_ruang.kode_ruang')
            ->get();

        if ($targetLevels !== null) {
            $pengawas = $pengawas->filter(fn ($p) => $p->jadwal && ! empty(array_intersect($p->jadwal->getTargetLevels(), $targetLevels)))->values();
        }

        // Kelompokkan data penugasan per tanggal ujian untuk mendukung cetak per hari maupun multi-hari
        $groupedPengawas = $pengawas->groupBy(function ($item) {
            return $item->jadwal?->tanggal_ujian?->format('Y-m-d') ?? 'unknown';
        });

        // Ambil data Ketua Panitia Ujian yang aktif
        $ketuaPanitia = null;
        if ($activePeriode) {
            $ketuaPanitia = UjianPanitia::with('teacher.user')
                ->where('ujian_periode_id', $activePeriode->id)
                ->where('jabatan', 'ketua')
                ->where('is_active', true)
                ->first();
        }

        // Ambil data Kepala Madrasah dan Pengaturan TTD
        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

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

        return view('ujian.pengawas.print-daftar-hadir', compact(
            'groupedPengawas',
            'pengawas',
            'activePeriode',
            'tanggal',
            'tanggalCetak',
            'tempatCetak',
            'namaKepala',
            'nipKepala',
            'namaKetua',
            'nipKetua',
            'setting',
            'ttdSetting',
            'kelompok'
        ));
    }

    /**
     * Cetak Lembar Matriks Jadwal Pengawas (Sheet 3 & Sheet 4).
     * Sheet 3: Kelompok 3–6, tampilkan mapel + kode pengawas 1..N + legenda.
     * Sheet 4: Kelompok 1–2, tanpa mapel, hanya kode pengawas 1..N + legenda.
     */
    public function printMatriks(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $kelompok = $request->query('kelompok', '3-6');
        $showMapel = $request->boolean('show_mapel', $kelompok === '3-6');

        $activePeriode = $periodeId ? UjianPeriode::find($periodeId) : UjianPeriode::firstWhere('is_active', true);
        if (! $activePeriode) {
            $activePeriode = UjianPeriode::first();
        }

        $targetLevels = match ($kelompok) {
            '1-2' => ['1', '2'],
            '3-6' => ['3', '4', '5', '6'],
            default => null,
        };

        $jadwals = collect();
        $ruangs = collect();
        if ($activePeriode) {
            $jadwalsQuery = UjianJadwal::with(['subject', 'schoolClass', 'pengawas.ruang', 'pengawas.pengawas1.user'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai');

            $allJadwals = $jadwalsQuery->get();
            $jadwals = $targetLevels !== null
                ? $allJadwals->filter(fn ($j) => ! empty(array_intersect($j->getTargetLevels(), $targetLevels)))->values()
                : $allJadwals;

            $ruangs = UjianRuang::with('schoolClass')
                ->orderBy('kode_ruang')
                ->get();
        }

        $roomCodes = $ruangs->map(function ($r) {
            if ($r->schoolClass) {
                return strtoupper(preg_replace('/\s+/', '', str_ireplace('kelas', '', $r->schoolClass->name)));
            }

            return strtoupper(preg_replace('/\s+/', '', str_ireplace(['ruang', 'kelas'], '', $r->nama_ruang ?: $r->kode_ruang)));
        })->unique()->filter()->values()->all();

        if (empty($roomCodes)) {
            $roomCodes = $kelompok === '1-2'
                ? ['1A', '1B', '2A', '2B']
                : ['3A', '3B', '3C', '4A', '4B', '5A', '5B', '6A', '6B', '6C'];
        }

        if ($kelompok === '1-2') {
            $roomCodes = array_values(array_filter($roomCodes, fn ($rc) => str_starts_with($rc, '1') || str_starts_with($rc, '2')));
        } elseif ($kelompok === '3-6') {
            $roomCodes = array_values(array_filter($roomCodes, fn ($rc) => in_array(substr($rc, 0, 1), ['3', '4', '5', '6'], true)));
        }

        natsort($roomCodes);
        $roomCodes = array_values($roomCodes);

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
            $teacherCodeMap[$t->id] = $idx + 1;
        }

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

                // 2. Prioritas kedua: jadwal umum tingkat
                if (! $jRombel) {
                    $jRombel = $slotJadwals->first(function ($j) use ($level) {
                        if ($j->school_class_id !== null) {
                            return false;
                        }

                        return in_array((string) $level, array_map('strval', $j->getTargetLevels()), true);
                    });
                }

                if (! $jRombel) {
                    $roomAssignments[$rCode] = null;

                    continue;
                }

                $kodeMapel = $jRombel->subject?->code
                    ?: ($jRombel->nama_mapel ? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $jRombel->nama_mapel), 0, 4)) : '—');

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

            $rows[] = [
                'no' => $isNewDate ? $noUrut++ : '',
                'is_new_date' => $isNewDate,
                'date_rowspan' => $dateRowCounts[$tglStr] ?? 1,
                'tanggal' => $tgl,
                'sesi_ke' => $sesiKe,
                'pukul' => "{$jamMulai} - {$jamSelesai}",
                'mapel' => $firstJadwal->nama_mapel,
                'rooms' => $roomAssignments,
            ];
        }

        $legend = [];
        ksort($legendCounts);
        foreach ($legendCounts as $code => $count) {
            $legend[] = [
                'code' => $code,
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

        $ketuaPanitia = UjianPanitia::with('teacher.user')
            ->where('ujian_periode_id', $activePeriode?->id)
            ->where(function ($q) {
                $q->where('jabatan', 'ketua')
                    ->orWhere('jabatan', 'Ketua')
                    ->orWhere('jabatan', 'like', '%ketua%');
            })
            ->where('is_active', true)
            ->first();

        $sekretarisPanitia = UjianPanitia::with('teacher.user')
            ->where('ujian_periode_id', $activePeriode?->id)
            ->where(function ($q) {
                $q->where('jabatan', 'sekretaris')
                    ->orWhere('jabatan', 'Sekretaris')
                    ->orWhere('jabatan', 'like', '%sekretaris%');
            })
            ->where('is_active', true)
            ->first();

        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;
        $setting = AttendanceSetting::first();

        $namaKepala = $ttdSetting?->nama_kepala ?? ($setting?->card_principal_name ?? ($setting?->headmasterTeacher?->user?->name ?? 'Sipulloh, M.Pd'));
        $nipKepala = $ttdSetting?->nip_kepala ?? ($setting?->card_principal_nip ?? ($setting?->headmasterTeacher?->nip ?? '-'));
        $namaKetua = $ketuaPanitia?->teacher?->user?->name ?? '....................................................';
        $nipKetua = $ketuaPanitia?->teacher?->nip ?? '-';
        $namaSekretaris = $sekretarisPanitia?->teacher?->user?->name ?? '....................................................';
        $nipSekretaris = $sekretarisPanitia?->teacher?->nip;
        $tempatCetak = $activePeriode?->tempat_cetak ?? ($ttdSetting?->tempat_penetapan ?? ($setting?->school_city ?? 'Tanggamus'));
        $tanggalCetak = $activePeriode?->tanggal_cetak_kartu ?? now();

        return view('ujian.pengawas.print-matriks', compact(
            'activePeriode',
            'kelompok',
            'showMapel',
            'rows',
            'legend',
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

    /**
     * Cetak Surat Tugas & Jadwal Tugas Mengawas Individual per Guru (A4 Portrait).
     */
    public function printJadwalGuru(Request $request): View
    {
        $teacherId = $request->query('teacher_id');
        $periodeId = $request->query('periode_id');

        $activePeriode = $periodeId ? UjianPeriode::find($periodeId) : UjianPeriode::firstWhere('is_active', true);
        if (! $activePeriode) {
            $activePeriode = UjianPeriode::first();
        }

        $teachers = collect();
        if ($teacherId && $teacherId !== 'all') {
            $teachers = Teacher::with('user')->where('id', $teacherId)->get();
        } else {
            // Ambil semua guru yang memiliki jadwal mengawas di periode ini
            if ($activePeriode) {
                $teacherIds = UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                    ->pluck('pengawas_1_id')
                    ->merge(
                        UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                            ->pluck('pengawas_2_id')
                    )
                    ->filter()
                    ->unique();

                $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get()->sortBy(fn ($t) => $t->user?->name);
            }

            if ($teachers->isEmpty()) {
                $teachers = Teacher::with('user')->get()->sortBy(fn ($t) => $t->user?->name);
            }
        }

        // Siapkan jadwal untuk setiap guru
        $teacherSchedules = [];
        foreach ($teachers as $t) {
            $duties = collect();
            if ($activePeriode) {
                $duties = UjianJadwalPengawas::with(['jadwal.subject', 'ruang', 'pengawas1.user', 'pengawas2.user'])
                    ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                    ->where(fn ($q) => $q->where('pengawas_1_id', $t->id)->orWhere('pengawas_2_id', $t->id))
                    ->join('ujian_jadwal', 'ujian_jadwal_pengawas.ujian_jadwal_id', '=', 'ujian_jadwal.id')
                    ->orderBy('ujian_jadwal.tanggal_ujian')
                    ->orderBy('ujian_jadwal.jam_mulai')
                    ->select('ujian_jadwal_pengawas.*')
                    ->get();
            }
            $teacherSchedules[] = [
                'teacher' => $t,
                'duties' => $duties,
            ];
        }

        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        return view('ujian.pengawas.print-jadwal-guru', compact('teacherSchedules', 'activePeriode', 'setting', 'ttdSetting'));
    }
}
