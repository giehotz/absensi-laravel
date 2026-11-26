<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiCoachingTirta;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiPraObservasi;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PraObservasiController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function show(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $jadwal->load(['academicYear', 'teacher.user', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'dokumenAjars', 'coachingTirtas', 'signatures']);

        $fokusDipilihList = ! empty($jadwal->praObservasi?->fokus_perilaku_dipilih)
            ? SupervisiAspekPenilaian::whereIn('id', $jadwal->praObservasi->fokus_perilaku_dipilih)->orderBy('urutan')->get()
            : collect();

        $coachingPra = $jadwal->coachingTirtas->firstWhere('tahap', SupervisiCoachingTirta::TAHAP_PRA);

        return view('supervisi.supervisor.pra_observasi', compact('jadwal', 'fokusDipilihList', 'coachingPra'));
    }

    public function agree(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);

        $pra = $jadwal->praObservasi;
        if (! $pra) {
            return redirect()->back()->with('error', 'Formulir A belum diisi oleh guru.');
        }

        $validated = $request->validate([
            'catatan_supervisor_pra' => ['nullable', 'string'],
            'tujuan_coaching' => ['nullable', 'string'],
            'identifikasi_kondisi' => ['nullable', 'string'],
            'rencana_aksi' => ['nullable', 'string'],
            'tanggung_jawab' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($jadwal, $pra, $validated): void {
            SupervisiCoachingTirta::updateOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'tahap' => SupervisiCoachingTirta::TAHAP_PRA,
                ],
                [
                    'tujuan_coaching' => $validated['tujuan_coaching'] ?? null,
                    'identifikasi_kondisi' => $validated['identifikasi_kondisi'] ?? null,
                    'rencana_aksi' => $validated['rencana_aksi'] ?? null,
                    'tanggung_jawab' => $validated['tanggung_jawab'] ?? null,
                ]
            );

            $pra->update([
                'catatan_supervisor_pra' => $validated['catatan_supervisor_pra'] ?? null,
                'status_kesepakatan' => SupervisiPraObservasi::STATUS_DISEPAKATI,
                'disepakati_pada' => now(),
            ]);

            if (in_array($jadwal->status, [SupervisiStatus::Terjadwal->value, SupervisiStatus::PraObservasi->value], true)) {
                $jadwal->update([
                    'status' => SupervisiStatus::Observasi->value,
                    'tanggal_pra_observasi' => now(),
                ]);
            }
        });

        return redirect()->route('supervisi.supervisor.penilaian.edit', $jadwal)
            ->with('success', 'Formulir A & Coaching TIRTA Pra-Observasi disepakati. Lanjutkan ke observasi kelas.');
    }
}
