<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiExcelExportService;
use App\Services\Supervisi\SupervisiPrintService;
use App\Services\Supervisi\SupervisiScoringService;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(
        private readonly SupervisiExcelExportService $excelService,
        private readonly SupervisiSignatureService $signatureService,
        private readonly SupervisiPrintService $printService,
        private readonly SupervisiScoringService $scoringService,
    ) {}

    public function index(Request $request): View
    {
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');
        $selectedStatus = $request->query('status', 'all');
        $selectedScope = $request->query('scope', 'semester') === 'tahun' ? 'tahun' : 'semester';

        $jadwals = $this->filteredJadwals($selectedYearId, $selectedStatus);
        $referenceYear = $selectedYearId !== 'all' && $selectedYearId !== ''
            ? $academicYears->firstWhere('id', (int) $selectedYearId)
            : ($activeYear ?? $academicYears->first());

        $portfolioProgressMap = $this->scoringService->buildPortfolioProgressMap(
            $jadwals->pluck('teacher_id')->filter()->unique()->values()->all(),
            $referenceYear,
            $selectedScope
        );

        return view('supervisi.admin.laporan.index', compact(
            'jadwals',
            'academicYears',
            'selectedYearId',
            'selectedStatus',
            'selectedScope',
            'activeYear',
            'referenceYear',
            'portfolioProgressMap'
        ));
    }

    public function detail(SupervisiJadwal $jadwal): View
    {
        $jadwal->load([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'dokumenAjars',
            'hasils.jenisPenilaian',
            'hasils.detailHasils.aspek',
            'fotoBuktis',
            'tindakLanjut',
            'coachingTirtas',
        ]);

        $signatures = $this->signatureService->getSignaturesByJadwal($jadwal);
        $isFullySigned = $this->signatureService->isFullySigned($jadwal);
        $hasAnySignature = $this->signatureService->hasAnySignature($jadwal);

        $fokusDipilihList = ! empty($jadwal->praObservasi?->fokus_perilaku_dipilih)
            ? SupervisiAspekPenilaian::whereIn('id', $jadwal->praObservasi->fokus_perilaku_dipilih)->orderBy('urutan')->get()
            : collect();

        return view('supervisi.shared.hasil_detail', [
            'jadwal' => $jadwal,
            'signatures' => $signatures,
            'isFullySigned' => $isFullySigned,
            'hasAnySignature' => $hasAnySignature,
            'fokusDipilihList' => $fokusDipilihList,
            'portalPrefix' => 'admin',
        ]);
    }

    public function printRekap(Request $request): View
    {
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');

        $jadwals = $this->filteredJadwals($selectedYearId, SupervisiStatus::Selesai->value);
        $setting = AttendanceSetting::query()->with('headmasterTeacher.user')->first();

        return view('supervisi.pdf.rekap', [
            'jadwals' => $jadwals,
            'setting' => $setting,
            'backUrl' => route('supervisi.admin.laporan.index'),
        ]);
    }

    public function printByInstrument(Request $request): View
    {
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = (string) $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');
        $selectedKategori = (string) $request->query('kategori', 'all');
        $rawJenisId = $request->query('jenis_penilaian_id');
        $selectedJenisId = ($rawJenisId !== null && $rawJenisId !== '' && $rawJenisId !== 'all') ? (int) $rawJenisId : null;
        $selectedStatus = (string) $request->query('status', 'all');

        $payload = $this->printService->buildInstrumentRekapPayload(
            $selectedYearId,
            $selectedKategori,
            $selectedJenisId,
            $selectedStatus,
            route('supervisi.admin.laporan.index')
        );

        return view('supervisi.pdf.rekap_per_instrumen', $payload);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');
        $selectedStatus = $request->query('status', 'all');

        $jadwals = $this->filteredJadwals($selectedYearId, $selectedStatus);
        $label = $selectedYearId !== 'all' ? 'TA '.(AcademicYear::find((int) $selectedYearId)?->name ?? '') : 'Semua Tahun Ajaran';

        return $this->excelService->exportRekap($jadwals, $label, 'laporan-supervisi-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportDetailExcel(SupervisiJadwal $jadwal): StreamedResponse
    {
        return $this->excelService->exportDetailJadwal($jadwal);
    }

    public function batchExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $ids = array_map('intval', (array) $request->input('selected_ids', []));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal satu data untuk diekspor.');
        }

        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject'])
            ->whereIn('id', $ids)
            ->orderBy('tanggal')
            ->get();

        return $this->excelService->exportRekap($jadwals, 'Data Terpilih', 'laporan-supervisi-batch-'.now()->format('Y-m-d').'.xlsx');
    }

    public function exportAllBatch(Request $request): StreamedResponse
    {
        $activeYear = AcademicYear::activeSemester();
        $selectedYearId = $request->query('academic_year_id', $activeYear?->id ? (string) $activeYear->id : 'all');

        $jadwals = $this->filteredJadwals($selectedYearId, SupervisiStatus::Selesai->value);

        return $this->excelService->exportBatchMultiSheetPerGuru($jadwals, 'rekap-supervisi-per-guru-'.now()->format('Y-m-d').'.xlsx');
    }

    private function filteredJadwals(string $selectedYearId, string $selectedStatus)
    {
        return SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'signatures',
        ])
            ->when($selectedYearId !== 'all' && $selectedYearId !== '', fn ($q) => $q->where('academic_year_id', (int) $selectedYearId))
            ->when($selectedStatus !== 'all' && $selectedStatus !== '', fn ($q) => $q->where('status', $selectedStatus))
            ->orderBy('tanggal')
            ->get();
    }
}
