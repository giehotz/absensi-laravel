<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
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

        $jadwals = collect();
        $ruangs = UjianRuang::orderBy('kode_ruang')->get();
        $pengawasList = collect();
        $mappings = collect();
        $panitiaTeacherIds = [];
        $panitiaRoles = [];
        $swapHistories = collect();

        if ($activePeriode) {
            $jadwals = UjianJadwal::with(['subject', 'schoolClass'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();

            $pengawasList = UjianJadwalPengawas::with(['jadwal.subject', 'jadwal.schoolClass', 'ruang', 'pengawas1.user', 'pengawas2.user'])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->get();

            $mappings = UjianPengawasMapping::where('ujian_periode_id', $activePeriode->id)
                ->get()
                ->keyBy('teacher_id');

            $panitiaList = UjianPanitia::where('ujian_periode_id', $activePeriode->id)
                ->where('is_active', true)
                ->get();

            $panitiaTeacherIds = $panitiaList->pluck('teacher_id')->toArray();
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

        return view('ujian.pengawas.index', compact(
            'periodes',
            'activePeriode',
            'jadwals',
            'ruangs',
            'pengawasList',
            'teachers',
            'examDates',
            'mappings',
            'panitiaTeacherIds',
            'panitiaRoles',
            'swapHistories',
            'totalTeachersCount',
            'panitiaCount',
            'mappedCount',
            'unmappedCount'
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

        $jadwal = UjianJadwal::findOrFail($validated['ujian_jadwal_id']);

        // Validasi kelayakan pemetaan kelas & status panitia pengawas 1
        if (! empty($validated['pengawas_1_id'])) {
            $eligibility1 = $this->mappingService->isTeacherEligibleForJadwal((int) $validated['pengawas_1_id'], $jadwal);
            if (! $eligibility1['eligible']) {
                return back()->withInput()->with('error', $eligibility1['reason']);
            }

            $conflict1 = $this->conflictService->checkTeacherSupervisorConflict(
                (int) $validated['pengawas_1_id'],
                (int) $validated['ujian_jadwal_id']
            );
            if ($conflict1 && $conflict1->ujian_ruang_id != $validated['ujian_ruang_id']) {
                $namaGuru = Teacher::find($validated['pengawas_1_id'])?->user?->name;

                return back()->withInput()->with(
                    'error',
                    "Bentrok Pengawas: {$namaGuru} sudah ditugaskan mengawas di {$conflict1->ruang?->nama_ruang} pada jam yang sama."
                );
            }
        }

        // Validasi kelayakan pemetaan kelas & status panitia pengawas 2
        if (! empty($validated['pengawas_2_id'])) {
            $eligibility2 = $this->mappingService->isTeacherEligibleForJadwal((int) $validated['pengawas_2_id'], $jadwal);
            if (! $eligibility2['eligible']) {
                return back()->withInput()->with('error', $eligibility2['reason']);
            }

            $conflict2 = $this->conflictService->checkTeacherSupervisorConflict(
                (int) $validated['pengawas_2_id'],
                (int) $validated['ujian_jadwal_id']
            );
            if ($conflict2 && $conflict2->ujian_ruang_id != $validated['ujian_ruang_id']) {
                $namaGuru = Teacher::find($validated['pengawas_2_id'])?->user?->name;

                return back()->withInput()->with(
                    'error',
                    "Bentrok Pengawas: {$namaGuru} sudah ditugaskan mengawas di {$conflict2->ruang?->nama_ruang} pada jam yang sama."
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
        ]);

        $periode = UjianPeriode::findOrFail($validated['ujian_periode_id']);
        $count = $this->autoPlotterService->autoPlotSupervisors(
            $periode,
            $request->boolean('two_supervisors')
        );

        return back()->with('success', "Auto-Plotter selesai: {$count} ruang/sesi ujian berhasil dialokasikan pengawas sesuai pemetaan kelas.");
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
        $tanggalCetakRaw = $request->query('tanggal_cetak');
        $tanggalCetak = $tanggalCetakRaw ? Carbon::parse($tanggalCetakRaw) : now();

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
            'ttdSetting'
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
