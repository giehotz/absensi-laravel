<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\SavePraObservasiRequest;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
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

    public function edit(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $jadwal->load(['academicYear', 'schoolClass', 'subject', 'supervisorTeacher.user', 'supervisorUser', 'praObservasi', 'dokumenAjars', 'signatures']);

        $indikatorPmm = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->byKategori(SupervisiJenisPenilaian::KATEGORI_OBSERVASI_PMM)
            ->active()
            ->orderBy('id')
            ->get();

        $pra = $jadwal->praObservasi;
        $fokusDipilih = $pra?->fokus_perilaku_dipilih ?? [];

        return view('supervisi.guru.pra_observasi', compact('jadwal', 'indikatorPmm', 'pra', 'fokusDipilih'));
    }

    public function save(SavePraObservasiRequest $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);

        $existing = $jadwal->praObservasi;
        if ($existing && $existing->isAgreed()) {
            return redirect()->back()->with('error', 'Formulir A telah disepakati bersama Supervisor dan tidak dapat diubah lagi.');
        }

        $validated = $request->validated();
        $isSubmit = $validated['aksi'] === 'ajukan';

        DB::transaction(function () use ($jadwal, $validated, $isSubmit): void {
            SupervisiPraObservasi::updateOrCreate(
                ['jadwal_id' => $jadwal->id],
                [
                    'fokus_perilaku_dipilih' => array_values(array_map('intval', $validated['fokus_perilaku_dipilih'] ?? [])),
                    'upaya_mempelajari' => $validated['upaya_mempelajari'] ?? null,
                    'tujuan_pembelajaran_sesi' => $validated['tujuan_pembelajaran_sesi'] ?? null,
                    'area_pengembangan' => $validated['area_pengembangan'] ?? null,
                    'strategi_persiapan' => $validated['strategi_persiapan'] ?? null,
                    'status_kesepakatan' => $isSubmit ? SupervisiPraObservasi::STATUS_DIAJUKAN : SupervisiPraObservasi::STATUS_DRAFT,
                    'submitted_at' => $isSubmit ? now() : null,
                ]
            );

            $jadwalUpdate = [];
            if (! empty($validated['indikator_pmm_id'])) {
                $jadwalUpdate['indikator_pmm_id'] = (int) $validated['indikator_pmm_id'];
            }
            if ($isSubmit && $jadwal->status === SupervisiStatus::Terjadwal->value) {
                $jadwalUpdate['status'] = SupervisiStatus::PraObservasi->value;
            }
            if (! empty($jadwalUpdate)) {
                $jadwal->update($jadwalUpdate);
            }
        });

        $msg = $isSubmit
            ? 'Formulir A berhasil diajukan ke Supervisor untuk disepakati.'
            : 'Draft Formulir A berhasil disimpan.';

        return redirect()->route('supervisi.guru.pra-observasi.edit', $jadwal)->with('success', $msg);
    }

    public function tarik(Request $request, SupervisiJadwal $jadwal): RedirectResponse
    {
        $this->guard->ensureGuruOwnsJadwal($request->user(), $jadwal);
        $this->guard->ensureNotLocked($jadwal);

        $pra = $jadwal->praObservasi;
        if (! $pra) {
            return redirect()->back()->with('error', 'Formulir A belum ditemukan.');
        }

        if ($pra->isAgreed()) {
            return redirect()->back()->with('error', 'Formulir A sudah disepakati dan tidak dapat ditarik kembali.');
        }

        $pra->update(['status_kesepakatan' => SupervisiPraObservasi::STATUS_DRAFT]);

        return redirect()->route('supervisi.guru.pra-observasi.edit', $jadwal)
            ->with('success', 'Pengajuan Formulir A berhasil ditarik kembali ke mode draf.');
    }
}
