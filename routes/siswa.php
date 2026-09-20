<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard Siswa
Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('dashboard');
