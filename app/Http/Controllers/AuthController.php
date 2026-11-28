<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public const CBT_LOGIN_MAX_ATTEMPTS = 3;

    public const CBT_LOGIN_DECAY_SECONDS = 600;

    /**
     * Key throttle login berbasis identifier (tanpa IP) agar dapat dibuka kuncinya
     * oleh admin/panitia ujian melalui endpoint unlock.
     */
    public static function cbtLoginThrottleKey(string $identifier): string
    {
        return 'cbt_login:'.mb_strtolower(trim($identifier));
    }

    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login', ['cbtMode' => false]);
    }

    /**
     * Tampilkan halaman login dalam mode CBT-Only (default langsung ke Portal CBT siswa).
     */
    public function showCbtLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSiswa()) {
                return redirect()->route('cbt.siswa.index');
            }

            return $this->redirectToDashboard($user);
        }

        return view('auth.login', ['cbtMode' => true]);
    }

    /**
     * Proses autentikasi Universal Login (Email / NIP / NIS).
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login_identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login_identifier.required' => 'Email, NIP, atau NIS wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $identifier = trim((string) $request->input('login_identifier'));
        $password = (string) $request->input('password');
        $throttleKey = self::cbtLoginThrottleKey($identifier);

        if (RateLimiter::tooManyAttempts($throttleKey, self::CBT_LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'login_identifier' => "Terlalu banyak percobaan login gagal. Akun dikunci sementara selama {$seconds} detik. Hubungi admin atau panitia ujian untuk membuka kunci.",
            ])->withInput($request->only('login_identifier', 'cbt_only'));
        }

        // 1. Cari via Email
        $user = User::where('email', $identifier)->first();

        // 2. Jika tidak ketemu, cari via NUPTK atau NIP Guru
        if (! $user) {
            $teacherUserId = Teacher::where('nuptk', $identifier)
                ->orWhere('nip', $identifier)
                ->value('user_id');
            if ($teacherUserId) {
                $user = User::find($teacherUserId);
            }
        }

        // 3. Jika tidak ketemu, cari via NIS atau NISN Siswa
        if (! $user) {
            $studentUserId = Student::where('nis', $identifier)
                ->orWhere('nisn', $identifier)
                ->value('user_id');
            if ($studentUserId) {
                $user = User::find($studentUserId);
            }
        }

        // Validasi Kredensial
        if (! $user || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($throttleKey, self::CBT_LOGIN_DECAY_SECONDS);

            return back()->withErrors([
                'login_identifier' => 'Kredensial yang dimasukkan tidak sesuai.',
            ])->withInput($request->only('login_identifier', 'cbt_only'));
        }

        // Cek Keaktifan Akun
        if (! $user->is_active) {
            return back()->withErrors([
                'login_identifier' => 'Akun Anda dinonaktifkan. Silakan hubungi admin sekolah.',
            ])->withInput($request->only('login_identifier', 'cbt_only'));
        }

        // Login Pengguna & bersihkan counter lockout
        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->isSiswa() && $request->boolean('cbt_only')) {
            return redirect()->route('cbt.siswa.index')->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        return $this->redirectToDashboard($user)->with('success', 'Selamat datang kembali, '.$user->name.'!');
    }

    /**
     * Keluar dari sesi aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Arahkan pengguna ke dashboard sesuai perannya.
     */
    private function redirectToDashboard(User $user): RedirectResponse
    {
        return match ($user->role) {
            'admin', 'kepala' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'orangtua' => redirect()->route('orangtua.dashboard'),
            'petugas_mbg' => redirect()->route('mbg.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
