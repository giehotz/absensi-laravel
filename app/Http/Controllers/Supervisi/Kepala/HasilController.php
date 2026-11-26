<?php

namespace App\Http\Controllers\Supervisi\Kepala;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiPrintService;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HasilController extends Controller
{
    public function __construct(
        private readonly SupervisiSignatureService $signatureService,
        private readonly SupervisiPrintService $printService,
    ) {}

    public function index(): View
    {
        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'signatures'])
            ->where('status', SupervisiStatus::Selesai->value)
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.kepala.hasil.index', compact('jadwals'));
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
            route('supervisi.kepala.hasil.index')
        );

        return view('supervisi.pdf.rekap_per_instrumen', $payload);
    }

    public function show(SupervisiJadwal $jadwal): View
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
            'portalPrefix' => 'kepala',
        ]);
    }
}
