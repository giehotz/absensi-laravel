<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\AssessmentController;
use App\Http\Controllers\Guru\AttendanceReportController;
use App\Http\Controllers\Guru\AttendanceUploadController;
use App\Http\Controllers\Guru\CalendarController;
use App\Http\Controllers\Guru\HomeroomClassController;
use App\Http\Controllers\Guru\LeaveRequestController;
use App\Http\Controllers\Guru\ProfileController;
use App\Http\Controllers\Guru\QrGeneratorController;
use App\Http\Controllers\Guru\ScheduleController;
use App\Http\Controllers\Guru\TeachingJournalController;
use App\Http\Controllers\ManualAttendanceController;
use Illuminate\Support\Facades\Route;

// Dashboard Guru
Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

// Jurnal Kegiatan Harian Guru Mengajar
Route::get('/jurnal/template', [TeachingJournalController::class, 'downloadTemplate'])->name('teaching-journals.template');
Route::post('/jurnal/upload', [TeachingJournalController::class, 'uploadExcel'])->name('teaching-journals.upload');
Route::get('/jurnal/import/preview', [TeachingJournalController::class, 'previewImport'])->name('teaching-journals.import.preview');
Route::post('/jurnal/import/publish', [TeachingJournalController::class, 'publishImport'])->name('teaching-journals.import.publish');
Route::patch('/jurnal/{teachingJournal}/toggle-share', [TeachingJournalController::class, 'toggleShare'])->name('teaching-journals.toggle-share');
Route::get('/jurnal/cetak', [TeachingJournalController::class, 'print'])->name('teaching-journals.print');
Route::get('/jurnal/check-attendance', [TeachingJournalController::class, 'checkAttendance'])->name('teaching-journals.check-attendance');
Route::resource('/jurnal', TeachingJournalController::class)->names('teaching-journals');

// Kalender Pendidikan & Hari Libur Sekolah
Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.index');

// Jadwal Mengajar & Jadwal Kelas
Route::get('/jadwal', [ScheduleController::class, 'index'])->name('jadwal');
Route::get('/jadwal/index', [ScheduleController::class, 'index'])->name('schedule');

// Perizinan Siswa (Guru / Wali Kelas)
Route::get('/perizinan', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
Route::post('/perizinan', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
Route::get('/perizinan/export', [LeaveRequestController::class, 'exportExcel'])->name('leave-requests.export');
Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

// Presensi Manual & Upload Siswa (Guru)
Route::get('/presensi/manual', [ManualAttendanceController::class, 'guru'])->name('attendance.manual');
Route::post('/presensi/manual', [ManualAttendanceController::class, 'store'])->name('attendance.manual.store');
Route::get('/presensi/template', [AttendanceUploadController::class, 'downloadTemplate'])->name('attendance.template');
Route::post('/presensi/upload', [AttendanceUploadController::class, 'upload'])->name('attendance.upload');
Route::get('/presensi/batches', [AttendanceUploadController::class, 'batches'])->name('attendance.batches');

// Rekap Presensi Siswa (Guru)
Route::get('/rekap', [AttendanceReportController::class, 'index'])->name('reports.attendance');
Route::get('/rekap/export', [AttendanceReportController::class, 'exportExcel'])->name('reports.attendance.export');

// Kelas Binaan (Wali Kelas)
Route::get('/kelas-binaan', [HomeroomClassController::class, 'index'])->name('classes.binaan');
Route::get('/kelas-binaan/export', [HomeroomClassController::class, 'exportExcel'])->name('classes.binaan.export');
Route::post('/kelas-binaan/catatan', [HomeroomClassController::class, 'storeNote'])->name('classes.binaan.notes.store');
Route::delete('/kelas-binaan/catatan/{studentNote}', [HomeroomClassController::class, 'destroyNote'])->name('classes.binaan.notes.destroy');
Route::put('/kelas-binaan/students/{student}', [HomeroomClassController::class, 'updateStudent'])->name('classes.binaan.students.update');
Route::post('/kelas-binaan/students/{student}/reset-password', [HomeroomClassController::class, 'resetPassword'])->name('classes.binaan.students.reset-password');

// Profil Saya (Guru)
Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// QR Generator (Guru)
Route::get('/qr-generator/{qrGenerator}/download/{format}', [QrGeneratorController::class, 'download'])->name('qr-generator.download');
Route::resource('/qr-generator', QrGeneratorController::class);
// Penilaian Sumatif Siswa (Guru Mapel & Wali Kelas)
Route::prefix('penilaian')->name('penilaian.')->group(function () {
    Route::get('/', [AssessmentController::class, 'index'])->name('index');
    Route::get('/create', [AssessmentController::class, 'create'])->name('create');
    Route::post('/', [AssessmentController::class, 'store'])->name('store');
    Route::get('/{package}', [AssessmentController::class, 'show'])->name('show');
    Route::post('/{package}/add-assessment', [AssessmentController::class, 'addAssessment'])->name('add-assessment');
    Route::delete('/{package}', [AssessmentController::class, 'destroy'])->name('destroy');
    Route::get('/{package}/template', [AssessmentController::class, 'downloadTemplate'])->name('template');
    Route::post('/{package}/upload', [AssessmentController::class, 'uploadExcel'])->name('upload');
    Route::get('/{package}/preview', [AssessmentController::class, 'previewImport'])->name('preview');
    Route::post('/{package}/publish', [AssessmentController::class, 'publishImport'])->name('publish');
    Route::put('/{package}/quick-update', [AssessmentController::class, 'quickUpdateScore'])->name('quick-update');
    Route::patch('/{package}/lock', [AssessmentController::class, 'lock'])->name('lock');
    Route::get('/{package}/export', [AssessmentController::class, 'exportExcel'])->name('export');
    Route::get('/{package}/cetak', [AssessmentController::class, 'printPdf'])->name('print');
});
