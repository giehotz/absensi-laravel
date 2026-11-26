<?php

namespace App\Http\Controllers\Supervisi\Concerns;

use App\Enums\SupervisiStatus;
use App\Http\Requests\Supervisi\SavePenilaianRequest;
use App\Models\Supervisi\SupervisiFotoBukti;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Supervisi\SupervisiJenisPenilaian;
use App\Services\ImageUploadService;
use App\Services\Supervisi\SupervisiCycleGuardService;
use App\Services\Supervisi\SupervisiScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

trait HandlesPenilaianObservasi
{
    protected function renderPenilaianForm(
        SupervisiJadwal $jadwal,
        string $portalPrefix,
        SupervisiCycleGuardService $guard
    ): View|RedirectResponse {
        $jadwal->loadMissing(['teacher.user', 'schoolClass', 'subject', 'academicYear', 'indikatorPmm', 'praObservasi', 'hasils.detailHasils', 'fotoBuktis', 'signatures']);

        if ($portalPrefix === 'supervisor' && ! $guard->canAccessObservasi($jadwal)) {
            return redirect()
                ->route('supervisi.supervisor.pra-observasi.show', $jadwal)
                ->with('error', 'Observasi kelas belum dapat dinilai sebelum Formulir A (Pra-Observasi) disepakati.');
        }

        if ($guard->isLocked($jadwal)) {
            return redirect()
                ->route("supervisi.{$portalPrefix}.".($portalPrefix === 'admin' ? 'laporan.detail' : 'hasil.show'), $jadwal)
                ->with('error', 'Penilaian terkunci karena dokumen sudah ditandatangani digital. Hubungi Admin untuk reset.');
        }

        $jenisList = SupervisiJenisPenilaian::with('aspekPenilaians')
            ->active()
            ->orderByRaw("CASE kategori_supervisi WHEN 'observasi_kelas_pmm' THEN 1 WHEN 'perangkat_ajar' THEN 2 ELSE 3 END")
            ->orderBy('id')
            ->get();

        // Filter relevan: jika jadwal Kurmer, tampilkan indikator PMM terpilih + perangkat ajar; jika Reguler, tampilkan instrumen Reguler
        if ($jadwal->isKurmer()) {
            $jenisList = $jenisList->filter(
                fn (SupervisiJenisPenilaian $j) => (int) $j->id === (int) $jadwal->indikator_pmm_id
                    || $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_PERANGKAT_AJAR
            )->values();
        } else {
            $jenisList = $jenisList->filter(
                fn (SupervisiJenisPenilaian $j) => $j->kategori_supervisi === SupervisiJenisPenilaian::KATEGORI_REGULER
            )->values();
        }

        $existingHasils = $jadwal->hasils->keyBy('jenis_penilaian_id');
        $existingDetails = [];
        foreach ($existingHasils as $jenisId => $hasil) {
            $existingDetails[$jenisId] = $hasil->detailHasils->keyBy('aspek_id');
        }

        $fokusDipilihIds = collect($jadwal->praObservasi?->fokus_perilaku_dipilih ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        return view('supervisi.shared.penilaian_form', [
            'jadwal' => $jadwal,
            'jenisList' => $jenisList,
            'existingHasils' => $existingHasils,
            'existingDetails' => $existingDetails,
            'fokusDipilihIds' => $fokusDipilihIds,
            'portalPrefix' => $portalPrefix,
        ]);
    }

    protected function handleSaveKomponen(
        SavePenilaianRequest $request,
        SupervisiJadwal $jadwal,
        SupervisiCycleGuardService $guard,
        SupervisiScoringService $scoring
    ): JsonResponse|RedirectResponse {
        if ($guard->isLocked($jadwal)) {
            $msg = 'Penilaian terkunci karena dokumen sudah ditandatangani digital.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        if (! $guard->canAccessObservasi($jadwal) && ! $request->user()->isAdmin()) {
            $msg = 'Formulir A (Pra-Observasi) harus disepakati terlebih dahulu.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        $jenis = SupervisiJenisPenilaian::findOrFail($request->integer('jenis_penilaian_id'));
        $hasil = $scoring->saveKomponen(
            $jadwal,
            $jenis,
            (array) $request->input('skor', []),
            (array) $request->input('catatan', []),
            $request->input('rekomendasi')
        );

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Nilai komponen {$jenis->nama} berhasil disimpan.",
                'hasil' => [
                    'total_skor' => $hasil->total_skor,
                    'skor_maksimal' => $hasil->skor_maksimal,
                    'nilai_akhir' => $hasil->nilai_akhir,
                    'ketercapaian' => $hasil->ketercapaian,
                ],
            ]);
        }

