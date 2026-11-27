<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiScoringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SupervisiScoringService $scoringService,
    ) {}

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $activeYear = AcademicYear::activeSemester() ?? $academicYears->first();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : '');
        $selectedScope = $request->query('scope', 'semester') === 'tahun' ? 'tahun' : 'semester';
        $referenceYear = $selectedYearId !== '' ? $academicYears->firstWhere('id', (int) $selectedYearId) : $activeYear;

        $jadwals = SupervisiJadwal::with(['academicYear', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'tindakLanjut', 'signatures'])
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('tanggal')
            ->get();

        $selesai = $jadwals->where('status', SupervisiStatus::Selesai->value);
        $avgScore = $selesai->whereNotNull('nilai_akhir')->avg('nilai_akhir');

        $stats = [
            'total' => $jadwals->count(),
            'mendatang' => $jadwals->where('status', '!=', SupervisiStatus::Selesai->value)->where('status', '!=', SupervisiStatus::Batal->value)->count(),
            'selesai' => $selesai->count(),
            'rata_rata' => $avgScore !== null ? round((float) $avgScore, 2) : 0.0,
        ];

        $portfolio = $referenceYear
            ? $this->scoringService->buildTeacherSemesterPortfolio($teacher, $referenceYear, $selectedScope)
            : null;

        return view('supervisi.guru.dashboard', compact(
            'jadwals',
            'stats',
            'teacher',
            'academicYears',
            'referenceYear',
            'selectedScope',
            'portfolio'
        ));
    }
}
