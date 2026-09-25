<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\CustomQrCode;
use App\Services\CustomQrGeneratorService;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class QrGeneratorController extends Controller
{
    public function __construct(
        protected CustomQrGeneratorService $qrService,
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Tampilkan daftar QR Code milik Guru yang sedang login.
     */
    public function index(Request $request)
    {
        $query = CustomQrCode::where('user_id', Auth::id())->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('target_url', 'like', "%{$search}%");
            });
        }

        $qrCodes = $query->paginate(12)->withQueryString();

        return view('guru.qr-generator.index', compact('qrCodes'));
    }

    /**
     * Form pembuatan QR Code baru.
     */
    public function create()
    {
        $schoolSetting = AttendanceSetting::first();

        return view('guru.qr-generator.create', compact('schoolSetting'));
    }

    /**
     * Simpan & generate QR Code baru milik guru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_url' => ['required', 'url', 'max:2048'],
            'title' => ['nullable', 'string', 'max:100'],
            'size' => ['nullable', 'integer', 'min:100', 'max:1000'],
            'qr_color' => ['nullable', 'string', 'max:10'],
            'bg_color' => ['nullable', 'string', 'max:10'],
            'logo_type' => ['required', 'in:none,default,custom'],
            'custom_logo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'frame_style' => ['required', 'in:default,neo_brutalism,scan_me'],
            'show_label' => ['nullable'],
        ]);

        $customLogoPath = null;
        if ($validated['logo_type'] === 'custom' && $request->hasFile('custom_logo')) {
            $customLogoPath = $this->imageUploadService->uploadAsWebp(
                $request->file('custom_logo'),
                'qr-logos',
                quality: 85,
                maxWidth: 500,
                maxHeight: 500
            );
        }

        $qrCode = CustomQrCode::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'target_url' => $validated['target_url'],
            'size' => (int) ($validated['size'] ?? 300),
            'qr_color' => $validated['qr_color'] ?? '#000000',
            'bg_color' => $validated['bg_color'] ?? '#ffffff',
            'logo_type' => $validated['logo_type'],
            'custom_logo_path' => $customLogoPath,
            'frame_style' => $validated['frame_style'],
            'show_label' => $request->boolean('show_label'),
        ]);

        // Generate dan simpan berkas SVG & PNG
        $this->qrService->generateAndSave($qrCode);

        return redirect()
            ->route('guru.qr-generator.show', $qrCode)
            ->with('success', 'QR Code berhasil dibuat dan disimpan!');
    }

    /**
     * Tampilkan detail hasil QR Code.
     */
    public function show(CustomQrCode $qrGenerator)
    {
        $qrCode = $qrGenerator;
        abort_if($qrCode->user_id !== Auth::id(), Response::HTTP_FORBIDDEN, 'Akses ditolak.');

        return view('guru.qr-generator.show', compact('qrCode'));
    }

    /**
     * Unduh QR Code dalam format PNG atau SVG.
     */
    public function download(CustomQrCode $qrGenerator, string $format)
    {
        $qrCode = $qrGenerator;
        abort_if($qrCode->user_id !== Auth::id(), Response::HTTP_FORBIDDEN, 'Akses ditolak.');

        if ($format === 'png') {
            if (empty($qrCode->png_path) || ! Storage::disk('public')->exists($qrCode->png_path)) {
                $this->qrService->generateAndSave($qrCode);
                $qrCode->refresh();
            }

            $filePath = Storage::disk('public')->path($qrCode->png_path);

            return response()->download($filePath, $qrCode->getDownloadFileName('png'), [
                'Content-Type' => 'image/png',
            ]);
        }

        if ($format === 'svg') {
            if (empty($qrCode->svg_content)) {
                $this->qrService->generateAndSave($qrCode);
                $qrCode->refresh();
            }

            return response($qrCode->svg_content, 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'attachment; filename="'.$qrCode->getDownloadFileName('svg').'"',
            ]);
        }

        abort(Response::HTTP_NOT_FOUND, 'Format tidak didukung');
    }

    /**
     * Hapus QR Code milik guru sendiri.
     */
    public function destroy(CustomQrCode $qrGenerator)
    {
        $qrCode = $qrGenerator;
        abort_if($qrCode->user_id !== Auth::id(), Response::HTTP_FORBIDDEN, 'Akses ditolak.');

        $this->imageUploadService->deleteOldFile($qrCode->custom_logo_path);
        $this->imageUploadService->deleteOldFile($qrCode->png_path);

        $qrCode->delete();

        return redirect()
            ->route('guru.qr-generator.index')
            ->with('success', 'QR Code berhasil dihapus!');
    }
}
