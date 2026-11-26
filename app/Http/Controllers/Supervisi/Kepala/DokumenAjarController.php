<?php

namespace App\Http\Controllers\Supervisi\Kepala;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesDokumenAjarVerification;
use App\Models\Supervisi\SupervisiDokumenAjar;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumenAjarController extends Controller
{
    use HandlesDokumenAjarVerification;

    public function index(SupervisiJadwal $jadwal): View
    {
        $jadwal->load(['teacher.user', 'schoolClass', 'subject', 'dokumenAjars']);

        return view('supervisi.shared.dokumen_ajar_verify', [
            'jadwal' => $jadwal,
            'dokumens' => $jadwal->dokumenAjars,
            'portalPrefix' => 'kepala',
        ]);
    }

    public function verify(Request $request, SupervisiDokumenAjar $dokumen): RedirectResponse
    {
        return $this->handleVerifyDokumen($request, $dokumen);
    }
}
