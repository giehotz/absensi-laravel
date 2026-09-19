<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\DatabaseMaintenanceController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentCardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect Home ke Dashboard atau Login
Route::get('/', function () {
    if (Auth::check()) {
        return match (Auth::user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            'orangtua' => redirect()->route('orangtua.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// Autentikasi
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard Berdasarkan Role
Route::middleware(['auth'])->group(function () {
    // Administrator
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // Master Data Management
        Route::get('teachers/template', [TeacherController::class, 'downloadTemplate'])->name('teachers.template');
        Route::post('teachers/import', [TeacherController::class, 'importExcel'])->name('teachers.import');
        Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show']);
        Route::get('classes/{class}/students', [SchoolClassController::class, 'students'])->name('classes.students');
        Route::get('classes/{class}/students/template', [SchoolClassController::class, 'downloadStudentsTemplate'])->name('classes.students.template');
        Route::post('classes/{class}/students/import', [SchoolClassController::class, 'importStudentsExcel'])->name('classes.students.import');
        Route::resource('classes', SchoolClassController::class)->except(['create', 'edit', 'show']);
        Route::get('students/cards', [StudentCardController::class, 'index'])->name('students.cards');
        Route::match(['get', 'post'], 'students/cards/print', [StudentCardController::class, 'print'])->name('students.cards.print');
        Route::get('students/{student}/card', [StudentCardController::class, 'single'])->name('students.card.single');
        Route::resource('students', StudentController::class)->except(['create', 'edit', 'show']);
        Route::resource('subjects', SubjectController::class)->except(['create', 'edit', 'show']);

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
    });

    // Guru
    Route::middleware(['role:guru'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');
    });

    // Siswa
    Route::middleware(['role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'siswa'])->name('dashboard');
    });

    // Orang Tua
    Route::middleware(['role:orangtua'])->prefix('orangtua')->name('orangtua.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'orangtua'])->name('dashboard');
    });
});
