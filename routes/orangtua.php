<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard Orang Tua
Route::get('/dashboard', [DashboardController::class, 'orangtua'])->name('dashboard');
