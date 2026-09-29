<?php

use App\Http\Controllers\Siswa\CalendarController;
use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\Siswa\LeaveRequestController;
use App\Http\Controllers\Siswa\ProfileController;
use App\Http\Controllers\Siswa\SavingsController;
use App\Http\Controllers\Siswa\TeachingJournalController;
use Illuminate\Support\Facades\Route;

// Dashboard Siswa Mobile-First
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Halaman Mandiri Kalender Pendidikan & Hari Libur (Read-Only)
Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.index');

// Halaman Mandiri Jurnal Kegiatan Pembelajaran (Read-Only)
Route::get('/jurnal-pembelajaran', [TeachingJournalController::class, 'index'])->name('teaching-journals.index');

// Halaman Mandiri Tabungan Siswa
Route::get('/tabungan', [SavingsController::class, 'index'])->name('savings.index');

// Pengajuan Izin / Sakit
Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');

// Profil Siswa & Keamanan
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
