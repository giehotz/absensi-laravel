<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ApiClientController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\DatabaseMaintenanceController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentCardController;
use App\Http\Controllers\Admin\StudentController;
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
Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show']);

// Master Data Kelas
Route::get('classes/transfer', [SchoolClassController::class, 'transferView'])->name('classes.transfer');
Route::post('classes/transfer', [SchoolClassController::class, 'executeTransfer'])->name('classes.transfer.store');
Route::get('classes/{class}/transfer-data', [SchoolClassController::class, 'getTransferData'])->name('classes.transfer-data');
Route::get('classes/{class}/students', [SchoolClassController::class, 'students'])->name('classes.students');
Route::get('classes/{class}/students/template', [SchoolClassController::class, 'downloadStudentsTemplate'])->name('classes.students.template');
Route::post('classes/{class}/students/import', [SchoolClassController::class, 'importStudentsExcel'])->name('classes.students.import');
Route::resource('classes', SchoolClassController::class)->except(['create', 'edit', 'show']);

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

// Jadwal Pelajaran (Admin)
Route::resource('schedules', ScheduleController::class)->except(['create', 'edit', 'show']);

// Manajemen Tahun Ajaran
Route::post('academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
Route::put('academic-years/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
Route::delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
Route::patch('academic-years/{academicYear}/toggle', [AcademicYearController::class, 'toggleStatus'])->name('academic-years.toggle');

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

// Presensi Manual Siswa (Admin)
Route::get('attendances/manual', [ManualAttendanceController::class, 'admin'])->name('attendances.manual');
Route::post('attendances/manual', [ManualAttendanceController::class, 'store'])->name('attendances.manual.store');

// Manajemen API Client & Integrasi
Route::get('api-clients', [ApiClientController::class, 'index'])->name('api-clients.index');
Route::post('api-clients', [ApiClientController::class, 'store'])->name('api-clients.store');
Route::put('api-clients/{client}', [ApiClientController::class, 'update'])->name('api-clients.update');
Route::post('api-clients/{client}/regenerate', [ApiClientController::class, 'regenerateKey'])->name('api-clients.regenerate');
Route::patch('api-clients/{client}/toggle', [ApiClientController::class, 'toggle'])->name('api-clients.toggle');
Route::delete('api-clients/{client}', [ApiClientController::class, 'destroy'])->name('api-clients.destroy');
