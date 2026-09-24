<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\AttendanceReportController;
use App\Http\Controllers\Guru\AttendanceUploadController;
use App\Http\Controllers\Guru\HomeroomClassController;
use App\Http\Controllers\Guru\LeaveRequestController;
use App\Http\Controllers\Guru\ProfileController;
use App\Http\Controllers\Guru\ScheduleController;
use App\Http\Controllers\ManualAttendanceController;
use Illuminate\Support\Facades\Route;

// Dashboard Guru
Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

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
