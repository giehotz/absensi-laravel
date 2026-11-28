<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Supervisi\Concerns\HandlesCoachingTirta;
use App\Models\Supervisi\SupervisiCoachingTemplate;
use App\Models\Supervisi\SupervisiCoachingTirta;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    use HandlesCoachingTirta;

    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function show(Request $request, SupervisiJadwal $jadwal): View|RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $portalPrefix = $this->resolvePortalPrefix($request);

        if (! $this->guard->canAccessPascaObservasi($jadwal)) {
            return redirect()->route("supervisi.{$portalPrefix}.jadwal.index")
                ->with('error', 'Formulir Tindak Lanjut & Refleksi hanya dapat diakses setelah observasi kelas selesai dinilai.');
        }

        $jadwal->load(['academicYear', 'teacher.user', 'schoolClass', 'subject', 'hasils.jenisPenilaian', 'hasils.detailHasils', 'tindakLanjut', 'coachingTirtas']);
        $coachingPasca = $jadwal->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PASCA);
        $tirtaTemplates = SupervisiCoachingTemplate::forAllStages();

        return view('supervisi.supervisor.tindak_lanjut', compact('jadwal', 'coachingPasca', 'tirtaTemplates', 'portalPrefix'));
    }

    public function feedbackC(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);
        $portalPrefix = $this->resolvePortalPrefix($request);

        $tindakLanjut = $jadwal->tindakLanjut;
        if (! $tindakLanjut) {
            return redirect()->back()->with('error', 'Formulir C belum diisi oleh guru.');
        }

        $validated = $request->validate([
            'respon_supervisor_c' => ['required', 'string', 'max:100'],
            'catatan_supervisor_c' => ['nullable', 'string'],
        ]);

        $tindakLanjut->update([
            'respon_supervisor_c' => $validated['respon_supervisor_c'],
            'catatan_supervisor_c' => $validated['catatan_supervisor_c'] ?? null,
            'status_tindak_lanjut' => 'form_c_disepakati',
        ]);

        return redirect()->route("supervisi.{$portalPrefix}.tindak-lanjut.show", $jadwal)
            ->with('success', 'Umpan balik Formulir C berhasil disimpan.');
    }

    public function evaluateD(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);
        $portalPrefix = $this->resolvePortalPrefix($request);

        $tindakLanjut = $jadwal->tindakLanjut;
        if (! $tindakLanjut || empty($tindakLanjut->inspirasi_baru)) {
            return redirect()->back()->with('error', 'Formulir D belum diisi oleh guru.');
        }

        $validated = $request->validate([
            'penilaian_supervisor_d' => ['required', 'string', 'max:100'],
            'catatan_supervisor_d' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($jadwal, $tindakLanjut, $validated): void {
            $tindakLanjut->update([
                'penilaian_supervisor_d' => $validated['penilaian_supervisor_d'],
                'catatan_supervisor_d' => $validated['catatan_supervisor_d'] ?? null,
                'status_tindak_lanjut' => 'selesai',
            ]);

            $jadwal->update([
                'status' => SupervisiStatus::Selesai->value,
                'tanggal_pasca_observasi' => now(),
            ]);
        });

        return redirect()->route("supervisi.{$portalPrefix}.tindak-lanjut.show", $jadwal)
            ->with('success', 'Evaluasi akhir Formulir D tersimpan. Siklus supervisi telah Selesai.');
    }

    public function storeCoachingPasca(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);
        $portalPrefix = $this->resolvePortalPrefix($request);

        if (! $jadwal->hasils()->whereHas('detailHasils')->exists()) {
            return redirect()->back()
                ->with('error', 'Isi minimal satu komponen observasi Form B terlebih dahulu sebelum menyusun Coaching TIRTA.');
        }

        $validated = $request->validate([
            'tujuan_coaching' => ['required', 'string'],
            'identifikasi_kondisi' => ['nullable', 'string'],
            'rencana_aksi' => ['nullable', 'string'],
            'tanggung_jawab' => ['nullable', 'string'],
            'catatan_coach' => ['nullable', 'string'],
            'aksi' => ['nullable', 'in:draft,submitted'],
        ]);

        $coaching = $this->upsertCoachingStage($jadwal, SupervisiCoachingTirta::TAHAP_PASCA, $validated);

        $msg = $coaching->status === SupervisiCoachingTirta::STATUS_DRAFT
            ? 'Draf Coaching TIRTA Pasca-Observasi disimpan.'
            : 'Hasil wawancara Coaching TIRTA berhasil disimpan dan dikirim untuk konfirmasi Guru.';

        return redirect()->route("supervisi.{$portalPrefix}.tindak-lanjut.show", $jadwal)
            ->with('success', $msg);
    }

    private function resolvePortalPrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'supervisi.kepala.')
            ? 'kepala'
            : 'supervisor';
    }
}
