<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Ujian\UjianPanitia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PanitiaUjianController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ujian_periode_id' => ['required', 'exists:ujian_periode,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'jabatan' => ['required', 'in:ketua,sekretaris,bendahara,anggota'],
            'sk_nomor' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        UjianPanitia::updateOrCreate(
            [
                'ujian_periode_id' => $validated['ujian_periode_id'],
                'teacher_id' => $validated['teacher_id'],
            ],
            [
                'jabatan' => $validated['jabatan'],
                'sk_nomor' => $validated['sk_nomor'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Guru berhasil ditetapkan sebagai Panitia Ujian.');
    }

    public function destroy(UjianPanitia $panitia): RedirectResponse
    {
        $panitia->delete();

        return back()->with('success', 'Status kepanitiaan guru berhasil dicabut.');
    }
}
