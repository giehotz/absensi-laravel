<?php

use App\Http\Controllers\Ujian\DashboardUjianController;
use App\Http\Controllers\Ujian\JadwalPengawasController;
use App\Http\Controllers\Ujian\JadwalUjianController;
use App\Http\Controllers\Ujian\JenisUjianController;
use App\Http\Controllers\Ujian\PanitiaUjianController;
use App\Http\Controllers\Ujian\PengaturanTtdController;
use App\Http\Controllers\Ujian\PesertaKartuUjianController;
use App\Http\Controllers\Ujian\RuangUjianController;
use App\Http\Middleware\EnsurePanitiaUjian;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', EnsurePanitiaUjian::class])
    ->prefix('ujian')
    ->name('ujian.')
    ->group(function () {
        // 1. Dashboard Ujian & Penetapan Panitia
        Route::get('/', [DashboardUjianController::class, 'index'])->name('dashboard');
        Route::post('/panitia', [PanitiaUjianController::class, 'store'])->name('panitia.store');
        Route::delete('/panitia/{panitia}', [PanitiaUjianController::class, 'destroy'])->name('panitia.destroy');

        // 2. Sub-Menu 1: Master Jenis & Periode Ujian
        Route::prefix('jenis-ujian')->name('jenis.')->group(function () {
            Route::get('/', [JenisUjianController::class, 'index'])->name('index');
            Route::post('/', [JenisUjianController::class, 'storeJenis'])->name('store');
            Route::put('/{jeni}', [JenisUjianController::class, 'updateJenis'])->name('update');
        });

        Route::prefix('periode')->name('periode.')->group(function () {
            Route::post('/', [JenisUjianController::class, 'storePeriode'])->name('store');
            Route::post('/{periode}/activate', [JenisUjianController::class, 'activatePeriode'])->name('activate');
        });

        // 3. Sub-Menu 2: Jadwal Ujian
        Route::prefix('jadwal-ujian')->name('jadwal.')->group(function () {
            Route::get('/', [JadwalUjianController::class, 'index'])->name('index');
            Route::post('/', [JadwalUjianController::class, 'store'])->name('store');
            Route::put('/{jadwal}', [JadwalUjianController::class, 'update'])->name('update');
            Route::post('/copy', [JadwalUjianController::class, 'copySchedule'])->name('copy');
            Route::delete('/{jadwal}', [JadwalUjianController::class, 'destroy'])->name('destroy');
            Route::get('/cetak', [JadwalUjianController::class, 'print'])->name('print');
        });

        // 4. Sub-Menu 3: Ruang Ujian & Jadwal Pengawas Ruang
        Route::prefix('ruang-ujian')->name('ruang.')->group(function () {
            Route::get('/', [RuangUjianController::class, 'index'])->name('index');
            Route::post('/', [RuangUjianController::class, 'store'])->name('store');
            Route::post('/sync', [RuangUjianController::class, 'syncFromClasses'])->name('sync');
            Route::put('/{ruang}', [RuangUjianController::class, 'update'])->name('update');
            Route::delete('/{ruang}', [RuangUjianController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('jadwal-pengawas')->name('pengawas.')->group(function () {
            Route::get('/', [JadwalPengawasController::class, 'index'])->name('index');
            Route::post('/', [JadwalPengawasController::class, 'store'])->name('store');
            Route::post('/auto-plot', [JadwalPengawasController::class, 'autoPlot'])->name('auto-plot');
            Route::get('/cetak-daftar-hadir', [JadwalPengawasController::class, 'printDaftarHadir'])->name('print-daftar-hadir');
        });

        // 5. Sub-Menu 4: Cetak Kartu Ujian & Manajemen Peserta
        Route::prefix('kartu-ujian')->name('kartu.')->group(function () {
            Route::get('/', [PesertaKartuUjianController::class, 'index'])->name('index');
            Route::post('/sync', [PesertaKartuUjianController::class, 'sync'])->name('sync');
            Route::post('/generate-nomor', [PesertaKartuUjianController::class, 'generateNomor'])->name('generate-nomor');
            Route::post('/allocate-rooms', [PesertaKartuUjianController::class, 'allocateRooms'])->name('allocate-rooms');
            Route::post('/peserta', [PesertaKartuUjianController::class, 'store'])->name('store');
            Route::put('/peserta/{peserta}', [PesertaKartuUjianController::class, 'update'])->name('update');
            Route::delete('/peserta/{peserta}', [PesertaKartuUjianController::class, 'destroy'])->name('destroy');
            Route::get('/cetak', [PesertaKartuUjianController::class, 'printCards'])->name('print');
        });

        // 6. Sub-Menu 5: Tanda Tangan Kepala & Pengaturan Aset Kartu
        Route::prefix('pengaturan-ttd')->name('pengaturan-ttd.')->group(function () {
            Route::get('/', [PengaturanTtdController::class, 'index'])->name('index');
            Route::put('/{pengaturan}', [PengaturanTtdController::class, 'update'])->name('update');
            Route::delete('/{pengaturan}/ttd', [PengaturanTtdController::class, 'deleteSignature'])->name('delete-signature');
            Route::delete('/{pengaturan}/stempel', [PengaturanTtdController::class, 'deleteStamp'])->name('delete-stamp');
        });
    });
