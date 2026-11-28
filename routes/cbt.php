<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cbt\CbtPortalController;
use App\Http\Controllers\Cbt\UserLockoutController;
use App\Http\Middleware\EnsureKepalaMonitoring;
use App\Http\Middleware\EnsurePanitiaUjian;
use Illuminate\Support\Facades\Route;

// Alias Login Mode CBT-Only (Tamu / Guest)
Route::middleware('guest')->group(function () {
    Route::get('/cbt/login', [AuthController::class, 'showCbtLoginForm'])->name('cbt.login');
});

// Buka Kunci Rate Limiter Login (Khusus Admin & Panitia Ujian; Kepala/Guru Non-Panitia/Siswa ditolak)
Route::post('/cbt/users/unlock', [UserLockoutController::class, 'unlock'])
    ->middleware(['auth', EnsurePanitiaUjian::class, EnsureKepalaMonitoring::class])
    ->name('cbt.users.unlock');

// Panel Manajemen CBT (Admin, Panitia Ujian, Guru, & Kepala Madrasah [Read-Only])
Route::middleware(['auth', 'cbt.manage', EnsureKepalaMonitoring::class])
    ->prefix('cbt/manage')
    ->name('cbt.manage.')
    ->group(function () {
        Route::get('/', [CbtPortalController::class, 'manageIndex'])->name('index');
    });

// Portal Ujian CBT Siswa (Siswa Aktif)
Route::middleware(['auth', 'cbt.student'])
    ->prefix('cbt/siswa')
    ->name('cbt.siswa.')
    ->group(function () {
        Route::get('/', [CbtPortalController::class, 'siswaIndex'])->name('index');
    });
