<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Kunci session untuk menyimpan ID admin yang melakukan impersonasi.
     */
    public const SESSION_ADMIN_ID = 'impersonator_id';

    /**
     * Kunci session untuk menyimpan nama admin yang melakukan impersonasi.
     */
    public const SESSION_ADMIN_NAME = 'impersonator_name';

    /**
     * Masuk ke akun guru menggunakan hak akses admin (impersonasi).
     */
    public function start(Request $request, Teacher $teacher): RedirectResponse
    {
        $admin = $request->user();

        if (! $admin?->isAdmin()) {
            abort(403, 'Hanya administrator yang dapat melakukan impersonasi.');
        }

        if ($request->session()->has(self::SESSION_ADMIN_ID)) {
            return back()->with('error', 'Anda sedang berada dalam mode impersonasi. Kembali ke akun admin terlebih dahulu.');
        }

        $teacherUser = $teacher->user;

        if (! $teacherUser || ! $teacherUser->isGuru()) {
            return back()->with('error', 'Akun guru yang dipilih tidak valid atau tidak ditemukan.');
        }

        if (! $teacherUser->is_active) {
            return back()->with('error', "Akun guru {$teacherUser->name} sedang dinonaktifkan sehingga tidak dapat diakses.");
        }

        $request->session()->put([
            self::SESSION_ADMIN_ID => $admin->id,
            self::SESSION_ADMIN_NAME => $admin->name,
        ]);

        Auth::logoutCurrentDevice();
        Auth::login($teacherUser);
        $request->session()->regenerate();

        Log::info('Admin memulai impersonasi akun guru.', [
            'admin_id' => $admin->id,
            'teacher_id' => $teacher->id,
            'teacher_user_id' => $teacherUser->id,
        ]);

        return redirect()
            ->route('guru.dashboard')
            ->with('success', "Anda sekarang masuk sebagai {$teacherUser->name}. Seluruh aktivitas akan tercatat atas nama akun guru ini.");
    }

    /**
     * Kembali ke akun admin asal setelah impersonasi selesai.
     */
    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get(self::SESSION_ADMIN_ID);

        if (! $adminId) {
            return redirect()
                ->route('login')
                ->with('error', 'Tidak ada sesi impersonasi yang aktif.');
        }

        $admin = User::find($adminId);

        if (! $admin || ! $admin->isAdmin() || ! $admin->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::warning('Sesi impersonasi dihentikan karena akun admin asal tidak tersedia.', [
                'admin_id' => $adminId,
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'Akun admin asal tidak tersedia. Silakan login kembali.');
        }

        $request->session()->forget([
            self::SESSION_ADMIN_ID,
            self::SESSION_ADMIN_NAME,
        ]);

        Auth::logoutCurrentDevice();
        Auth::login($admin);
        $request->session()->regenerate();

        Log::info('Admin kembali dari impersonasi akun guru.', [
            'admin_id' => $admin->id,
        ]);

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', "Anda kembali masuk sebagai administrator, {$admin->name}.");
    }
}
