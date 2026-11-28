<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesCoachingTirta;
use App\Http\Requests\Supervisi\SaveCoachingTemplateRequest;
use App\Http\Requests\Supervisi\SaveCoachingTirtaRequest;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachingController extends Controller
{
    use HandlesCoachingTirta;

    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function index(Request $request): View
    {
        return $this->renderCoachingIndex($request, 'supervisor');
    }

    public function edit(Request $request, SupervisiJadwal $jadwal): View|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->renderCoachingEdit($request, $jadwal, 'supervisor', $this->guard);
    }

    public function update(SaveCoachingTirtaRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        return $this->handleSaveCoaching($request, $jadwal, 'supervisor', $this->guard);
    }

    public function updateTemplate(SaveCoachingTemplateRequest $request): RedirectResponse
    {
        return $this->handleUpdateTemplate($request, 'supervisor');
    }

    public function applyTemplate(Request $request): RedirectResponse
    {
        return $this->handleApplyTemplateBatch($request, 'supervisor');
    }
}
