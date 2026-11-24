<?php

use App\Http\Controllers\Ujian\AdministrasiUjianController;
use App\Http\Controllers\Ujian\DashboardUjianController;
use App\Http\Controllers\Ujian\JadwalPengawasController;
use App\Http\Controllers\Ujian\JadwalUjianController;
use App\Http\Controllers\Ujian\JenisUjianController;
use App\Http\Controllers\Ujian\PanitiaUjianController;
use App\Http\Controllers\Ujian\PengaturanTtdController;
use App\Http\Controllers\Ujian\PengawasAbsensiAdminController;
use App\Http\Controllers\Ujian\PesertaKartuUjianController;
use App\Http\Controllers\Ujian\RuangUjianController;
use App\Http\Controllers\Ujian\SesiUjianController;
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
            Route::post('/pemetaan-hari', [JenisUjianController::class, 'storePemetaanHari'])->name('pemetaan-hari.store');
            Route::post('/pemetaan-hari/preview', [JenisUjianController::class, 'previewPemetaanHari'])->name('pemetaan-hari.preview');
            Route::put('/{jeni}', [JenisUjianController::class, 'updateJenis'])->name('update');
        });

        Route::prefix('periode')->name('periode.')->group(function () {
            Route::post('/', [JenisUjianController::class, 'storePeriode'])->name('store');
            Route::put('/{periode}', [JenisUjianController::class, 'updatePeriode'])->name('update');
            Route::post('/{periode}/activate', [JenisUjianController::class, 'activatePeriode'])->name('activate');
        });

        // 3. Sub-Menu 2: Jadwal Ujian
        Route::prefix('jadwal-ujian')->name('jadwal.')->group(function () {
            Route::get('/', [JadwalUjianController::class, 'index'])->name('index');
            Route::post('/', [JadwalUjianController::class, 'store'])->name('store');
            Route::get('/template', [JadwalUjianController::class, 'downloadTemplate'])->name('template');
            Route::post('/import', [JadwalUjianController::class, 'import'])->name('import');
            Route::post('/import-pdf', [JadwalUjianController::class, 'importPdf'])->name('import-pdf');
            Route::put('/{jadwal}', [JadwalUjianController::class, 'update'])->name('update');
            Route::post('/copy', [JadwalUjianController::class, 'copySchedule'])->name('copy');
            Route::post('/sync-levels/preview', [JadwalUjianController::class, 'previewCopyLevels'])->name('sync-levels.preview');
            Route::post('/sync-levels', [JadwalUjianController::class, 'copyLevels'])->name('sync-levels');
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
            Route::get('/{ruang}/cetak-label-pintu', [RuangUjianController::class, 'printLabelPintu'])->name('print-label-pintu');
            Route::get('/{ruang}/cetak-denah', [RuangUjianController::class, 'printDenahDuduk'])->name('print-denah');

            // Setting Master Sesi Ujian
            Route::prefix('sesi')->name('sesi.')->group(function () {
                Route::post('/', [SesiUjianController::class, 'store'])->name('store');
                Route::put('/{sesi}', [SesiUjianController::class, 'update'])->name('update');
                Route::delete('/{sesi}', [SesiUjianController::class, 'destroy'])->name('destroy');
                Route::post('/reset-default', [SesiUjianController::class, 'resetDefault'])->name('reset-default');
            });
        });

        Route::prefix('jadwal-pengawas')->name('pengawas.')->group(function () {
            Route::get('/', [JadwalPengawasController::class, 'index'])->name('index');
            Route::post('/', [JadwalPengawasController::class, 'store'])->name('store');
            Route::post('/auto-plot', [JadwalPengawasController::class, 'autoPlot'])->name('auto-plot');
            Route::post('/reset', [JadwalPengawasController::class, 'reset'])->name('reset');
            Route::post('/import-pdf', [JadwalPengawasController::class, 'importPdf'])->name('import-pdf');
            Route::delete('/penugasan/{pengawas}', [JadwalPengawasController::class, 'destroy'])->name('destroy');
            Route::post('/mapping', [JadwalPengawasController::class, 'storeMapping'])->name('mapping.store');
            Route::post('/mapping/bulk', [JadwalPengawasController::class, 'bulkMapping'])->name('mapping.bulk');
            Route::delete('/mapping', [JadwalPengawasController::class, 'destroyMapping'])->name('mapping.destroy');
            Route::get('/swap-candidates', [JadwalPengawasController::class, 'getSwapCandidates'])->name('swap.candidates');
            Route::post('/swap', [JadwalPengawasController::class, 'executeSwap'])->name('swap.execute');
            Route::get('/cetak-daftar-hadir', [JadwalPengawasController::class, 'printDaftarHadir'])->name('print-daftar-hadir');
            Route::get('/cetak-matriks', [JadwalPengawasController::class, 'printMatriks'])->name('print-matriks');
            Route::get('/cetak-tugas-guru', [JadwalPengawasController::class, 'printJadwalGuru'])->name('print-jadwal-guru');

            // Presensi Pengawas Admin
            Route::get('/presensi', [PengawasAbsensiAdminController::class, 'index'])->name('presensi.index');
            Route::match(['GET', 'POST'], '/presensi/toggle-pengisian-awal', [PengawasAbsensiAdminController::class, 'togglePengisianAwal'])->name('presensi.toggle-pengisian-awal');
            Route::post('/presensi/{jadwalPengawas}', [PengawasAbsensiAdminController::class, 'update'])->name('presensi.update');
            Route::get('/presensi/cetak', [PengawasAbsensiAdminController::class, 'printRekap'])->name('presensi.print');
            Route::get('/presensi/export', [PengawasAbsensiAdminController::class, 'exportExcel'])->name('presensi.export');
        });

        // 5. Sub-Menu 4: Cetak Kartu Ujian & Manajemen Peserta
        Route::prefix('kartu-ujian')->name('kartu.')->group(function () {
            Route::get('/', [PesertaKartuUjianController::class, 'index'])->name('index');
            Route::post('/sync', [PesertaKartuUjianController::class, 'sync'])->name('sync');
            Route::post('/generate-nomor', [PesertaKartuUjianController::class, 'generateNomor'])->name('generate-nomor');
            Route::post('/allocate-rooms', [PesertaKartuUjianController::class, 'allocateRooms'])->name('allocate-rooms');
            Route::get('/available-students', [PesertaKartuUjianController::class, 'getAvailableStudents'])->name('available-students');
            Route::post('/peserta', [PesertaKartuUjianController::class, 'store'])->name('store');
            Route::put('/peserta/{peserta}', [PesertaKartuUjianController::class, 'update'])->name('update');
            Route::delete('/peserta/{peserta}', [PesertaKartuUjianController::class, 'destroy'])->name('destroy');
            Route::match(['GET', 'POST'], '/cetak', [PesertaKartuUjianController::class, 'printCards'])->name('print');
            Route::match(['GET', 'POST'], '/cetak-label-meja', [PesertaKartuUjianController::class, 'printLabelMeja'])->name('print-label-meja');
        });

        // 6. Sub-Menu 5: Administrasi & Berkas Ruang Ujian (Fase 1)
        Route::prefix('administrasi')->name('administrasi.')->group(function () {
            Route::get('/', [AdministrasiUjianController::class, 'index'])->name('index');
            Route::get('/daftar-hadir-peserta', [AdministrasiUjianController::class, 'printDaftarHadirPeserta'])->name('daftar-hadir-peserta');
            Route::get('/berita-acara', [AdministrasiUjianController::class, 'printBeritaAcara'])->name('berita-acara');
            Route::post('/berita-acara/{beritaAcara}/verifikasi', [AdministrasiUjianController::class, 'verifikasiBeritaAcara'])->name('berita-acara.verifikasi');
            Route::get('/label-amplop', [AdministrasiUjianController::class, 'printLabelAmplop'])->name('label-amplop');
            Route::get('/tata-tertib', [AdministrasiUjianController::class, 'printTataTertib'])->name('tata-tertib');
            Route::post('/tata-tertib/update', [AdministrasiUjianController::class, 'updateTataTertib'])->name('tata-tertib.update');
            Route::post('/tata-tertib/reset', [AdministrasiUjianController::class, 'resetTataTertib'])->name('tata-tertib.reset');
            Route::get('/sk-panitia', [AdministrasiUjianController::class, 'printSkPanitia'])->name('sk-panitia');
            Route::post('/sk-panitia/update', [AdministrasiUjianController::class, 'updateSkPanitia'])->name('sk-panitia.update');
            Route::post('/sk-panitia/reset', [AdministrasiUjianController::class, 'resetSkPanitia'])->name('sk-panitia.reset');
        });

        // 7. Sub-Menu 6: Tanda Tangan Kepala & Pengaturan Aset Kartu
        Route::prefix('pengaturan-ttd')->name('pengaturan-ttd.')->group(function () {
            Route::get('/', [PengaturanTtdController::class, 'index'])->name('index');
            Route::put('/{pengaturan}', [PengaturanTtdController::class, 'update'])->name('update');
            Route::delete('/{pengaturan}/ttd', [PengaturanTtdController::class, 'deleteSignature'])->name('delete-signature');
            Route::delete('/{pengaturan}/stempel', [PengaturanTtdController::class, 'deleteStamp'])->name('delete-stamp');
        });
    });
