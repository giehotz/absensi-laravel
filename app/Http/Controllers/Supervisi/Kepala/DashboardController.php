<?php

namespace App\Http\Controllers\Supervisi\Kepala;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiHasil;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Supervisi\SupervisiTindakLanjut;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeYear = AcademicYear::activeSemester();
        $jadwalQuery = SupervisiJadwal::query()->forAcademicYear($activeYear?->id);

        $stageCounts = [
            'terjadwal' => (clone $jadwalQuery)->where('status', SupervisiStatus::Terjadwal->value)->count(),
            'pra_observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::PraObservasi->value)->count(),
            'observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::Observasi->value)->count(),
            'pasca_observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::PascaObservasi->value)->count(),
            'selesai' => (clone $jadwalQuery)->where('status', SupervisiStatus::Selesai->value)->count(),
        ];

        $rekapDukunganKombel = SupervisiTindakLanjut::query()
            ->whereNotNull('kebutuhan_dukungan')
            ->where('kebutuhan_dukungan', '!=', '')
            ->select('kebutuhan_dukungan', DB::raw('COUNT(*) as total'))
            ->groupBy('kebutuhan_dukungan')
            ->orderByDesc('total')
            ->get();

        $pmmIndikators = SupervisiJenisPenilaian::byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)->orderBy('id')->get();
        $pmmScores = SupervisiHasil::query()
            ->whereIn('jenis_penilaian_id', $pmmIndikators->pluck('id'))
            ->select('jenis_penilaian_id', DB::raw('AVG(nilai_akhir) as avg_nilai'))
            ->groupBy('jenis_penilaian_id')
            ->pluck('avg_nilai', 'jenis_penilaian_id');

        $pmmChart = $pmmIndikators->map(fn (SupervisiJenisPenilaian $ind): array => [
            'nama' => $ind->nama,
            'skor' => round((float) ($pmmScores[$ind->id] ?? 0), 1),
        ]);

        $todayJadwals = SupervisiJadwal::with(['teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject'])
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('jam_mulai')
            ->get();

        $recentResults = SupervisiJadwal::with(['teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'signatures'])
            ->where('status', SupervisiStatus::Selesai->value)
            ->orderByDesc('tanggal')
            ->limit(6)
            ->get();

        return view('supervisi.kepala.dashboard', [
            'activeYear' => $activeYear,
            'totalGuru' => Teacher::excludeKepala()->count(),
            'totalSupervisor' => SupervisiSupervisor::active()->count(),
            'stageCounts' => $stageCounts,
            'rekapDukunganKombel' => $rekapDukunganKombel,
            'pmmChart' => $pmmChart,
            'todayJadwals' => $todayJadwals,
            'recentResults' => $recentResults,
        ]);
    }

    public function performance(Request $request): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');

        $teachers = Teacher::with('user')
            ->excludeKepala()
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get();

        $completedQuery = SupervisiJadwal::with(['subject', 'schoolClass'])
            ->where('status', SupervisiStatus::Selesai->value)
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->get()
            ->groupBy('teacher_id');

        $performanceData = $teachers->map(function (Teacher $teacher) use ($completedQuery): array {
            $items = $completedQuery->get($teacher->id, collect());
            $scored = $items->whereNotNull('nilai_akhir');
            $avg = $scored->isNotEmpty() ? round((float) $scored->avg('nilai_akhir'), 2) : 0.0;

            $kategori = match (true) {
                $scored->isEmpty() => '-',
                $avg >= 86 => 'Baik Sekali',
                $avg >= 70 => 'Baik',
                $avg >= 55 => 'Cukup',
                default => 'Kurang',
            };

            return [
                'teacher' => $teacher,
                'rata_rata' => $avg,
                'kategori' => $kategori,
                'jumlah_supervisi' => $items->count(),
                'subjects' => $items->pluck('subject.name')->filter()->unique()->values()->all(),
            ];
        })->sortByDesc('rata_rata')->values();

        return view('supervisi.kepala.performance', compact('performanceData', 'academicYears', 'selectedYearId'));
    }
}
