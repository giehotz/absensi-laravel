<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guru\AttendanceReportController;
use App\Http\Controllers\Guru\HomeroomClassController;
use App\Http\Controllers\Guru\LeaveRequestController;
use App\Http\Controllers\Guru\ProfileController;
use App\Http\Controllers\ManualAttendanceController;
use Illuminate\Support\Facades\Route;

// Dashboard Guru
Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');

// Perizinan Siswa (Guru / Wali Kelas)
Route::get('/perizinan', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
Route::post('/perizinan', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
Route::get('/perizinan/export', [LeaveRequestController::class, 'exportExcel'])->name('leave-requests.export');
Route::patch('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
Route::patch('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

// Presensi Manual Siswa (Guru)
Route::get('/presensi/manual', [ManualAttendanceController::class, 'guru'])->name('attendance.manual');
Route::post('/presensi/manual', [ManualAttendanceController::class, 'store'])->name('attendance.manual.store');

// Rekap Presensi Siswa (Guru)
Route::get('/rekap', [AttendanceReportController::class, 'index'])->name('reports.attendance');
Route::get('/rekap/export', [AttendanceReportController::class, 'exportExcel'])->name('reports.attendance.export');

// Kelas Binaan (Wali Kelas)
Route::get('/kelas-binaan', [HomeroomClassController::class, 'index'])->name('classes.binaan');
Route::get('/kelas-binaan/export', [HomeroomClassController::class, 'exportExcel'])->name('classes.binaan.export');
Route::post('/kelas-binaan/catatan', [HomeroomClassController::class, 'storeNote'])->name('classes.binaan.notes.store');
Route::delete('/kelas-binaan/catatan/{studentNote}', [HomeroomClassController::class, 'destroyNote'])->name('classes.binaan.notes.destroy');

// Profil Saya (Guru)
Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
