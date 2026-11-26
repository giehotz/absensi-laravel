<?php

namespace App\Http\Controllers\Supervisi;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
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
}
