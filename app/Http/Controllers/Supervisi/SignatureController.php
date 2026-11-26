<?php

namespace App\Http\Controllers\Supervisi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisi\ResetSignatureRequest;
use App\Http\Requests\Supervisi\SignDokumenRequest;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    public function __construct(
        private readonly SupervisiSignatureService $signatureService,
    ) {}

    public function sign(SignDokumenRequest $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        try {
            $signature = $this->signatureService->sign(
                $jadwal,
                $request->user(),
                $request->validated('signer_role'),
                $request->validated('tanggal_ttd') ?? ($jadwal->tanggal?->toDateString() ?? now()->toDateString()),
                $request->ip(),
                $request->userAgent(),
                $request->validated('dokumen_type') ?? 'supervisi'
            );

            $message = 'Dokumen berhasil ditandatangani secara digital.';

            return $request->expectsJson()
                ? response()->json(['status' => 'success', 'message' => $message, 'token' => $signature->token_verifikasi])
                : redirect()->back()->with('success', $message);
        } catch (AuthorizationException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 403)
                : abort(403, $e->getMessage());
        } catch (\DomainException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function signAllAdmin(Request $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_ttd' => ['nullable', 'date_format:Y-m-d'],
            'dokumen_type' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $this->signatureService->signAllAdmin(
                $jadwal,
                $request->user(),
                $validated['tanggal_ttd'] ?? ($jadwal->tanggal?->toDateString() ?? now()->toDateString()),
                $request->ip(),
                $request->userAgent(),
                $validated['dokumen_type'] ?? 'supervisi'
            );

            $message = 'Seluruh tanda tangan digital (Guru, Supervisor, Kepala Madrasah) berhasil disahkan.';

            return $request->expectsJson()
                ? response()->json(['status' => 'success', 'message' => $message])
                : redirect()->back()->with('success', $message);
        } catch (\DomainException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 422)
                : redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reset(ResetSignatureRequest $request, SupervisiJadwal $jadwal): JsonResponse|RedirectResponse
    {
        $this->signatureService->resetSignatures(
            $jadwal,
            $request->user(),
            $request->validated('alasan'),
            $request->validated('dokumen_type') ?? 'supervisi'
        );

        $message = 'Seluruh tanda tangan digital berhasil di-reset. Penilaian kini dapat diperbarui kembali.';

        return $request->expectsJson()
            ? response()->json(['status' => 'success', 'message' => $message])
            : redirect()->back()->with('success', $message);
    }
}
