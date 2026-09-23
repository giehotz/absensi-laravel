<?php

use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\Siswa\LeaveRequestController;
use App\Http\Controllers\Siswa\ProfileController;
use App\Http\Controllers\Siswa\SavingsController;
use Illuminate\Support\Facades\Route;

// Dashboard Siswa Mobile-First
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Halaman Mandiri Tabungan Siswa
Route::get('/tabungan', [SavingsController::class, 'index'])->name('savings.index');

// Pengajuan Izin / Sakit
Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');

// Profil Siswa & Keamanan
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
