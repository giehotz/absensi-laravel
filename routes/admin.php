<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ApiClientController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\AttendanceUploadController;
use App\Http\Controllers\Admin\DatabaseMaintenanceController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\QrGeneratorController;
use App\Http\Controllers\Admin\SavingsController as AdminSavingsController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\ScheduleSlotController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentCardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentPromotionController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManualAttendanceController;
use Illuminate\Support\Facades\Route;

// Dashboard Administrator
Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

// Master Data Guru
Route::get('teachers/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.template');
Route::post('teachers/import', [TeacherController::class, 'importExcel'])->name('teachers.import');
Route::get('teachers/{teacher}/assignments', [TeacherController::class, 'getAssignments'])->name('teachers.assignments');
Route::match(['post', 'patch'], 'teachers/{teacher}/assignments', [TeacherController::class, 'updateAssignments'])->name('teachers.assignments.update');
Route::match(['post', 'patch'], 'teachers/{teacher}/savings-assignment', [TeacherController::class, 'updateSavingsAssignment'])->name('teachers.savings-assignment');
Route::patch('teachers/{teacher}/toggle-savings-officer', [TeacherController::class, 'toggleSavingsOfficer'])->name('teachers.toggle-savings-officer');
Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show']);

// Master Data Kelas
Route::get('classes/transfer', [SchoolClassController::class, 'transferView'])->name('classes.transfer');
Route::post('classes/transfer', [SchoolClassController::class, 'executeTransfer'])->name('classes.transfer.store');
Route::get('classes/{class}/transfer-data', [SchoolClassController::class, 'getTransferData'])->name('classes.transfer-data');
Route::get('classes/{class}/students', [SchoolClassController::class, 'students'])->name('classes.students');
Route::get('classes/{class}/students/template', [SchoolClassController::class, 'downloadStudentsTemplate'])->name('classes.students.template');
Route::post('classes/{class}/students/import', [SchoolClassController::class, 'importStudentsExcel'])->name('classes.students.import');
Route::resource('classes', SchoolClassController::class)->except(['create', 'edit', 'show']);

// Kenaikan Kelas & Kelulusan Siswa
Route::prefix('students/promotion')->name('students.promotion.')->group(function () {
    Route::get('/', [StudentPromotionController::class, 'index'])->name('index');
    Route::post('/process', [StudentPromotionController::class, 'promote'])->name('process');
    Route::post('/copy-classes', [StudentPromotionController::class, 'copyClasses'])->name('copy-classes');
    Route::post('/quick-class', [StudentPromotionController::class, 'quickStoreClass'])->name('quick-class');
});

// Kartu Pelajar Siswa
Route::get('students/cards', [StudentCardController::class, 'index'])->name('students.cards');
Route::match(['get', 'post'], 'students/cards/print', [StudentCardController::class, 'print'])->name('students.cards.print');
Route::get('students/{student}/card', [StudentCardController::class, 'single'])->name('students.card.single');

// Master Data Siswa & Mata Pelajaran
Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.template');
Route::post('students/import', [StudentController::class, 'importExcel'])->name('students.import');
Route::post('students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
Route::resource('students', StudentController::class)->except(['create', 'edit', 'show']);
Route::post('subjects/sync', [SubjectController::class, 'sync'])->name('subjects.sync');
Route::resource('subjects', SubjectController::class)->except(['create', 'edit', 'show']);

// Template Slot Jam KBM (Integrasi Schedules)
Route::get('schedules/slots', [ScheduleSlotController::class, 'index'])->name('schedules.slots.index');
Route::post('schedules/slots', [ScheduleSlotController::class, 'store'])->name('schedules.slots.store');
Route::post('schedules/slots/reset-default', [ScheduleSlotController::class, 'resetDefault'])->name('schedules.slots.reset-default');

// Jadwal Pelajaran (Admin)
Route::resource('schedules', ScheduleController::class)->except(['create', 'edit', 'show']);

// Manajemen Tahun Ajaran
Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
Route::put('academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
Route::patch('academic-years/{academicYear}/toggle', [AcademicYearController::class, 'toggleStatus'])->name('academic-years.toggle');

// Manajemen Kalender Hari Libur
Route::get('holidays', [HolidayController::class, 'index'])->name('holidays.index');
Route::post('holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');
Route::post('holidays', [HolidayController::class, 'store'])->name('holidays.store');
Route::patch('holidays/{holiday}/toggle', [HolidayController::class, 'toggleActive'])->name('holidays.toggle');
Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');

// Laporan Presensi Siswa
Route::get('reports/attendance', [AttendanceReportController::class, 'index'])->name('reports.attendance');
Route::get('reports/attendance/export', [AttendanceReportController::class, 'exportExcel'])->name('reports.attendance.export');

// Pengaturan Sistem Absensi
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

// Pemeliharaan & Pengarsipan Database
Route::get('database-maintenance/preview/{academicYear}', [DatabaseMaintenanceController::class, 'preview'])->name('database.preview');
Route::post('database-maintenance/archive', [DatabaseMaintenanceController::class, 'archive'])->name('database.archive');
Route::post('database-maintenance/restore', [DatabaseMaintenanceController::class, 'restore'])->name('database.restore');
Route::post('database-maintenance/optimize', [DatabaseMaintenanceController::class, 'optimize'])->name('database.optimize');

// Presensi Manual & Upload Siswa (Admin)
Route::get('attendances/manual', [ManualAttendanceController::class, 'admin'])->name('attendances.manual');
Route::post('attendances/manual', [ManualAttendanceController::class, 'store'])->name('attendances.manual.store');
Route::get('attendances/template', [AttendanceUploadController::class, 'downloadTemplate'])->name('attendances.template');
Route::post('attendances/upload', [AttendanceUploadController::class, 'upload'])->name('attendances.upload');
Route::get('attendances/batches', [AttendanceUploadController::class, 'batches'])->name('attendances.batches');
Route::post('attendances/monthly-fill', [ManualAttendanceController::class, 'monthlyFill'])->name('attendances.monthly-fill');

// Manajemen & Monitoring Tabungan Siswa (Admin)
Route::get('savings', [AdminSavingsController::class, 'index'])->name('savings.index');
Route::get('savings/export', [AdminSavingsController::class, 'exportExcel'])->name('savings.export');
Route::get('savings/receipt/{transaction}', [AdminSavingsController::class, 'receipt'])->name('savings.receipt');
Route::post('savings/transactions/{transaction}/correct', [AdminSavingsController::class, 'correct'])->name('savings.transactions.correct');
Route::get('savings/classes/{class}/students', [AdminSavingsController::class, 'classStudents'])->name('savings.class-students');

// Manajemen API Client & Integrasi
Route::get('api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
Route::post('api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
Route::put('api-clients/{client}', [ApiClientController::class, 'update'])->name('api-clients.update');
Route::post('api-clients/{client}/regenerate', [ApiClientController::class, 'regenerateKey'])->name('api-clients.regenerate');
Route::patch('api-clients/{client}/toggle', [ApiClientController::class, 'toggle'])->name('api-clients.toggle');
Route::delete('api-clients/{client}', [ApiClientController::class, 'destroy'])->name('api-clients.destroy');

// QR Generator
Route::get('qr-generator/{qrGenerator}/download/{format}', [QrGeneratorController::class, 'download'])->name('qr-generator.download');
Route::resource('qr-generator', QrGeneratorController::class);
