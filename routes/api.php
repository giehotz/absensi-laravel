<?php

use App\Http\Controllers\Api\SubjectReferenceController;
use App\Http\Controllers\Api\V1\StudentTransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Master Referensi Kurikulum Nasional (Publik)
Route::get('subjects/references', [SubjectReferenceController::class, 'index'])->name('api.subjects.references');

// Group API V1 (Terlindungi API Key Klien & Rate Limiting)
Route::prefix('v1')
    ->middleware(['api.client', 'throttle:60,1'])
    ->name('api.v1.')
    ->group(function () {
        // Student Transfer API
        Route::get('students', [StudentTransferController::class, 'index'])->name('students.index');
        Route::get('students/export-transfer', [StudentTransferController::class, 'exportTransfer'])->name('students.export');
        Route::get('students/{identifier}', [StudentTransferController::class, 'show'])->name('students.show');
    });
