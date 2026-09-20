<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rute Autentikasi (Tamu / Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout (Wajib Login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
