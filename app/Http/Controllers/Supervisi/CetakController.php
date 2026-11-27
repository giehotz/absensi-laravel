<?php

namespace App\Http\Controllers\Supervisi;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Models\Teacher;
use App\Services\Supervisi\SupervisiCycleGuardService;
use App\Services\Supervisi\SupervisiPrintService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CetakController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
        private readonly SupervisiPrintService $printService,
    ) {}

    public function show(Request $request, SupervisiJadwal $jadwal, string $type = 'detail'): View
    {
        $this->guard->ensureCanViewJadwal($request->user(), $jadwal);

        $isPolos = $request->boolean('polos');
        $payload = $this->printService->buildJadwalPrintPayload($jadwal, $isPolos);
        $payload['type'] = $type;

        $viewName = match ($type) {
            'kurmer' => 'supervisi.pdf.formulir_abcd_pmm',
            'telaah' => 'supervisi.pdf.telaah_perangkat_ajar',
            'coaching' => 'supervisi.pdf.coaching_tirta',
            default => 'supervisi.pdf.detail_hasil',
        };

        return view($viewName, $payload);
    }

    public function showInstrument(Request $request, SupervisiJadwal $jadwal, SupervisiJenisPenilaian $jenis): View
    {
        $this->guard->ensureCanViewJadwal($request->user(), $jadwal);

        $isPolos = $request->boolean('polos');
        $payload = $this->printService->buildSingleInstrumentPrintPayload($jadwal, $jenis, $isPolos);

        return view('supervisi.pdf.single_instrumen', $payload);
    }

    public function portfolio(Request $request, Teacher $teacher): View
    {
        $this->guard->ensureCanViewTeacherPortfolio($request->user(), $teacher);

        $scope = $request->query('scope', 'semester') === 'tahun' ? 'tahun' : 'semester';
        $isPolos = $request->boolean('polos');
        $yearId = $request->query('academic_year_id');

        $referenceYear = $yearId
            ? AcademicYear::findOrFail((int) $yearId)
            : (AcademicYear::activeSemester() ?? AcademicYear::query()->orderByDesc('start_date')->first());

        $payload = $this->printService->buildTeacherPortfolioPrintPayload($teacher, $referenceYear, $scope, $isPolos);

        return view('supervisi.pdf.portofolio_guru', $payload);
    }
}
