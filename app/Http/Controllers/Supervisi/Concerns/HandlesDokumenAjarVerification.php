<?php

namespace App\Http\Controllers\Supervisi\Concerns;

use App\Models\Supervisi\SupervisiDokumenAjar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HandlesDokumenAjarVerification
{
    protected function handleVerifyDokumen(Request $request, SupervisiDokumenAjar $dokumen): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,disetujui,ditolak'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $dokumen->update([
            'status' => $validated['status'],
            'feedback' => $validated['feedback'] ?? null,
            'verified_by' => $request->user()?->id,
            'verified_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Status telaah perangkat ajar berhasil diperbarui.');
    }
}
