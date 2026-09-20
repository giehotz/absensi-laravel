<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\LeaveRequest;
use App\Models\NotificationLog;
use App\Models\QrToken;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard untuk Administrator Sekolah.
     */
    public function admin(): View
    {
        $today = Carbon::today()->toDateString();

        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_classes' => SchoolClass::count(),
            'hadir_today' => Attendance::where('date', $today)->where('status', 'hadir')->count(),
            'terlambat_today' => Attendance::where('date', $today)->where('status', 'terlambat')->count(),
            'sakit_today' => Attendance::where('date', $today)->where('status', 'sakit')->count(),
            'izin_today' => Attendance::where('date', $today)->where('status', 'izin')->count(),
            'alpa_today' => Attendance::where('date', $today)->where('status', 'alpa')->count(),
        ];

        $classes = SchoolClass::with(['homeroomTeacher.user', 'students'])->get();
        $recentAttendances = Attendance::with(['student.user', 'student.schoolClass'])
            ->where('date', $today)
            ->latest('check_in_time')
            ->take(10)
            ->get();

        $setting = AttendanceSetting::first() ?? new AttendanceSetting(['mode' => 'daily', 'tolerance_minutes' => 15]);

        return view('dashboard.admin', compact('stats', 'classes', 'recentAttendances', 'setting'));
    }

    /**
     * Dashboard untuk Guru / Tenaga Pengajar.
     */
    public function guru(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );
        $today = Carbon::today()->toDateString();
        $currentDayOfWeek = (int) Carbon::now()->dayOfWeekIso;

        // Jadwal mengajar guru hari ini
        $todaySchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $currentDayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Seluruh jadwal mingguan guru
        $weeklySchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Kelas binaan (sebagai Wali Kelas)
        $homeroomClasses = SchoolClass::with(['students.user', 'students.attendances' => function ($q) use ($today) {
            $q->where('date', $today);
        }])->where('homeroom_teacher_id', $teacher->id)->get();

        // Kelas & siswa dalam lingkup guru (jadwal mengajar + wali kelas)
        $taughtClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id')->unique();
        $homeroomClassIds = $homeroomClasses->pluck('id');
        $allClassIds = $taughtClassIds->merge($homeroomClassIds)->unique();
        $studentIds = Student::whereIn('school_class_id', $allClassIds)->pluck('id');

        // Statistik presensi hari ini untuk siswa binaan/ajar
        $attendancesToday = Attendance::whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->get();

        $totalStudents = $studentIds->count();
        $stats = [
            'total_students' => $totalStudents,
            'hadir' => $attendancesToday->where('status', 'hadir')->count(),
            'terlambat' => $attendancesToday->where('status', 'terlambat')->count(),
            'izin' => $attendancesToday->where('status', 'izin')->count(),
            'sakit' => $attendancesToday->where('status', 'sakit')->count(),
            'alpa' => $attendancesToday->where('status', 'alpa')->count(),
            'belum_absen' => max(0, $totalStudents - $attendancesToday->count()),
        ];

        // 10 Log presensi terbaru hari ini
        $recentAttendances = Attendance::with(['student.user', 'student.schoolClass', 'schedule.subject'])
            ->whereIn('student_id', $studentIds)
            ->where('date', $today)
            ->latest('check_in_time')
            ->latest('id')
            ->take(10)
            ->get();

        // Ringkasan Kehadiran 7 Hari Terakhir (Progress Bar Mingguan)
        $startDate = Carbon::today()->subDays(6)->toDateString();
        $weeklyAttendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$startDate, $today])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $weeklyHadir = (int) ($weeklyAttendances['hadir'] ?? 0);
        $weeklyTerlambat = (int) ($weeklyAttendances['terlambat'] ?? 0);
        $weeklySakit = (int) ($weeklyAttendances['sakit'] ?? 0);
        $weeklyIzin = (int) ($weeklyAttendances['izin'] ?? 0);
        $weeklyAlpa = (int) ($weeklyAttendances['alpa'] ?? 0);
        $weeklyTotal = $weeklyHadir + $weeklyTerlambat + $weeklySakit + $weeklyIzin + $weeklyAlpa;

        $weeklyStats = [
            'total' => $weeklyTotal,
            'hadir' => [
                'count' => $weeklyHadir,
                'percentage' => $weeklyTotal > 0 ? round(($weeklyHadir / $weeklyTotal) * 100, 1) : 0,
            ],
            'terlambat' => [
                'count' => $weeklyTerlambat,
                'percentage' => $weeklyTotal > 0 ? round(($weeklyTerlambat / $weeklyTotal) * 100, 1) : 0,
            ],
            'sakit' => [
                'count' => $weeklySakit,
                'percentage' => $weeklyTotal > 0 ? round(($weeklySakit / $weeklyTotal) * 100, 1) : 0,
            ],
            'izin' => [
                'count' => $weeklyIzin,
                'percentage' => $weeklyTotal > 0 ? round(($weeklyIzin / $weeklyTotal) * 100, 1) : 0,
            ],
            'alpa' => [
                'count' => $weeklyAlpa,
                'percentage' => $weeklyTotal > 0 ? round(($weeklyAlpa / $weeklyTotal) * 100, 1) : 0,
            ],
        ];

        // Permohonan izin yang pending untuk siswa kelas binaan guru (atau kelas yang diampu)
        $targetLeaveClassIds = $homeroomClassIds->isNotEmpty() ? $homeroomClassIds : $allClassIds;
        $pendingLeaveRequests = LeaveRequest::with(['student.user', 'student.schoolClass', 'requester'])
            ->whereHas('student', function ($q) use ($targetLeaveClassIds) {
                $q->whereIn('school_class_id', $targetLeaveClassIds);
            })
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('guru.dashboard', compact(
            'teacher',
            'todaySchedules',
            'weeklySchedules',
            'homeroomClasses',
            'stats',
            'recentAttendances',
            'weeklyStats',
            'pendingLeaveRequests'
        ));
    }

    /**
     * Dashboard untuk Siswa.
     */
    public function siswa(): View
    {
        $user = Auth::user();
        $student = $user->student;

        if (! $student) {
            // Fallback student demo jika belum ada relasi
            $firstClass = SchoolClass::first();
            $student = Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'school_class_id' => $firstClass ? $firstClass->id : 1,
                    'nis' => '12345',
                    'qr_code_identifier' => 'QR-STU-DEMO',
                    'gender' => 'L',
                ]
            );
        }

        $student->load(['schoolClass.homeroomTeacher.user']);

        $today = Carbon::today()->toDateString();

        // Presensi hari ini
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        // Token QR aktif hari ini
        $activeToken = QrToken::where('student_id', $student->id)
            ->where('is_used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        // Jika belum ada token hari ini, buat token otomatis untuk mockup
        if (! $activeToken) {
            $activeToken = QrToken::create([
                'student_id' => $student->id,
                'token' => 'TOKEN-'.$student->nis.'-'.Carbon::now()->format('His'),
                'expires_at' => Carbon::now()->endOfDay(),
                'is_used' => false,
            ]);
        }

        // Riwayat presensi 7 hari terakhir
        $history = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->take(7)
            ->get();

        $summaryMonth = [
            'hadir' => Attendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'terlambat')->count(),
            'izin' => Attendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'izin')->count(),
            'sakit' => Attendance::where('student_id', $student->id)->whereMonth('date', Carbon::now()->month)->where('status', 'sakit')->count(),
        ];

        return view('dashboard.siswa', compact('student', 'todayAttendance', 'activeToken', 'history', 'summaryMonth'));
    }

    /**
     * Dashboard untuk Orang Tua Siswa.
     */
    public function orangtua(): View
    {
        $user = Auth::user();
        $parent = $user->parentProfile;

        if (! $parent) {
            $parent = Parents::firstOrCreate(
                ['user_id' => $user->id],
                ['phone' => '081234567890', 'relation' => 'wali']
            );
            $firstStudent = Student::first();
            if ($firstStudent) {
                $parent->students()->syncWithoutDetaching([$firstStudent->id]);
            }
        }

        $today = Carbon::today()->toDateString();
        $children = $parent->students()->with(['user', 'schoolClass.homeroomTeacher.user'])->get();

        // Presensi hari ini untuk anak-anak
        $childrenAttendances = Attendance::whereIn('student_id', $children->pluck('id'))
            ->where('date', $today)
            ->get()
            ->keyBy('student_id');

        // Log notifikasi WhatsApp yang dikirimkan ke orang tua
        $notifications = NotificationLog::where('parent_id', $parent->id)
            ->latest('sent_at')
            ->take(10)
            ->get();

        return view('dashboard.orangtua', compact('parent', 'children', 'childrenAttendances', 'notifications'));
    }
}
