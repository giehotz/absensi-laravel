<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\SaveTindakLanjutCRequest;
use App\Http\Requests\Supervisi\SaveTindakLanjutDRequest;
use App\Models\Supervisi\SupervisiCoachingTirta;
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
        $this->guard->ensureNotLocked($jadwal);

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

        $route = $request->input('redirect_to') === 'siklus'
            ? route('supervisi.guru.siklus.show', ['jadwal' => $jadwal, 'step' => 3])
            : route('supervisi.guru.tindak-lanjut.edit', $jadwal);

        return redirect()->to($route)
            ->with('success', 'Formulir C (Rencana Tindak Lanjut) berhasil disimpan dan diajukan ke Supervisor.');
    }

    public function saveFormD(SaveTindakLanjutDRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);

        $tindakLanjut = $jadwal->tindakLanjut;
        if (! $tindakLanjut) {
            return redirect()->back()->with('error', 'Harap isi dan simpan Formulir C terlebih dahulu.');
        }

        $tindakLanjut->update([
            ...$request->validated(),
            'status_tindak_lanjut' => 'form_d_diisi',
        ]);

        $route = $request->input('redirect_to') === 'siklus'
            ? route('supervisi.guru.siklus.show', ['jadwal' => $jadwal, 'step' => 3])
            : route('supervisi.guru.tindak-lanjut.edit', $jadwal);

        return redirect()->to($route)
            ->with('success', 'Formulir D (Refleksi Tindak Lanjut) berhasil disimpan dan diajukan ke Supervisor.');
    }

    public function confirmCoaching(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);

        $coaching = SupervisiCoachingTirta::where('jadwal_id', $jadwal->id)
            ->where('tahap', SupervisiCoachingTirta::TAHAP_PASCA)
            ->first();

        if (! $coaching || $coaching->status === SupervisiCoachingTirta::STATUS_DRAFT) {
            return redirect()->back()
                ->with('error', 'Catatan Coaching TIRTA belum dikirim oleh Supervisor.');
        }

        $validated = $request->validate([
            'catatan_guru' => ['required', 'string', 'max:2000'],
        ], [
            'catatan_guru.required' => 'Tuliskan komitmen atau refleksi singkat Guru sebelum menyetujui Coaching TIRTA.',
        ]);

        $coaching->update([
            'catatan_guru' => $validated['catatan_guru'],
            'status' => SupervisiCoachingTirta::STATUS_CONFIRMED,
            'guru_confirmed_at' => now(),
        ]);

        $route = $request->input('redirect_to') === 'siklus'
            ? route('supervisi.guru.siklus.show', ['jadwal' => $jadwal, 'step' => 3])
            : route('supervisi.guru.tindak-lanjut.edit', $jadwal);

        return redirect()->to($route)
            ->with('success', 'Komitmen Coaching TIRTA berhasil disetujui oleh Guru.');
    }
}
