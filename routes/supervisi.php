<?php

use App\Http\Controllers\Supervisi\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Supervisi\Admin\InstrumenController as AdminInstrumenController;
use App\Http\Controllers\Supervisi\Admin\JadwalController as AdminJadwalController;
use App\Http\Controllers\Supervisi\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Supervisi\Admin\PenilaianController as AdminPenilaianController;
use App\Http\Controllers\Supervisi\Admin\SupervisorController as AdminSupervisorController;
use App\Http\Controllers\Supervisi\CetakController;
use App\Http\Controllers\Supervisi\DashboardController;
use App\Http\Controllers\Supervisi\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Supervisi\Guru\DokumenAjarController as GuruDokumenAjarController;
use App\Http\Controllers\Supervisi\Guru\HasilController as GuruHasilController;
use App\Http\Controllers\Supervisi\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Supervisi\Guru\PraObservasiController as GuruPraObservasiController;
use App\Http\Controllers\Supervisi\Guru\SiklusController as GuruSiklusController;
use App\Http\Controllers\Supervisi\Guru\TindakLanjutController as GuruTindakLanjutController;
use App\Http\Controllers\Supervisi\Kepala\DashboardController as KepalaDashboardController;
use App\Http\Controllers\Supervisi\Kepala\DokumenAjarController as KepalaDokumenAjarController;
use App\Http\Controllers\Supervisi\Kepala\HasilController as KepalaHasilController;
use App\Http\Controllers\Supervisi\Kepala\JadwalController as KepalaJadwalController;
use App\Http\Controllers\Supervisi\Kepala\PenilaianController as KepalaPenilaianController;
use App\Http\Controllers\Supervisi\SignatureController;
use App\Http\Controllers\Supervisi\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisi\Supervisor\DokumenAjarController as SupervisorDokumenAjarController;
use App\Http\Controllers\Supervisi\Supervisor\GuruBinaanController as SupervisorGuruBinaanController;
use App\Http\Controllers\Supervisi\Supervisor\HasilController as SupervisorHasilController;
use App\Http\Controllers\Supervisi\Supervisor\JadwalController as SupervisorJadwalController;
use App\Http\Controllers\Supervisi\Supervisor\PenilaianController as SupervisorPenilaianController;
use App\Http\Controllers\Supervisi\Supervisor\PraObservasiController as SupervisorPraObservasiController;
use App\Http\Controllers\Supervisi\Supervisor\TindakLanjutController as SupervisorTindakLanjutController;
use App\Http\Controllers\Supervisi\VerifikasiController;
use Illuminate\Support\Facades\Route;

// Publik: Verifikasi QR Code Tanda Tangan Digital
Route::get('/verifikasi/supervisi/{token}', [VerifikasiController::class, 'show'])
    ->name('supervisi.verifikasi');

