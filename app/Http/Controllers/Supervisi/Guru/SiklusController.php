<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Services\Supervisi\SupervisiCycleGuardService;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiklusController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
        private readonly SupervisiSignatureService $signatureService,
    ) {}

    public function show(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);

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
            'signatures',
        ]);

        $stepStates = $this->guard->resolveGuruStepStates($jadwal);

        $requestedStep = (int) $request->query('step', 0);
        if (in_array($requestedStep, [1, 2, 3, 4], true)) {
            $activeStep = $requestedStep;
        } else {
            $firstIncomplete = collect($stepStates)->first(fn (array $s): bool => $s['state'] !== 'done');
            $activeStep = $firstIncomplete['step'] ?? 1;
        }

        $indikatorPmm = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)
            ->active()
            ->orderBy('id')
            ->get();

        $pra = $jadwal->praObservasi;
        $fokusDipilih = $pra?->fokus_perilaku_dipilih ?? [];

        $signatures = $this->signatureService->getSignaturesByJadwal($jadwal);
        $isFullySigned = $this->signatureService->isFullySigned($jadwal);
        $canSignCheck = $this->signatureService->canSign($jadwal, 'guru');

        $fokusDipilihList = ! empty($fokusDipilih)
            ? SupervisiAspekPenilaian::whereIn('id', $fokusDipilih)->orderBy('urutan')->get()
            : collect();

        return view('supervisi.guru.siklus', [
            'jadwal' => $jadwal,
            'dokumens' => $jadwal->dokumenAjars->sortByDesc('id'),
            'indikatorPmm' => $indikatorPmm,
            'pra' => $pra,
            'fokusDipilih' => $fokusDipilih,
            'fokusDipilihList' => $fokusDipilihList,
            'canAccessPasca' => $this->guard->canAccessPascaObservasi($jadwal),
            'signatures' => $signatures,
            'isFullySigned' => $isFullySigned,
            'canSignCheck' => $canSignCheck,
            'stepStates' => $stepStates,
            'activeStep' => $activeStep,
            'portalPrefix' => 'guru',
        ]);
    }
}
