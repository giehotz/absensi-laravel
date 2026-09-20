<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if ($selectedClassId) {
            $students = Student::with(['user', 'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date);
            }])
                ->where('school_class_id', $selectedClassId)
                ->get()
                ->sortBy(fn ($s) => $s->user->name ?? '');
        }

        return view('guru.attendance-manual', compact('classes', 'selectedClass', 'selectedClassId', 'date', 'students'));
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
        if ($selectedClassId) {
            $students = Student::with(['user', 'attendances' => function ($q) use ($date) {
                $q->whereDate('date', $date);
            }])
                ->where('school_class_id', $selectedClassId)
                ->get()
                ->sortBy(fn ($s) => $s->user->name ?? '');
        }

        return view('admin.attendance-manual', compact('classes', 'selectedClass', 'selectedClassId', 'date', 'students'));
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
}
