<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiHasil;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Supervisi\SupervisiTindakLanjut;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeYear = AcademicYear::activeSemester();

        $jadwalQuery = SupervisiJadwal::query()->forAcademicYear($activeYear?->id);
        $totalJadwal = (clone $jadwalQuery)->count();
        $jadwalSelesai = (clone $jadwalQuery)->where('status', SupervisiStatus::Selesai->value)->count();
        $completionRate = $totalJadwal > 0 ? round(($jadwalSelesai / $totalJadwal) * 100, 1) : 0.0;

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
            'totalGuru' => Teacher::excludeKepala()->count(),
            'totalSupervisor' => SupervisiSupervisor::active()->count(),
            'totalJadwal' => $totalJadwal,
            'jadwalSelesai' => $jadwalSelesai,
            'completionRate' => $completionRate,
            'stageCounts' => $stageCounts,
            'predikatStats' => $predikatStats,
            'rekapDukunganKombel' => $rekapDukunganKombel,
            'pmmChart' => $pmmChart,
            'upcomingJadwals' => $upcomingJadwals,
        ]);
    }
}
