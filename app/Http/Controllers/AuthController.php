<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
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

        $identifier = trim($request->input('login_identifier'));
        $password = $request->input('password');

        // 1. Cari via Email
        $user = User::where('email', $identifier)->first();

        // 2. Jika tidak ketemu, cari via NIP Guru
        if (! $user) {
            $teacherUserId = Teacher::where('nip', $identifier)->value('user_id');
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
            return back()->withErrors([
                'login_identifier' => 'Kredensial yang dimasukkan tidak sesuai.',
            ])->withInput($request->only('login_identifier'));
        }

        // Cek Keaktifan Akun
        if (! $user->is_active) {
            return back()->withErrors([
                'login_identifier' => 'Akun Anda dinonaktifkan. Silakan hubungi admin sekolah.',
            ])->withInput($request->only('login_identifier'));
        }

        // Login Pengguna
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

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
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'orangtua' => redirect()->route('orangtua.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
