<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiScoringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruBinaanController extends Controller
{
    public function __construct(
        private readonly SupervisiScoringService $scoringService,
    ) {}

    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher?->id;
        $referenceYear = AcademicYear::activeSemester() ?? AcademicYear::query()->orderByDesc('start_date')->first();
        $scope = $request->query('scope', 'semester') === 'tahun' ? 'tahun' : 'semester';

        $jadwals = SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'tindakLanjut',
            'signatures',
        ])
            ->where(function ($q) use ($teacherId, $request): void {
                $q->where('supervisor_teacher_id', $teacherId)
                    ->orWhere('supervisor_user_id', $request->user()->id);
            })
            ->orderByDesc('tanggal')
            ->get();

        $portfolioProgressMap = $this->scoringService->buildPortfolioProgressMap(
            $jadwals->pluck('teacher_id')->filter()->unique()->values()->all(),
            $referenceYear,
            $scope
        );

        return view('supervisi.supervisor.guru_binaan', compact(
            'jadwals',
            'referenceYear',
            'scope',
            'portfolioProgressMap'
        ));
    }
}
