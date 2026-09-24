<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\CustomQrCode;
use App\Models\User;
use App\Services\CustomQrGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class QrGeneratorController extends Controller
{
    public function __construct(
        protected CustomQrGeneratorService $qrService
    ) {}

    /**
     * Tampilkan seluruh daftar QR Code untuk Admin.
     */
    public function index(Request $request)
    {
        $query = CustomQrCode::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('target_url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $qrCodes = $query->paginate(12)->withQueryString();

        // Ambil daftar user pembuat untuk filter
        $creators = User::whereIn('id', CustomQrCode::select('user_id')->distinct())->get(['id', 'name', 'role']);

        return view('admin.qr-generator.index', compact('qrCodes', 'creators'));
    }

    /**
     * Form pembuatan QR Code baru.
     */
    public function create()
    {
        $schoolSetting = AttendanceSetting::first();

        return view('admin.qr-generator.create', compact('schoolSetting'));
    }

    /**
     * Simpan & generate QR Code baru.
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
            $customLogoPath = $request->file('custom_logo')->store('qr-logos', 'public');
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
            ->route('admin.qr-generator.show', $qrCode)
            ->with('success', 'QR Code berhasil dibuat dan disimpan!');
    }

    /**
     * Tampilkan detail hasil QR Code.
     */
    public function show(CustomQrCode $qrGenerator)
    {
        $qrCode = $qrGenerator;

        return view('admin.qr-generator.show', compact('qrCode'));
    }

    /**
     * Unduh QR Code dalam format PNG atau SVG.
     */
    public function download(CustomQrCode $qrGenerator, string $format)
    {
        $qrCode = $qrGenerator;

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
     * Hapus QR Code.
     */
    public function destroy(CustomQrCode $qrGenerator)
    {
        $qrCode = $qrGenerator;

        if (! empty($qrCode->custom_logo_path) && Storage::disk('public')->exists($qrCode->custom_logo_path)) {
            Storage::disk('public')->delete($qrCode->custom_logo_path);
        }

        if (! empty($qrCode->png_path) && Storage::disk('public')->exists($qrCode->png_path)) {
            Storage::disk('public')->delete($qrCode->png_path);
        }

        $qrCode->delete();

        return redirect()
            ->route('admin.qr-generator.index')
            ->with('success', 'QR Code berhasil dihapus!');
    }
}
