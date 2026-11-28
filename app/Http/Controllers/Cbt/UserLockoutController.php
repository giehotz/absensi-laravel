<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class UserLockoutController extends Controller
{
    /**
     * Buka kunci percobaan login gagal berdasarkan identifier (NIS/NISN/NIP/NUPTK/Email).
     */
    public function unlock(Request $request): JsonResponse|RedirectResponse
    {
        $actor = $request->user();
        abort_unless(
            $actor && ! $actor->isKepala() && $actor->isPanitiaUjian(),
            403,
            'Hanya Administrator atau Panitia Ujian yang dapat membuka kunci login.'
        );

        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:150'],
        ], [
            'identifier.required' => 'Identifier akun (NIS/NISN/NIP/NUPTK/Email) wajib diisi.',
        ]);

        $identifier = trim($validated['identifier']);
        $keysToClear = [AuthController::cbtLoginThrottleKey($identifier)];

        foreach ($this->resolveLinkedIdentifiers($identifier) as $linkedIdentifier) {
            $keysToClear[] = AuthController::cbtLoginThrottleKey($linkedIdentifier);
        }

        foreach (array_unique($keysToClear) as $key) {
            RateLimiter::clear($key);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => "Lockout login untuk identifier '{$identifier}' berhasil dibuka.",
            ]);
        }

        return back()->with('success', "Lockout login untuk identifier '{$identifier}' berhasil dibuka.");
    }

    /**
     * Kumpulkan seluruh identifier login milik pengguna terkait agar unlock tetap akurat
     * meskipun login gagal menggunakan NIS/NISN/NIP/NUPTK/Email yang berbeda.
     *
     * @return array<int, string>
     */
    private function resolveLinkedIdentifiers(string $identifier): array
    {
        $user = User::where('email', $identifier)->first();

        if (! $user) {
            $teacherUserId = Teacher::where('nuptk', $identifier)
                ->orWhere('nip', $identifier)
                ->value('user_id');
            if ($teacherUserId) {
                $user = User::find($teacherUserId);
            }
        }

        if (! $user) {
            $studentUserId = Student::where('nis', $identifier)
                ->orWhere('nisn', $identifier)
                ->value('user_id');
            if ($studentUserId) {
                $user = User::find($studentUserId);
            }
        }

        if (! $user) {
            return [];
        }

        $candidates = array_filter([
            $user->email,
            $user->nip,
            $user->teacher?->nip,
            $user->teacher?->nuptk,
            $user->student?->nis,
            $user->student?->nisn,
        ], fn (?string $value): bool => filled($value));

        return array_values($candidates);
    }
}
