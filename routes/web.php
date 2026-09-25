<?php

use App\Http\Controllers\Api\SubjectReferenceController;
use App\Http\Controllers\PublicVerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect Home ke Dashboard Berdasarkan Peran atau Login
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'orangtua' => redirect()->route('orangtua.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// Autentikasi (Login & Logout)
require __DIR__.'/auth.php';

// API Referensi Mata Pelajaran (Publik / Internal)
Route::prefix('api/v1')->group(function () {
    Route::get('reference-subjects', [SubjectReferenceController::class, 'index'])->name('api.reference-subjects');
});

// Rute Peran Pengguna Terautentikasi
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(base_path('routes/guru.php'));
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(base_path('routes/siswa.php'));
Route::middleware(['auth', 'role:orangtua'])->prefix('orangtua')->name('orangtua.')->group(base_path('routes/orangtua.php'));

// Modul Terisolasi: Pengelolaan Tabungan Siswa
require base_path('routes/tabungan.php');

// Rute Publik: Verifikasi Keabsahan Kartu Tanda Pelajar (Scan QR Depan)
Route::get('validasi/siswa/{identifier}', [PublicVerificationController::class, 'verifyStudent'])->name('public.verify.student');
