<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use App\Services\Ujian\JadwalConflictService;
use App\Services\Ujian\PengawasAutoPlotterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalPengawasController extends Controller
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasAutoPlotterService $autoPlotterService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jadwals = collect();
        $ruangs = UjianRuang::orderBy('kode_ruang')->get();
        $pengawasList = collect();

        if ($activePeriode) {
            $jadwals = UjianJadwal::with(['subject', 'schoolClass'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();

            $pengawasList = UjianJadwalPengawas::with(['jadwal.subject', 'jadwal.schoolClass', 'ruang', 'pengawas1.user', 'pengawas2.user'])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->get();
        }

        $teachers = Teacher::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn ($t) => $t->user?->name ?? '')
            ->values();

        return view('ujian.pengawas.index', compact(
            'periodes',
            'activePeriode',
            'jadwals',
            'ruangs',
            'pengawasList',
            'teachers'
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

        // Cek bentrok pengawas 1
        if (! empty($validated['pengawas_1_id'])) {
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

        // Cek bentrok pengawas 2
        if (! empty($validated['pengawas_2_id'])) {
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

        return back()->with('success', "Auto-Plotter selesai: {$count} ruang/sesi ujian berhasil dialokasikan pengawas.");
    }

    public function printDaftarHadir(Request $request): View
    {
        $jadwalId = $request->query('jadwal_id');
        $ruangId = $request->query('ruang_id');

        $pengawas = UjianJadwalPengawas::with(['jadwal.periode.jenis', 'jadwal.subject', 'jadwal.schoolClass', 'ruang', 'pengawas1.user', 'pengawas2.user'])
            ->when($jadwalId, fn ($q) => $q->where('ujian_jadwal_id', $jadwalId))
            ->when($ruangId, fn ($q) => $q->where('ujian_ruang_id', $ruangId))
            ->get();

        $setting = AttendanceSetting::first();

        return view('ujian.pengawas.print-daftar-hadir', compact('pengawas', 'setting'));
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
