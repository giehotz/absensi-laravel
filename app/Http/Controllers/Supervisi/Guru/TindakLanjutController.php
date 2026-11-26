<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\SaveTindakLanjutCRequest;
use App\Http\Requests\Supervisi\SaveTindakLanjutDRequest;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiTindakLanjut;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function edit(Request $request, SupervisiJadwal $jadwal): View|RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);

        if (! $this->guard->canAccessPascaObservasi($jadwal)) {
            return redirect()->route('supervisi.guru.jadwal.index')
                ->with('error', 'Formulir Tindak Lanjut & Refleksi (Form C & D) baru dapat diakses setelah observasi selesai dinilai.');
        }

        $jadwal->load(['academicYear', 'schoolClass', 'subject', 'supervisorTeacher.user', 'supervisorUser', 'hasils.jenisPenilaian', 'tindakLanjut', 'coachingTirtas']);

        return view('supervisi.guru.tindak_lanjut', compact('jadwal'));
    }

    public function saveFormC(SaveTindakLanjutCRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);

        if (! $this->guard->canAccessPascaObservasi($jadwal)) {
            return redirect()->route('supervisi.guru.jadwal.index')
                ->with('error', 'Tahap pasca-observasi belum dibuka.');
        }

        SupervisiTindakLanjut::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                ...$request->validated(),
                'status_tindak_lanjut' => 'form_c_diisi',
            ]
        );

        return redirect()->route('supervisi.guru.tindak-lanjut.edit', $jadwal)
            ->with('success', 'Formulir C (Rencana Tindak Lanjut) berhasil disimpan dan diajukan ke Supervisor.');
    }

    public function saveFormD(SaveTindakLanjutDRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);

        $tindakLanjut = $jadwal->tindakLanjut;
        if (! $tindakLanjut) {
            return redirect()->back()->with('error', 'Harap isi dan simpan Formulir C terlebih dahulu.');
        }

        $tindakLanjut->update([
            ...$request->validated(),
            'status_tindak_lanjut' => 'form_d_diisi',
        ]);

        return redirect()->route('supervisi.guru.tindak-lanjut.edit', $jadwal)
            ->with('success', 'Formulir D (Refleksi Tindak Lanjut) berhasil disimpan dan diajukan ke Supervisor.');
    }
}
