<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Services\Ujian\SignatureAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanTtdController extends Controller
{
    public function __construct(
        protected SignatureAssetService $assetService
    ) {}

    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $pengaturan = null;
        if ($activePeriode) {
            $pengaturan = UjianPengaturanTtd::firstOrCreate(
                ['ujian_periode_id' => $activePeriode->id],
                [
                    'nama_instansi' => 'MIN 2 TANGGAMUS',
                    'kop_baris_1' => 'KARTU PESERTA',
                    'kop_baris_2' => 'ASESMEN SUMATIF',
                    'kop_baris_3' => 'MIN 2 TANGGAMUS',
                    'kop_baris_4' => 'TAHUN PELAJARAN '.$activePeriode->tahun_pelajaran,
                    'jabatan_penandatangan' => 'Kepala Madrasah',
                    'nama_kepala' => 'Sipulloh, M.Pd',
                    'nip_kepala' => '197005272007011022',
                    'tempat_penetapan' => 'Tanggamus',
                    'tanggal_penetapan' => now()->toDateString(),
                    'opacity_watermark' => 0.35,
                    'lebar_ttd_cm' => 3.00,
                    'tinggi_ttd_cm' => 3.00,
                    'offset_atas_ttd_cm' => 0.00,
                    'offset_kanan_ttd_cm' => 0.00,
                    'tampilkan_stempel' => true,
                    'tampilkan_qrcode_validasi' => true,
                ]
            );
        }

        $mainSetting = AttendanceSetting::first();

        return view('ujian.pengaturan-ttd.index', compact('periodes', 'activePeriode', 'pengaturan', 'mainSetting'));
    }

    public function update(Request $request, UjianPengaturanTtd $pengaturan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:150'],
            'kop_baris_1' => ['required', 'string', 'max:150'],
            'kop_baris_2' => ['nullable', 'string', 'max:150'],
            'kop_baris_3' => ['required', 'string', 'max:150'],
            'kop_baris_4' => ['nullable', 'string', 'max:150'],
            'jabatan_penandatangan' => ['required', 'string', 'max:100'],
            'nama_kepala' => ['required', 'string', 'max:150'],
            'nip_kepala' => ['nullable', 'string', 'max:50'],
            'tempat_penetapan' => ['required', 'string', 'max:100'],
            'tanggal_penetapan' => ['nullable', 'date'],
            'opacity_watermark' => ['required', 'numeric', 'min:0.05', 'max:1.00'],
            'lebar_ttd_cm' => ['required', 'numeric', 'min:1.00', 'max:8.00'],
            'tinggi_ttd_cm' => ['required', 'numeric', 'min:1.00', 'max:8.00'],
            'offset_atas_ttd_cm' => ['required', 'numeric', 'min:-5.00', 'max:5.00'],
            'offset_kanan_ttd_cm' => ['required', 'numeric', 'min:-5.00', 'max:5.00'],
            'tampilkan_stempel' => ['nullable', 'boolean'],
            'tampilkan_qrcode_validasi' => ['nullable', 'boolean'],
            'gambar_ttd_file' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'stempel_file' => ['nullable', 'image', 'mimes:png,webp,jpg,jpeg', 'max:2048'],
            'canvas_signature' => ['nullable', 'string'],
        ]);

        $validated['tampilkan_stempel'] = $request->has('tampilkan_stempel');
        $validated['tampilkan_qrcode_validasi'] = $request->has('tampilkan_qrcode_validasi');

        // Unggah berkas tanda tangan baru
        if ($request->hasFile('gambar_ttd_file')) {
            $this->assetService->deleteOldAsset($pengaturan->path_gambar_ttd);
            $validated['path_gambar_ttd'] = $this->assetService->storeUploadedAsset($request->file('gambar_ttd_file'), 'signature');
        } elseif (! empty($validated['canvas_signature'])) {
            $canvasPath = $this->assetService->storeCanvasBase64($validated['canvas_signature'], 'signature');
            if ($canvasPath) {
                $this->assetService->deleteOldAsset($pengaturan->path_gambar_ttd);
                $validated['path_gambar_ttd'] = $canvasPath;
            }
        }

        // Unggah berkas stempel baru
        if ($request->hasFile('stempel_file')) {
            $this->assetService->deleteOldAsset($pengaturan->path_stempel);
            $validated['path_stempel'] = $this->assetService->storeUploadedAsset($request->file('stempel_file'), 'stamp');
        }

        unset($validated['gambar_ttd_file'], $validated['stempel_file'], $validated['canvas_signature']);

        $pengaturan->update($validated);

        return back()->with('success', 'Pengaturan Kop dan Tanda Tangan Kartu Ujian berhasil disimpan.');
    }

    public function deleteSignature(UjianPengaturanTtd $pengaturan): RedirectResponse
    {
        $this->assetService->deleteOldAsset($pengaturan->path_gambar_ttd);
        $pengaturan->update(['path_gambar_ttd' => null]);

        return back()->with('success', 'Berkas tanda tangan digital berhasil dihapus.');
    }

    public function deleteStamp(UjianPengaturanTtd $pengaturan): RedirectResponse
    {
        $this->assetService->deleteOldAsset($pengaturan->path_stempel);
        $pengaturan->update(['path_stempel' => null]);

        return back()->with('success', 'Berkas stempel resmi madrasah berhasil dihapus.');
    }
}