Route::middleware(['web', 'auth', 'supervisi.role:admin,kepala,supervisor,guru'])
    ->prefix('supervisi')
    ->name('supervisi.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/switch-role/{role}', [DashboardController::class, 'switchRole'])->name('switch-role');
        Route::post('/signature/{jadwal}/sign', [SignatureController::class, 'sign'])->name('signature.sign');
        Route::get('/cetak/{jadwal}/{type?}', [CetakController::class, 'show'])->name('cetak');
        Route::get('/cetak/{jadwal}/instrumen/{jenis}', [CetakController::class, 'showInstrument'])->name('cetak.instrumen');
        Route::get('/cetak-portofolio/{teacher}', [CetakController::class, 'portfolio'])->name('cetak.portofolio');

        // Panel Admin Supervisi
        Route::middleware('supervisi.role:admin')
            ->prefix('admin')
            ->name('admin.')
            ->group(function (): void {
                Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

                Route::get('/supervisors', [AdminSupervisorController::class, 'index'])->name('supervisors.index');
                Route::post('/supervisors', [AdminSupervisorController::class, 'store'])->name('supervisors.store');
                Route::post('/supervisors/{supervisor}/toggle', [AdminSupervisorController::class, 'toggle'])->name('supervisors.toggle');
                Route::delete('/supervisors/{supervisor}', [AdminSupervisorController::class, 'destroy'])->name('supervisors.destroy');

                Route::get('/instrumen', [AdminInstrumenController::class, 'index'])->name('instrumen.index');
                Route::post('/instrumen/jenis', [AdminInstrumenController::class, 'storeJenis'])->name('instrumen.jenis.store');
                Route::put('/instrumen/jenis/{jenis}', [AdminInstrumenController::class, 'updateJenis'])->name('instrumen.jenis.update');
                Route::delete('/instrumen/jenis/{jenis}', [AdminInstrumenController::class, 'destroyJenis'])->name('instrumen.jenis.destroy');
                Route::post('/instrumen/aspek', [AdminInstrumenController::class, 'storeAspek'])->name('instrumen.aspek.store');
                Route::put('/instrumen/aspek/{aspek}', [AdminInstrumenController::class, 'updateAspek'])->name('instrumen.aspek.update');
                Route::delete('/instrumen/aspek/{aspek}', [AdminInstrumenController::class, 'destroyAspek'])->name('instrumen.aspek.destroy');

                Route::get('/jadwal', [AdminJadwalController::class, 'index'])->name('jadwal.index');
                Route::get('/jadwal/create', [AdminJadwalController::class, 'create'])->name('jadwal.create');
                Route::post('/jadwal', [AdminJadwalController::class, 'store'])->name('jadwal.store');
                Route::get('/jadwal/generate', [AdminJadwalController::class, 'generateForm'])->name('jadwal.generate');
                Route::post('/jadwal/generate/preview', [AdminJadwalController::class, 'generatePreview'])->name('jadwal.generate.preview');
                Route::post('/jadwal/generate/store', [AdminJadwalController::class, 'generateStore'])->name('jadwal.generate.store');
                Route::post('/jadwal/bulk-destroy', [AdminJadwalController::class, 'bulkDestroy'])->name('jadwal.bulk-destroy');
                Route::get('/jadwal/print', [AdminJadwalController::class, 'print'])->name('jadwal.print');
                Route::get('/jadwal/{jadwal}/edit', [AdminJadwalController::class, 'edit'])->name('jadwal.edit');
                Route::put('/jadwal/{jadwal}', [AdminJadwalController::class, 'update'])->name('jadwal.update');
                Route::delete('/jadwal/{jadwal}', [AdminJadwalController::class, 'destroy'])->name('jadwal.destroy');

                Route::get('/penilaian/{jadwal}/edit', [AdminPenilaianController::class, 'edit'])->name('penilaian.edit');
                Route::get('/penilaian/{jadwal}/form/{jenis}', [AdminPenilaianController::class, 'showForm'])->name('penilaian.form');
                Route::post('/penilaian/{jadwal}/save', [AdminPenilaianController::class, 'saveKomponen'])->name('penilaian.save');
                Route::post('/penilaian/{jadwal}/complete', [AdminPenilaianController::class, 'complete'])->name('penilaian.complete');
                Route::post('/penilaian/{jadwal}/foto', [AdminPenilaianController::class, 'uploadFoto'])->name('penilaian.foto.upload');
                Route::delete('/penilaian/foto/{foto}', [AdminPenilaianController::class, 'deleteFoto'])->name('penilaian.foto.delete');

                Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
                Route::get('/laporan/{jadwal}', [AdminLaporanController::class, 'detail'])->name('laporan.detail');
                Route::get('/laporan-print/rekap', [AdminLaporanController::class, 'printRekap'])->name('laporan.print-rekap');
                Route::get('/laporan-print/instrumen', [AdminLaporanController::class, 'printByInstrument'])->name('laporan.print-instrumen');
                Route::get('/laporan-export/excel', [AdminLaporanController::class, 'exportExcel'])->name('laporan.export-excel');
                Route::get('/laporan-export/{jadwal}/excel', [AdminLaporanController::class, 'exportDetailExcel'])->name('laporan.export-detail-excel');
                Route::post('/laporan-export/batch-excel', [AdminLaporanController::class, 'batchExcel'])->name('laporan.batch-excel');
                Route::get('/laporan-export/batch-all', [AdminLaporanController::class, 'exportAllBatch'])->name('laporan.batch-all');

                Route::post('/signature/{jadwal}/sign-all', [SignatureController::class, 'signAllAdmin'])->name('signature.sign-all');
                Route::post('/signature/{jadwal}/reset', [SignatureController::class, 'reset'])->name('signature.reset');
            });

        // Panel Supervisor
        Route::middleware('supervisi.role:supervisor')
            ->prefix('supervisor')
            ->name('supervisor.')
            ->group(function (): void {
                Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
                Route::get('/guru-binaan', [SupervisorGuruBinaanController::class, 'index'])->name('guru-binaan');
                Route::get('/jadwal', [SupervisorJadwalController::class, 'index'])->name('jadwal.index');
                Route::get('/jadwal/{jadwal}', [SupervisorJadwalController::class, 'show'])->name('jadwal.show');

                Route::get('/pra-observasi/{jadwal}', [SupervisorPraObservasiController::class, 'show'])->name('pra-observasi.show');
                Route::post('/pra-observasi/{jadwal}/agree', [SupervisorPraObservasiController::class, 'agree'])->name('pra-observasi.agree');

                Route::get('/dokumen-ajar/{jadwal}', [SupervisorDokumenAjarController::class, 'index'])->name('dokumen-ajar.index');
                Route::post('/dokumen-ajar/{dokumen}/verify', [SupervisorDokumenAjarController::class, 'verify'])->name('dokumen-ajar.verify');

                Route::get('/penilaian/{jadwal}/edit', [SupervisorPenilaianController::class, 'edit'])->name('penilaian.edit');
                Route::get('/penilaian/{jadwal}/form/{jenis}', [SupervisorPenilaianController::class, 'showForm'])->name('penilaian.form');
                Route::post('/penilaian/{jadwal}/save', [SupervisorPenilaianController::class, 'saveKomponen'])->name('penilaian.save');
                Route::post('/penilaian/{jadwal}/complete', [SupervisorPenilaianController::class, 'complete'])->name('penilaian.complete');
                Route::post('/penilaian/{jadwal}/foto', [SupervisorPenilaianController::class, 'uploadFoto'])->name('penilaian.foto.upload');
                Route::delete('/penilaian/foto/{foto}', [SupervisorPenilaianController::class, 'deleteFoto'])->name('penilaian.foto.delete');

                Route::get('/tindak-lanjut/{jadwal}', [SupervisorTindakLanjutController::class, 'show'])->name('tindak-lanjut.show');
                Route::post('/tindak-lanjut/{jadwal}/feedback-c', [SupervisorTindakLanjutController::class, 'feedbackC'])->name('tindak-lanjut.feedback-c');
                Route::post('/tindak-lanjut/{jadwal}/evaluate-d', [SupervisorTindakLanjutController::class, 'evaluateD'])->name('tindak-lanjut.evaluate-d');
                Route::post('/tindak-lanjut/{jadwal}/coaching', [SupervisorTindakLanjutController::class, 'storeCoachingPasca'])->name('tindak-lanjut.coaching');

                Route::get('/hasil', [SupervisorHasilController::class, 'index'])->name('hasil.index');
                Route::get('/hasil/{jadwal}', [SupervisorHasilController::class, 'show'])->name('hasil.show');
            });

        // Panel Kepala Madrasah (Akses Tulis Penuh di Supervisi)
        Route::middleware('supervisi.role:kepala')
            ->prefix('kepala')
            ->name('kepala.')
            ->group(function (): void {
                Route::get('/dashboard', [KepalaDashboardController::class, 'index'])->name('dashboard');
                Route::get('/performance', [KepalaDashboardController::class, 'performance'])->name('performance');

                Route::get('/jadwal', [KepalaJadwalController::class, 'index'])->name('jadwal.index');
                Route::get('/jadwal/create', [KepalaJadwalController::class, 'create'])->name('jadwal.create');
                Route::post('/jadwal', [KepalaJadwalController::class, 'store'])->name('jadwal.store');
                Route::get('/jadwal/export-excel', [KepalaJadwalController::class, 'exportExcel'])->name('jadwal.export-excel');
                Route::get('/jadwal/print', [KepalaJadwalController::class, 'print'])->name('jadwal.print');
                Route::get('/jadwal/{jadwal}', [KepalaJadwalController::class, 'show'])->name('jadwal.show');
                Route::get('/jadwal/{jadwal}/edit', [KepalaJadwalController::class, 'edit'])->name('jadwal.edit');
                Route::put('/jadwal/{jadwal}', [KepalaJadwalController::class, 'update'])->name('jadwal.update');
                Route::delete('/jadwal/{jadwal}', [KepalaJadwalController::class, 'destroy'])->name('jadwal.destroy');

                Route::get('/pra-observasi/{jadwal}', [SupervisorPraObservasiController::class, 'show'])->name('pra-observasi.show');
                Route::post('/pra-observasi/{jadwal}/agree', [SupervisorPraObservasiController::class, 'agree'])->name('pra-observasi.agree');

                Route::get('/dokumen-ajar/{jadwal}', [KepalaDokumenAjarController::class, 'index'])->name('dokumen-ajar.index');
                Route::post('/dokumen-ajar/{dokumen}/verify', [KepalaDokumenAjarController::class, 'verify'])->name('dokumen-ajar.verify');

                Route::get('/penilaian/{jadwal}/edit', [KepalaPenilaianController::class, 'edit'])->name('penilaian.edit');
                Route::get('/penilaian/{jadwal}/form/{jenis}', [KepalaPenilaianController::class, 'showForm'])->name('penilaian.form');
                Route::post('/penilaian/{jadwal}/save', [KepalaPenilaianController::class, 'saveKomponen'])->name('penilaian.save');
                Route::post('/penilaian/{jadwal}/complete', [KepalaPenilaianController::class, 'complete'])->name('penilaian.complete');
                Route::post('/penilaian/{jadwal}/foto', [KepalaPenilaianController::class, 'uploadFoto'])->name('penilaian.foto.upload');
                Route::delete('/penilaian/foto/{foto}', [KepalaPenilaianController::class, 'deleteFoto'])->name('penilaian.foto.delete');

                Route::get('/tindak-lanjut/{jadwal}', [SupervisorTindakLanjutController::class, 'show'])->name('tindak-lanjut.show');
                Route::post('/tindak-lanjut/{jadwal}/feedback-c', [SupervisorTindakLanjutController::class, 'feedbackC'])->name('tindak-lanjut.feedback-c');
                Route::post('/tindak-lanjut/{jadwal}/evaluate-d', [SupervisorTindakLanjutController::class, 'evaluateD'])->name('tindak-lanjut.evaluate-d');
                Route::post('/tindak-lanjut/{jadwal}/coaching', [SupervisorTindakLanjutController::class, 'storeCoachingPasca'])->name('tindak-lanjut.coaching');

                Route::get('/hasil', [KepalaHasilController::class, 'index'])->name('hasil.index');
                Route::get('/hasil-print/instrumen', [KepalaHasilController::class, 'printByInstrument'])->name('hasil.print-instrumen');
                Route::get('/hasil/{jadwal}', [KepalaHasilController::class, 'show'])->name('hasil.show');
            });

        // Panel Guru
        Route::middleware('supervisi.role:guru')
            ->prefix('guru')
            ->name('guru.')
            ->group(function (): void {
                Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
                Route::get('/jadwal', [GuruJadwalController::class, 'index'])->name('jadwal.index');
                Route::get('/siklus/{jadwal}', [GuruSiklusController::class, 'show'])->name('siklus.show');

                Route::get('/dokumen-ajar', [GuruDokumenAjarController::class, 'index'])->name('dokumen-ajar.index');
                Route::get('/dokumen-ajar/{jadwal}', [GuruDokumenAjarController::class, 'manage'])->name('dokumen-ajar.manage');
                Route::post('/dokumen-ajar/{jadwal}', [GuruDokumenAjarController::class, 'store'])->name('dokumen-ajar.store');
                Route::delete('/dokumen-ajar/item/{dokumen}', [GuruDokumenAjarController::class, 'destroy'])->name('dokumen-ajar.destroy');

                Route::get('/pra-observasi/{jadwal}', [GuruPraObservasiController::class, 'edit'])->name('pra-observasi.edit');
                Route::post('/pra-observasi/{jadwal}', [GuruPraObservasiController::class, 'save'])->name('pra-observasi.save');
                Route::post('/pra-observasi/{jadwal}/tarik', [GuruPraObservasiController::class, 'tarik'])->name('pra-observasi.tarik');

                Route::get('/tindak-lanjut/{jadwal}', [GuruTindakLanjutController::class, 'edit'])->name('tindak-lanjut.edit');
                Route::post('/tindak-lanjut/{jadwal}/form-c', [GuruTindakLanjutController::class, 'saveFormC'])->name('tindak-lanjut.save-c');
                Route::post('/tindak-lanjut/{jadwal}/form-d', [GuruTindakLanjutController::class, 'saveFormD'])->name('tindak-lanjut.save-d');

                Route::get('/hasil', [GuruHasilController::class, 'index'])->name('hasil.index');
                Route::get('/hasil/{jadwal}', [GuruHasilController::class, 'show'])->name('hasil.show');
            });
    });
