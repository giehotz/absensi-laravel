<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
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
        $teacher = $user->teacher ?? Teacher::firstOrCreate(['user_id' => $user->id], ['nip' => 'GURU-DEMO', 'phone' => '081234567800']);
        $today = Carbon::today()->toDateString();
        $currentDayOfWeek = (int) Carbon::now()->dayOfWeekIso;

        // Jadwal mengajar guru hari ini
        $todaySchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->where('day_of_week', $currentDayOfWeek)
            ->orderBy('start_time')
            ->get();

        // Kelas yang diampu sebagai Wali Kelas
        $homeroomClasses = SchoolClass::with(['students.user', 'students.attendances' => function ($q) use ($today) {
            $q->where('date', $today);
        }])->where('homeroom_teacher_id', $teacher->id)->get();

        return view('dashboard.guru', compact('teacher', 'todaySchedules', 'homeroomClasses'));
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
