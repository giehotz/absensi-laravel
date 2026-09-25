<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Holiday;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ManualAttendanceController extends Controller
{
    /**
     * Tampilan Input Absen Manual untuk Tenaga Pengajar (Guru).
     */
    public function guru(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $date = $request->input('date', Carbon::today()->toDateString());

        // Ambil kelas yang diampu guru (wali kelas + jadwal mengajar)
        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
        $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $allowedClassIds = $homeroomClassIds->merge($teachingClassIds)->unique();

        $classes = SchoolClass::whereIn('id', $allowedClassIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('school_class_id', $classes->first()?->id);
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $students = collect();
        $attendanceMeta = null;
        if ($selectedClassId) {
            [$students, $attendanceMeta] = $this->getStudentsAndAttendanceMeta($selectedClassId, $date);
        }

        $holiday = Holiday::getHolidayFor($date);

        return view('guru.attendance-manual', compact('classes', 'selectedClass', 'selectedClassId', 'date', 'students', 'attendanceMeta', 'holiday'));
    }

    /**
     * Tampilan Input Absen Manual untuk Administrator Sekolah.
     */
    public function admin(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $selectedClassId = $request->input('school_class_id', $classes->first()?->id);
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $students = collect();
        $attendanceMeta = null;
        if ($selectedClassId) {
            [$students, $attendanceMeta] = $this->getStudentsAndAttendanceMeta($selectedClassId, $date);
        }

        $holiday = Holiday::getHolidayFor($date);

        return view('admin.attendance-manual', compact('classes', 'selectedClass', 'selectedClassId', 'date', 'students', 'attendanceMeta', 'holiday'));
    }

    /**
     * Dapatkan daftar siswa dan metadata status pengisian presensi kelas.
     *
     * @return array{Collection<int, Student>, array<string, mixed>|null}
     */
    protected function getStudentsAndAttendanceMeta(int $classId, string $date): array
    {
        $students = Student::with([
            'user',
            'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date)->with('recordedByUser');
            },
        ])
            ->where('school_class_id', $classId)
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '')
            ->values();

        $attendances = $students->flatMap->attendances;
        $totalStudents = $students->count();
        $recordedCount = $attendances->count();

        $attendanceMeta = null;
        if ($totalStudents > 0) {
            $latestAttendance = $attendances->sortByDesc('updated_at')->first();
            $isUpdated = $latestAttendance && $latestAttendance->updated_at && $latestAttendance->created_at
                && $latestAttendance->updated_at->diffInSeconds($latestAttendance->created_at) > 1;

            $statusCounts = [
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'terlambat' => $attendances->where('status', 'terlambat')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'alpa' => $attendances->where('status', 'alpa')->count(),
            ];

            $attendanceMeta = [
                'total_students' => $totalStudents,
                'recorded_count' => $recordedCount,
                'is_complete' => $recordedCount >= $totalStudents && $totalStudents > 0,
                'is_partial' => $recordedCount > 0 && $recordedCount < $totalStudents,
                'is_empty' => $recordedCount === 0,
                'latest_attendance' => $latestAttendance,
                'recorder_name' => $latestAttendance?->recordedByUser?->name ?? 'Sistem / Guru',
                'recorded_at' => $latestAttendance?->updated_at ?? $latestAttendance?->created_at,
                'is_updated' => $isUpdated,
                'status_counts' => $statusCounts,
            ];
        }

        return [$students, $attendanceMeta];
    }

    /**
     * Simpan data presensi manual secara massal (Bulk Store).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'school_class_id' => 'required|exists:school_classes,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:hadir,terlambat,sakit,izin,alpa',
            'attendances.*.notes' => 'nullable|string|max:255',
        ]);

        $date = $validated['date'];
        $classId = (int) $validated['school_class_id'];
        $user = Auth::user();

        // Otorisasi jika role adalah Guru
        if ($user->role === 'guru') {
            $teacher = $user->teacher;
            if (! $teacher) {
                abort(403, 'Profil guru tidak ditemukan.');
            }

            $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
            $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');
            $allowedClassIds = $homeroomClassIds->merge($teachingClassIds)->unique();

            if (! $allowedClassIds->contains($classId)) {
                abort(403, 'Anda tidak memiliki hak akses mencatat presensi untuk kelas ini.');
            }
        }

        $now = Carbon::now();
        $savedCount = 0;

        foreach ($validated['attendances'] as $item) {
            $studentId = $item['student_id'];
            $status = $item['status'];
            $notes = $item['notes'] ?? null;

            $existing = Attendance::where('student_id', $studentId)
                ->whereDate('date', $date)
                ->first();

            $checkInTime = in_array($status, ['hadir', 'terlambat'])
                ? ($existing?->check_in_time ?? $now)
                : null;

            if ($existing) {
                $existing->update([
                    'status' => $status,
                    'method' => 'manual',
                    'recorded_by' => $user->id,
                    'check_in_time' => $checkInTime,
                    'notes' => $notes,
                ]);
            } else {
                Attendance::create([
                    'student_id' => $studentId,
                    'date' => $date,
                    'status' => $status,
                    'method' => 'manual',
                    'recorded_by' => $user->id,
                    'check_in_time' => $checkInTime,
                    'notes' => $notes,
                ]);
            }

            $savedCount++;
        }

        $className = SchoolClass::find($classId)?->name ?? 'Kelas';
        $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

        return redirect()->route(
            $user->role === 'admin' ? 'admin.attendances.manual' : 'guru.attendance.manual',
            ['date' => $date, 'school_class_id' => $classId]
        )->with('success', "Presensi {$className} tanggal {$formattedDate} berhasil disimpan ({$savedCount} siswa).");
    }

    /**
     * Isi presensi 1 bulan penuh untuk hari aktif (Senin - Sabtu) khusus Administrator.
     */
    public function monthlyFill(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            abort(403, 'Hanya Administrator yang memiliki akses untuk mengisi absensi 1 bulan penuh.');
        }

        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2099',
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpa',
            'scope' => 'required|in:all,selected',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'overwrite' => 'nullable|boolean',
        ], [
            'school_class_id.required' => 'Pilih kelas tujuan presensi.',
            'month.between' => 'Bulan tidak valid.',
            'year.between' => 'Tahun tidak valid.',
            'status.required' => 'Pilih status kehadiran.',
        ]);

        $classId = (int) $validated['school_class_id'];
        $schoolClass = SchoolClass::findOrFail($classId);
        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $status = $validated['status'];
        $scope = $validated['scope'];
        $overwrite = (bool) ($validated['overwrite'] ?? false);

        // Ambil daftar siswa target
        $studentQuery = Student::where('school_class_id', $classId);
        if ($scope === 'selected' && ! empty($validated['student_ids'])) {
            $studentQuery->whereIn('id', $validated['student_ids']);
        }
        $students = $studentQuery->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang dipilih atau terdaftar di kelas ini.');
        }

        // Ambil setting jam mulai sekolah
        $attendanceSetting = AttendanceSetting::first();
        $startTimeStr = $attendanceSetting?->school_start_time ?? '07:00:00';

        // Hitung seluruh tanggal dalam bulan tersebut (kecualikan hari Minggu & hari libur)
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $holidayDates = Holiday::active()
            ->whereBetween('holiday_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $activeDates = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day);
            // Lewati hari Minggu (dayOfWeek === 0) dan hari libur aktif
            if ($currentDate->dayOfWeek === Carbon::SUNDAY || in_array($currentDate->toDateString(), $holidayDates, true)) {
                continue;
            }
            $activeDates[] = $currentDate->toDateString();
        }

        if (empty($activeDates)) {
            return back()->with('error', 'Tidak ada hari efektif (Senin-Sabtu) pada bulan yang dipilih.');
        }

        $processedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $students,
            $activeDates,
            $status,
            $overwrite,
            $startTimeStr,
            $user,
            &$processedCount,
            &$skippedCount
        ) {
            foreach ($activeDates as $dateStr) {
                $checkInTime = in_array($status, ['hadir', 'terlambat'])
                    ? Carbon::parse($dateStr.' '.$startTimeStr)
                    : null;

                foreach ($students as $student) {
                    $existing = Attendance::where('student_id', $student->id)
                        ->whereDate('date', $dateStr)
                        ->first();

                    if ($existing) {
                        if ($overwrite) {
                            $existing->update([
                                'status' => $status,
                                'method' => 'manual',
                                'recorded_by' => $user->id,
                                'check_in_time' => $checkInTime,
                            ]);
                            $processedCount++;
                        } else {
                            $skippedCount++;
                        }
                    } else {
                        Attendance::create([
                            'student_id' => $student->id,
                            'date' => $dateStr,
                            'status' => $status,
                            'method' => 'manual',
                            'recorded_by' => $user->id,
                            'check_in_time' => $checkInTime,
                            'notes' => 'Diisi massal bulanan oleh Admin',
                        ]);
                        $processedCount++;
                    }
                }
            }
        });

        $monthName = Carbon::createFromDate($year, $month, 1)->locale('id')->translatedFormat('F Y');
        $studentCount = $students->count();
        $activeDaysCount = count($activeDates);

        $msg = "Presensi bulan {$monthName} untuk {$studentCount} siswa ({$activeDaysCount} hari aktif Senin-Sabtu) berhasil diproses. Sebanyak {$processedCount} rekaman disimpan.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} rekaman dilewati karena sudah ada data sebelumnya).";
        }

        $targetDate = $activeDates[0] ?? Carbon::createFromDate($year, $month, 1)->toDateString();

        return redirect()->route('admin.attendances.manual', [
            'school_class_id' => $classId,
            'date' => $targetDate,
        ])->with('success', $msg);
    }
}
