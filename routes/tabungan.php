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
        Route::post('/register-student', [SavingsController::class, 'registerStudent'])->name('register-student');
        Route::post('/register-selected-students', [SavingsController::class, 'registerSelectedStudents'])->name('register-selected-students');
        Route::post('/register-class-students', [SavingsController::class, 'registerClassStudents'])->name('register-class-students');
        Route::post('/cancel-registration', [SavingsController::class, 'cancelRegistration'])->name('cancel-registration');
        Route::post('/close-account', [SavingsController::class, 'closeAccount'])->name('close-account');
        Route::post('/reopen-account', [SavingsController::class, 'reopenAccount'])->name('reopen-account');

        Route::get('/transactions', [SavingsController::class, 'transactions'])->name('transactions');
        Route::post('/transactions/{transaction}/correct', [SavingsController::class, 'correct'])->name('transactions.correct');
        Route::get('/receipt/{transaction}', [SavingsController::class, 'receipt'])->name('receipt');
        Route::get('/export', [SavingsController::class, 'exportExcel'])->name('export');
    });
