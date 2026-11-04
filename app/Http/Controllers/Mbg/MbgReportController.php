<?php

namespace App\Http\Controllers\Mbg;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\MbgDistribution;
use App\Models\MbgDistributionClass;
use App\Models\SchoolClass;
use App\Services\LetterheadService;
use App\Services\MbgReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MbgReportController extends Controller
{
    public function __construct(
        protected MbgReportService $reportService
    ) {}

    /**
     * Halaman Rekapitulasi MBG (Filter 1 bulan, triwulan 1-4, semester, rentang bulan bebas, atau tanggal kustom).
     */
    public function index(Request $request): View
    {
        $period = (string) $request->query('period', 'this_month');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fromMonth = $request->filled('from_month') ? (int) $request->query('from_month') : null;
        $toMonth = $request->filled('to_month') ? (int) $request->query('to_month') : null;
        $year = $request->filled('year') ? (int) $request->query('year') : null;

        $range = $this->reportService->resolveDateRange($period, $startDate, $endDate, $fromMonth, $toMonth, $year);

        $distributions = MbgDistribution::with(['recordedByUser', 'classDistributions.schoolClass'])
            ->whereBetween('date', [$range['start_date']->format('Y-m-d'), $range['end_date']->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $stats = $this->reportService->getSummaryStats($range['start_date'], $range['end_date']);

        return view('mbg.rekap.index', compact(
            'period',
            'startDate',
            'endDate',
            'fromMonth',
            'toMonth',
            'year',
            'range',
            'distributions',
            'stats'
        ));
    }

    /**
     * Tampilan Cetak Resmi Rekapitulasi MBG (Format Ringkas / Tabel Presensi MBG Ber-Kop).
     */
    public function printPdf(Request $request): View
    {
        $period = (string) $request->query('period', 'this_month');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fromMonth = $request->filled('from_month') ? (int) $request->query('from_month') : null;
        $toMonth = $request->filled('to_month') ? (int) $request->query('to_month') : null;
        $year = $request->filled('year') ? (int) $request->query('year') : null;

        $range = $this->reportService->resolveDateRange($period, $startDate, $endDate, $fromMonth, $toMonth, $year);

        $distributions = MbgDistribution::with(['recordedByUser', 'classDistributions.schoolClass'])
            ->whereBetween('date', [$range['start_date']->format('Y-m-d'), $range['end_date']->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $stats = $this->reportService->getSummaryStats($range['start_date'], $range['end_date']);
        $setting = AttendanceSetting::first();

        return view('reports.mbg-rekap-print', compact(
            'range',
            'distributions',
            'stats',
            'setting'
        ));
    }

    /**
     * Tampilan Cetak Laporan Lengkap Pelaksanaan & Pendistribusian MBG (Format Detail SKP / e-Kinerja 100% Dinamis).
     */
    public function printDetail(Request $request): View
    {
        $period = (string) $request->query('period', 'triwulan_3');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fromMonth = $request->filled('from_month') ? (int) $request->query('from_month') : null;
        $toMonth = $request->filled('to_month') ? (int) $request->query('to_month') : null;
        $year = $request->filled('year') ? (int) $request->query('year') : null;

        $range = $this->reportService->resolveDateRange($period, $startDate, $endDate, $fromMonth, $toMonth, $year);

        $distributions = MbgDistribution::with(['recordedByUser.teacher', 'classDistributions.schoolClass'])
            ->whereBetween('date', [$range['start_date']->format('Y-m-d'), $range['end_date']->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $stats = $this->reportService->getSummaryStats($range['start_date'], $range['end_date']);
        $monthlyBreakdown = $this->reportService->getMonthlyBreakdown($range['start_date'], $range['end_date']);
        $setting = AttendanceSetting::first();
        $letterhead = app(LetterheadService::class)->getData();

        $activeAcademicYear = AcademicYear::activeSemester() ?? AcademicYear::orderByDesc('start_date')->first();

        // Data rombongan belajar aktif beserta wali kelas dan jumlah siswa
        $classes = SchoolClass::with(['homeroomTeacher.user'])
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        // Kalkulasi akumulasi distribusi per kelas pada periode laporan ini
        $distributionIds = $distributions->pluck('id');
        $classDistAggregates = MbgDistributionClass::whereIn('mbg_distribution_id', $distributionIds)
            ->selectRaw('school_class_id, SUM(target_portions) as sum_target, SUM(distributed_portions) as sum_distributed, SUM(leftover_portions) as sum_leftover')
            ->groupBy('school_class_id')
            ->get()
            ->keyBy('school_class_id');

        return view('reports.mbg-detail-print', compact(
            'period',
            'range',
            'fromMonth',
            'toMonth',
            'year',
            'startDate',
            'endDate',
            'distributions',
            'stats',
            'monthlyBreakdown',
            'setting',
            'classes',
            'classDistAggregates',
            'activeAcademicYear',
            'letterhead'
        ));
    }

    /**
     * Unduh Laporan Rekapitulasi dalam format Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $period = (string) $request->query('period', 'this_month');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fromMonth = $request->filled('from_month') ? (int) $request->query('from_month') : null;
        $toMonth = $request->filled('to_month') ? (int) $request->query('to_month') : null;
        $year = $request->filled('year') ? (int) $request->query('year') : null;

        $range = $this->reportService->resolveDateRange($period, $startDate, $endDate, $fromMonth, $toMonth, $year);

        $distributions = MbgDistribution::with(['recordedByUser', 'classDistributions.schoolClass'])
            ->whereBetween('date', [$range['start_date']->format('Y-m-d'), $range['end_date']->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $stats = $this->reportService->getSummaryStats($range['start_date'], $range['end_date']);
        $setting = AttendanceSetting::first();
        $schoolName = $setting?->school_name ?? config('app.name', 'Sekolah');

        return $this->reportService->exportExcel($distributions, $stats, $range['period_label'], $schoolName);
    }
}
