<?php

namespace App\Http\Controllers\Supervisi\Admin;

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
        return $this->renderCoachingIndex($request, 'admin');
    }

    public function edit(Request $request, SupervisiJadwal $jadwal): View|RedirectResponse
    {
        return $this->renderCoachingEdit($request, $jadwal, 'admin', $this->guard);
    }

    public function update(SaveCoachingTirtaRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        return $this->handleSaveCoaching($request, $jadwal, 'admin', $this->guard);
    }

    public function updateTemplate(SaveCoachingTemplateRequest $request): RedirectResponse
    {
        return $this->handleUpdateTemplate($request, 'admin');
    }

    public function applyTemplate(Request $request): RedirectResponse
    {
        return $this->handleApplyTemplateBatch($request, 'admin');
    }
}
