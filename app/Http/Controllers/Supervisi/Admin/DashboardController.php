<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiCoachingTirta;
use App\Models\Supervisi\SupervisiHasil;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Supervisi\SupervisiTindakLanjut;
use App\Models\Teacher;
use App\Services\Supervisi\SupervisiScoringService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SupervisiScoringService $scoringService,
    ) {}

    public function index(): View
    {
        $activeYear = AcademicYear::activeSemester();

        $jadwalQuery = SupervisiJadwal::query()
            ->forAcademicYear($activeYear?->id)
            ->where('status', '!=', SupervisiStatus::Batal->value);
        $totalJadwal = (clone $jadwalQuery)->count();
        $jadwalSelesai = (clone $jadwalQuery)->where('status', SupervisiStatus::Selesai->value)->count();
        $completionRate = $totalJadwal > 0 ? round(($jadwalSelesai / $totalJadwal) * 100, 1) : 0.0;
        $avgNilaiAkhir = round((float) ((clone $jadwalQuery)->whereNotNull('nilai_akhir')->avg('nilai_akhir') ?? 0), 2);

        $stageCounts = [
            'terjadwal' => (clone $jadwalQuery)->where('status', SupervisiStatus::Terjadwal->value)->count(),
            'pra_observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::PraObservasi->value)->count(),
            'observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::Observasi->value)->count(),
            'pasca_observasi' => (clone $jadwalQuery)->where('status', SupervisiStatus::PascaObservasi->value)->count(),
            'selesai' => $jadwalSelesai,
        ];

        $predikatStats = (clone $jadwalQuery)
            ->where('status', SupervisiStatus::Selesai->value)
            ->whereNotNull('ketercapaian')
            ->select('ketercapaian', DB::raw('COUNT(*) as total'))
            ->groupBy('ketercapaian')
            ->pluck('total', 'ketercapaian');

        $allTeacherIds = Teacher::excludeKepala()
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->pluck('id')
            ->all();
        $scheduledTeacherIds = (clone $jadwalQuery)->distinct()->pluck('teacher_id')->all();
        $unscheduledTeachersCount = count(array_diff($allTeacherIds, $scheduledTeacherIds));

        $tirtaSelesaiCount = SupervisiCoachingTirta::query()
            ->where('tahap', SupervisiCoachingTirta::TAHAP_PASCA)
            ->whereIn('status', [SupervisiCoachingTirta::STATUS_SUBMITTED, SupervisiCoachingTirta::STATUS_CONFIRMED])
            ->whereHas('jadwal', fn ($q) => $q->forAcademicYear($activeYear?->id)->where('status', '!=', SupervisiStatus::Batal->value))
            ->distinct('jadwal_id')
            ->count('jadwal_id');

        $pendingTirtaOrTtdCount = (clone $jadwalQuery)
            ->where('status', SupervisiStatus::Selesai->value)
            ->where(function ($q): void {
                $q->whereDoesntHave('coachingTirtas', fn ($cq) => $cq->where('tahap', SupervisiCoachingTirta::TAHAP_PASCA))
                    ->orWhereHas('signatures', null, '<', 3);
            })
            ->count();

        $fallbackSlotCount = (clone $jadwalQuery)
            ->where('sumber_slot', 'teacher_assignment_fallback')
            ->count();

        $portfolioProgressMap = $this->scoringService->buildPortfolioProgressMap($allTeacherIds, $activeYear, 'semester');
        $kmaProgressMap = $this->scoringService->buildKmaProgressMap($allTeacherIds, $activeYear, 'semester');
        $completed14TeachersCount = collect($portfolioProgressMap)->filter(fn (array $p): bool => $p['total_forms'] > 0 && $p['done_count'] >= $p['total_forms'])->count();
        $completedKmaTeachersCount = collect($kmaProgressMap)->filter(fn (array $p): bool => $p['total_forms'] > 0 && $p['done_count'] >= $p['total_forms'])->count();

        $rekapDukunganKombel = SupervisiTindakLanjut::query()
            ->whereNotNull('kebutuhan_dukungan')
            ->where('kebutuhan_dukungan', '!=', '')
            ->select('kebutuhan_dukungan', DB::raw('COUNT(*) as total'))
            ->groupBy('kebutuhan_dukungan')
            ->orderByDesc('total')
            ->get();

        $pmmIndikators = SupervisiJenisPenilaian::query()
            ->byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)
            ->orderBy('id')
            ->get();

        $pmmScores = SupervisiHasil::query()
            ->whereIn('jenis_penilaian_id', $pmmIndikators->pluck('id'))
            ->select('jenis_penilaian_id', DB::raw('AVG(nilai_akhir) as avg_nilai'))
            ->groupBy('jenis_penilaian_id')
            ->pluck('avg_nilai', 'jenis_penilaian_id');

        $pmmChart = $pmmIndikators->map(fn (SupervisiJenisPenilaian $ind): array => [
            'nama' => $ind->nama,
            'skor' => round((float) ($pmmScores[$ind->id] ?? 0), 1),
        ]);

        $upcomingJadwals = SupervisiJadwal::with(['teacher.user', 'supervisorTeacher.user', 'schoolClass', 'subject'])
            ->forAcademicYear($activeYear?->id)
            ->whereDate('tanggal', '>=', now()->toDateString())
            ->where('status', '!=', SupervisiStatus::Selesai->value)
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->orderBy('tanggal')
            ->limit(6)
            ->get();

        return view('supervisi.admin.dashboard', [
            'activeYear' => $activeYear,
            'totalGuru' => count($allTeacherIds),
            'totalSupervisor' => SupervisiSupervisor::active()->count(),
            'totalJadwal' => $totalJadwal,
            'jadwalSelesai' => $jadwalSelesai,
            'completionRate' => $completionRate,
            'avgNilaiAkhir' => $avgNilaiAkhir,
            'tirtaSelesaiCount' => $tirtaSelesaiCount,
            'unscheduledTeachersCount' => $unscheduledTeachersCount,
            'pendingTirtaOrTtdCount' => $pendingTirtaOrTtdCount,
            'fallbackSlotCount' => $fallbackSlotCount,
            'completed14TeachersCount' => $completed14TeachersCount,
            'completedKmaTeachersCount' => $completedKmaTeachersCount,
            'stageCounts' => $stageCounts,
            'predikatStats' => $predikatStats,
            'rekapDukunganKombel' => $rekapDukunganKombel,
            'pmmChart' => $pmmChart,
            'upcomingJadwals' => $upcomingJadwals,
        ]);
    }
}
