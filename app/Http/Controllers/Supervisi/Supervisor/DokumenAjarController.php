<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesDokumenAjarVerification;
use App\Models\Supervisi\SupervisiDokumenAjar;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenAjarController extends Controller
{
    use HandlesDokumenAjarVerification;

    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function index(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $jadwal->load(['teacher.user', 'schoolClass', 'subject', 'dokumenAjars']);

        return view('supervisi.shared.dokumen_ajar_verify', [
            'jadwal' => $jadwal,
            'dokumens' => $jadwal->dokumenAjars,
            'portalPrefix' => 'supervisor',
        ]);
    }

    public function verify(Request $request, SupervisiDokumenAjar $dokumen): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $dokumen->jadwal);

        return $this->handleVerifyDokumen($request, $dokumen);
    }
}
