<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesPenilaianObservasi;
use App\Http\Requests\Supervisi\SavePenilaianRequest;
use App\Models\Supervisi\SupervisiFotoBukti;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Services\ImageUploadService;
use App\Services\Supervisi\SupervisiCycleGuardService;
use App\Services\Supervisi\SupervisiScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenilaianController extends Controller
{
    use HandlesPenilaianObservasi;

    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
        private readonly SupervisiScoringService $scoring,
        private readonly ImageUploadService $imageUploadService,
    ) {}

    public function edit(Request $request, SupervisiJadwal $jadwal): View|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->renderPenilaianForm($jadwal, 'supervisor', $this->guard);
    }

    public function showForm(Request $request, SupervisiJadwal $jadwal, SupervisiJenisPenilaian $jenis): View|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->renderSingleInstrumentForm($jadwal, $jenis, 'supervisor', $this->guard);
    }

    public function saveKomponen(SavePenilaianRequest $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->handleSaveKomponen($request, $jadwal, $this->guard, $this->scoring);
    }

    public function complete(Request $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->handleCompletePenilaian($request, $jadwal, 'supervisor', $this->guard, $this->scoring);
    }

    public function uploadFoto(Request $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->handleUploadFoto($request, $jadwal, $this->imageUploadService, $this->guard);
    }

    public function deleteFoto(Request $request, SupervisiFotoBukti $foto): JsonResponse|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $foto->jadwal);

        return $this->handleDeleteFoto($foto, $this->imageUploadService, $this->guard);
    }
}
