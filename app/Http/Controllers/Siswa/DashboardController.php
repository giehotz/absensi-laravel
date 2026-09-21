<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\QrToken;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SlotTemplate;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\QrCodeService;
use App\Services\SavingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Siswa yang dioptimalkan untuk Mobile-First.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student;

        // Fallback untuk akun demo jika profil siswa belum terhubung
        if (! $student) {
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

        $student->load(['schoolClass.homeroomTeacher.user', 'schoolClass.academicYear']);

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 1. Presensi Hari Ini
        $todayAttendance = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        // 2. Token QR Aktif Hari Ini & SVG Data URI
        $activeToken = QrToken::where('student_id', $student->id)
            ->where('is_used', false)
            ->where('expires_at', '>', $now)
            ->latest()
            ->first();

        if (! $activeToken) {
            $activeToken = QrToken::create([
                'student_id' => $student->id,
                'token' => 'TOKEN-'.$student->nis.'-'.$now->format('His'),
                'expires_at' => $now->copy()->endOfDay(),
                'is_used' => false,
            ]);
        }

        $qrCodeService = app(QrCodeService::class);
        $qrCodeDataUri = $qrCodeService->generateDataUri($activeToken->token ?? $student->qr_code_identifier, 260, 4);

        // 3. Rekap Statistik Kehadiran Bulan Berjalan
        $summaryMonth = [
            'hadir' => Attendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'terlambat')->count(),
            'izin' => Attendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'izin')->count(),
            'sakit' => Attendance::where('student_id', $student->id)->whereMonth('date', $now->month)->whereYear('date', $now->year)->where('status', 'sakit')->count(),
        ];

        // 4. Jadwal Pelajaran Kelas Siswa
        $schedules = Schedule::with(['subject', 'teacher.user'])
            ->where('school_class_id', $student->school_class_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $schedulesByDay = [];
        foreach ($daysMap as $dayNum => $dayName) {
            $schedulesByDay[$dayNum] = [];
        }
        foreach ($schedules as $sched) {
            $schedulesByDay[$sched->day_of_week][] = $sched;
        }

        $academicYear = AcademicYear::where('is_active', true)->first();
        $slotTemplates = SlotTemplate::where('academic_year_id', $academicYear?->id)
            ->orderBy('day_of_week')
            ->orderBy('jam_ke')
            ->get();

        $defaultPresets = SlotTemplate::getDefaultMadrasahSlots();
        $allSlotsByDay = [];
        foreach ($daysMap as $dayNum => $dayName) {
            $daySlots = $slotTemplates->where('day_of_week', $dayNum)->values();
            if ($daySlots->isEmpty() && isset($defaultPresets[$dayNum])) {
                $daySlots = collect($defaultPresets[$dayNum])->map(function ($s) use ($dayNum) {
                    return new SlotTemplate([
                        'day_of_week' => $dayNum,
                        'jam_ke' => $s['jam_ke'],
                        'k_jadwal' => $s['k_jadwal'],
                        'name' => $s['name'],
                        'start_time' => $s['start'].':00',
                        'end_time' => $s['end'].':00',
                    ]);
                });
            }
            $allSlotsByDay[$dayNum] = $daySlots;
        }

        $currentDayOfWeek = (int) $now->dayOfWeekIso; // 1 = Senin ... 7 = Minggu
        $currentTimeStr = $now->format('H:i:s');

        // 5. Riwayat Pengajuan Izin / Sakit
        $leaveRequests = LeaveRequest::where('student_id', $student->id)
            ->with('reviewer')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        // 6. Riwayat Presensi Terakhir (30 hari terakhir)
        $history = Attendance::where('student_id', $student->id)
            ->orderByDesc('date')
            ->take(30)
            ->get();

        // 7. Catatan Pembinaan Siswa (dari Guru / Wali Kelas / BK)
        $studentNotes = StudentNote::where('student_id', $student->id)
            ->with('teacher.user')
            ->orderByDesc('date')
            ->take(10)
            ->get();

        // 8. Tabungan Siswa (Rekening & Mutasi Transaksi)
        $savingsService = app(SavingsService::class);
        $savingsAccount = $savingsService->getOrCreateAccount($student);
        $savingsTransactions = $savingsAccount->transactions()->with(['handler', 'corrector'])->take(20)->get();

        return view('siswa.dashboard', compact(
            'student',
            'todayAttendance',
            'activeToken',
            'qrCodeDataUri',
            'summaryMonth',
            'schedules',
            'schedulesByDay',
            'daysMap',
            'currentDayOfWeek',
            'currentTimeStr',
            'leaveRequests',
            'history',
            'studentNotes',
            'savingsAccount',
            'savingsTransactions',
            'allSlotsByDay'
        ));
    }
}