        return redirect()->back()->with('success', "Penilaian komponen {$jenis->nama} berhasil disimpan.");
    }

    protected function handleCompletePenilaian(
        Request $request,
        SupervisiJadwal $jadwal,
        string $portalPrefix,
        SupervisiCycleGuardService $guard,
        SupervisiScoringService $scoring
    ): JsonResponse|RedirectResponse {
        if ($guard->isLocked($jadwal)) {
            $msg = 'Penilaian terkunci karena dokumen sudah ditandatangani digital.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : redirect()->back()->with('error', $msg);
        }

        if ($request->filled('jenis_penilaian_id')) {
            $jenis = SupervisiJenisPenilaian::find($request->integer('jenis_penilaian_id'));
            if ($jenis) {
                $scoring->saveKomponen(
                    $jadwal,
                    $jenis,
                    (array) $request->input('skor', []),
                    (array) $request->input('catatan', []),
                    $request->input('rekomendasi')
                );
            }
        }

        $summary = $scoring->recalculateJadwalSummary($jadwal);

        $nextStatus = SupervisiStatus::Selesai->value;
        $updatePayload = [];

        if ($jadwal->status !== SupervisiStatus::Selesai->value && $jadwal->isKurmer()) {
            $nextStatus = SupervisiStatus::PascaObservasi->value;
            $updatePayload['tanggal_pasca_observasi'] = now();
        }

        $updatePayload['status'] = $nextStatus;
        $jadwal->update($updatePayload);

        $message = $nextStatus === SupervisiStatus::PascaObservasi->value
            ? 'Observasi kelas selesai. Jadwal masuk ke tahap Tindak Lanjut (Formulir C & D).'
            : 'Penilaian supervisi berhasil diselesaikan.';

        $redirectUrl = match ($portalPrefix) {
            'admin' => route('supervisi.admin.laporan.detail', $jadwal),
            'kepala' => route('supervisi.kepala.hasil.show', $jadwal),
            default => route('supervisi.supervisor.jadwal.index'),
        };

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'summary' => $summary,
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', $message);
    }

    protected function handleUploadFoto(
        Request $request,
        SupervisiJadwal $jadwal,
        ImageUploadService $imageUploadService
    ): JsonResponse|RedirectResponse {
        $request->validate([
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $existingCount = $jadwal->fotoBuktis()->count();
        $incomingFiles = $request->file('photos', []);

        if ($existingCount + count($incomingFiles) > 5) {
            $message = 'Maksimal 5 foto bukti per jadwal supervisi.';

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        foreach ($incomingFiles as $file) {
            $path = $imageUploadService->uploadAsWebp(
                $file,
                "supervisi/foto-bukti/{$jadwal->id}",
                82,
                1600,
                1600
            );

            SupervisiFotoBukti::create([
                'jadwal_id' => $jadwal->id,
                'file_path' => $path,
                'keterangan' => $request->input('keterangan'),
                'uploaded_by' => $request->user()?->id,
            ]);
        }

        if ($request->expectsJson()) {
            $routeName = (string) ($request->route()?->getName() ?? '');
            $deleteRouteName = preg_replace('/\.upload$/', '.delete', $routeName) ?: 'supervisi.supervisor.penilaian.foto.delete';
            $photos = $jadwal->fotoBuktis()->orderBy('id')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Foto bukti supervisi berhasil diunggah.',
                'count' => $photos->count(),
                'max' => 5,
                'photos' => $photos->map(fn (SupervisiFotoBukti $item): array => [
                    'id' => $item->id,
                    'url' => $item->url,
                    'keterangan' => $item->keterangan ?? 'Bukti',
                    'delete_url' => route($deleteRouteName, $item),
                ])->values()->all(),
            ]);
        }

        return redirect()->back()->with('success', 'Foto bukti supervisi berhasil diunggah.');
    }

    protected function handleDeleteFoto(
        SupervisiFotoBukti $foto,
        ImageUploadService $imageUploadService
    ): JsonResponse|RedirectResponse {
        $jadwalId = $foto->jadwal_id;
        $deletedId = $foto->id;
        $imageUploadService->deleteOldFile($foto->file_path);
        $foto->delete();

        if (request()->expectsJson()) {
            $remainingCount = SupervisiFotoBukti::where('jadwal_id', $jadwalId)->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Foto bukti berhasil dihapus.',
                'deleted_id' => $deletedId,
                'count' => $remainingCount,
                'max' => 5,
            ]);
        }

        return redirect()->back()->with('success', 'Foto bukti berhasil dihapus.');
    }
}
