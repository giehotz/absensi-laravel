<?php

use App\Http\Controllers\Guru\SavingsController;
use App\Http\Middleware\EnsureIsSavingsOfficer;
use Illuminate\Support\Facades\Route;

// Kelola Tabungan Siswa (Untuk Guru yang ditunjuk sebagai Pengelola atau Admin)
Route::middleware(['auth', EnsureIsSavingsOfficer::class])
    ->prefix('guru/tabungan')
    ->name('guru.savings.')
    ->group(function () {
        Route::get('/', [SavingsController::class, 'index'])->name('index');
        Route::get('/search', [SavingsController::class, 'search'])->name('search');
        Route::post('/deposit', [SavingsController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [SavingsController::class, 'withdraw'])->name('withdraw');
        Route::get('/transactions', [SavingsController::class, 'transactions'])->name('transactions');
        Route::get('/receipt/{transaction}', [SavingsController::class, 'receipt'])->name('receipt');
        Route::get('/export', [SavingsController::class, 'exportExcel'])->name('export');
    });
