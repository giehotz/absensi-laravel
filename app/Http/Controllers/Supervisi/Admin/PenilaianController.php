<?php

namespace App\Http\Controllers\Supervisi\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesPenilaianObservasi;
use App\Http\Requests\Supervisi\SavePenilaianRequest;
use App\Models\Supervisi\SupervisiFotoBukti;
use App\Models\Supervisi\SupervisiJadwal;
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

    public function edit(SupervisiJadwal $jadwal): View|RedirectResponse
    {
        return $this->renderPenilaianForm($jadwal, 'admin', $this->guard);
    }

    public function saveKomponen(SavePenilaianRequest $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        return $this->handleSaveKomponen($request, $jadwal, $this->guard, $this->scoring);
    }

    public function complete(Request $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        return $this->handleCompletePenilaian($request, $jadwal, 'admin', $this->guard, $this->scoring);
    }

    public function uploadFoto(Request $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        return $this->handleUploadFoto($request, $jadwal, $this->imageUploadService, $this->guard);
    }

    public function deleteFoto(SupervisiFotoBukti $foto): JsonResponse|RedirectResponse
    {
        return $this->handleDeleteFoto($foto, $this->imageUploadService, $this->guard);
    }
}
