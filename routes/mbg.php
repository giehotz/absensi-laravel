<?php

use App\Http\Controllers\Mbg\MbgDistributionController;
use App\Http\Controllers\Mbg\MbgReportController;
use Illuminate\Support\Facades\Route;

// Dashboard & Riwayat
Route::get('/', [MbgDistributionController::class, 'index'])->name('dashboard');

// Pencatatan Distribusi Harian (CRUD)
Route::get('distributions/create', [MbgDistributionController::class, 'create'])->name('distributions.create');
Route::post('distributions', [MbgDistributionController::class, 'store'])->name('distributions.store');
Route::get('distributions/{distribution}/edit', [MbgDistributionController::class, 'edit'])->name('distributions.edit');
Route::put('distributions/{distribution}', [MbgDistributionController::class, 'update'])->name('distributions.update');
Route::delete('distributions/{distribution}', [MbgDistributionController::class, 'destroy'])->name('distributions.destroy');

// Rekapitulasi & Pelaporan
Route::get('rekap', [MbgReportController::class, 'index'])->name('rekap.index');
Route::get('rekap/print', [MbgReportController::class, 'printPdf'])->name('rekap.print');
Route::get('rekap/print-detail', [MbgReportController::class, 'printDetail'])->name('rekap.print-detail');
Route::get('rekap/export-excel', [MbgReportController::class, 'exportExcel'])->name('rekap.export');
