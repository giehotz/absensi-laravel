<?php

use App\Http\Controllers\Siswa\DashboardController;
use App\Http\Controllers\Siswa\LeaveRequestController;
use Illuminate\Support\Facades\Route;

// Dashboard Siswa Mobile-First
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Pengajuan Izin / Sakit
Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
