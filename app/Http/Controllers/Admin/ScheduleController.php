<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Tampilkan antarmuka manajemen jadwal mingguan per kelas ala Simpatika.
     */
    public function index(Request $request): View
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::with('academicYear')
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('school_class_id', $classes->first()?->id);
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $schedules = Schedule::with(['subject', 'teacher.user', 'schoolClass'])
            ->when($selectedClassId, fn ($q) => $q->where('school_class_id', $selectedClassId))
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
        ];

        // Kelompokkan jadwal mingguan berdasarkan hari
        $schedulesByDay = [];
        foreach ($daysMap as $dayNum => $dayName) {
            $schedulesByDay[$dayNum] = [
                'name' => $dayName,
                'items' => $schedules->where('day_of_week', $dayNum)->values(),
            ];
        }

        $teachers = Teacher::with('user')->get()->sortBy(fn ($t) => $t->user->name ?? '');
        $subjects = Subject::orderBy('name')->get();

        // Preset Slot Jam Tatap Muka (Referensi Simpatika)
        $presets = [
            'reguler_40' => [
                'name' => 'Model Reguler (40 Menit/Jam)',
                'slots' => [
                    ['label' => 'Jam ke-1', 'start' => '07:15', 'end' => '07:55'],
                    ['label' => 'Jam ke-2', 'start' => '07:55', 'end' => '08:35'],
                    ['label' => 'Jam ke-3', 'start' => '08:35', 'end' => '09:15'],
                    ['label' => 'Jam ke-4', 'start' => '09:45', 'end' => '10:25'],
                    ['label' => 'Jam ke-5', 'start' => '10:25', 'end' => '11:05'],
                    ['label' => 'Jam ke-6', 'start' => '11:05', 'end' => '11:45'],
                    ['label' => 'Jam ke-7', 'start' => '12:30', 'end' => '13:10'],
                    ['label' => 'Jam ke-8', 'start' => '13:10', 'end' => '13:50'],
                ],
            ],
            'standar_45' => [
                'name' => 'Model Standar (45 Menit/Jam)',
                'slots' => [
                    ['label' => 'Jam ke-1', 'start' => '07:00', 'end' => '07:45'],
                    ['label' => 'Jam ke-2', 'start' => '07:45', 'end' => '08:30'],
                    ['label' => 'Jam ke-3', 'start' => '08:30', 'end' => '09:15'],
                    ['label' => 'Jam ke-4', 'start' => '09:45', 'end' => '10:30'],
                    ['label' => 'Jam ke-5', 'start' => '10:30', 'end' => '11:15'],
                    ['label' => 'Jam ke-6', 'start' => '11:15', 'end' => '12:00'],
                    ['label' => 'Jam ke-7', 'start' => '13:00', 'end' => '13:45'],
                    ['label' => 'Jam ke-8', 'start' => '13:45', 'end' => '14:30'],
                ],
            ],
        ];

        return view('admin.schedules.index', compact(
            'academicYear',
            'classes',
            'selectedClass',
            'selectedClassId',
            'schedules',
            'schedulesByDay',
            'daysMap',
            'teachers',
            'subjects',
            'presets'
        ));
    }

    /**
     * Simpan jadwal pelajaran baru dengan validasi anti-bentrok.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $conflict = $this->checkScheduleConflict(
            schoolClassId: (int) $validated['school_class_id'],
            teacherId: (int) $validated['teacher_id'],
            dayOfWeek: (int) $validated['day_of_week'],
            startTime: $validated['start_time'].':00',
            endTime: $validated['end_time'].':00'
        );

        if ($conflict) {
            return back()
                ->withInput()
                ->with('conflict_error', $conflict)
                ->withErrors(['conflict' => $conflict]);
        }

        Schedule::create([
            'school_class_id' => $validated['school_class_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('admin.schedules.index', ['school_class_id' => $validated['school_class_id']])
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jadwal pelajaran dengan validasi anti-bentrok.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $conflict = $this->checkScheduleConflict(
            schoolClassId: (int) $validated['school_class_id'],
            teacherId: (int) $validated['teacher_id'],
            dayOfWeek: (int) $validated['day_of_week'],
            startTime: $validated['start_time'].':00',
            endTime: $validated['end_time'].':00',
            excludeScheduleId: $schedule->id
        );

        if ($conflict) {
            return back()
                ->withInput()
                ->with('conflict_error', $conflict)
                ->withErrors(['conflict' => $conflict]);
        }

        $schedule->update([
            'school_class_id' => $validated['school_class_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('admin.schedules.index', ['school_class_id' => $validated['school_class_id']])
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jadwal pelajaran.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $classId = $schedule->school_class_id;
        $schedule->delete();

        return redirect()
            ->route('admin.schedules.index', ['school_class_id' => $classId])
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Periksa potensi jadwal bentrok (Conflict Check) untuk Guru maupun Kelas.
     */
    private function checkScheduleConflict(
        int $schoolClassId,
        int $teacherId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeScheduleId = null
    ): ?string {
        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        $dayName = $daysMap[$dayOfWeek] ?? 'Hari '.$dayOfWeek;

        // 1. Validasi Bentrok Guru (Guru tidak boleh mengajar di kelas lain pada hari & jam yang sama)
        $teacherConflict = Schedule::with(['schoolClass', 'subject', 'teacher.user'])
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeScheduleId, fn ($q) => $q->where('id', '!=', $excludeScheduleId))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->first();

        if ($teacherConflict) {
            $teacherName = $teacherConflict->teacher->user->name ?? 'Guru ini';
            $conflictClass = $teacherConflict->schoolClass->name ?? 'Kelas Lain';
            $conflictSubject = $teacherConflict->subject->name ?? 'Mata Pelajaran';
            $startStr = substr($teacherConflict->start_time, 0, 5);
            $endStr = substr($teacherConflict->end_time, 0, 5);

            return "BENTROK JADWAL GURU: {$teacherName} sudah terjadwal mengajar di Kelas {$conflictClass} ({$conflictSubject}) pada hari {$dayName} pukul {$startStr} - {$endStr}. Satu guru tidak dapat dijadwalkan di dua kelas berbeda pada jam yang sama.";
        }

        // 2. Validasi Bentrok Kelas (Kelas tidak boleh memiliki 2 mapel/guru berbeda pada hari & jam yang sama)
        $classConflict = Schedule::with(['schoolClass', 'subject', 'teacher.user'])
            ->where('school_class_id', $schoolClassId)
            ->where('day_of_week', $dayOfWeek)
            ->when($excludeScheduleId, fn ($q) => $q->where('id', '!=', $excludeScheduleId))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->first();

        if ($classConflict) {
            $className = $classConflict->schoolClass->name ?? 'Kelas ini';
            $conflictSubject = $classConflict->subject->name ?? 'Mata Pelajaran';
            $conflictTeacher = $classConflict->teacher->user->name ?? 'Guru';
            $startStr = substr($classConflict->start_time, 0, 5);
            $endStr = substr($classConflict->end_time, 0, 5);

            return "BENTROK JADWAL KELAS: Kelas {$className} sudah terisi pelajaran {$conflictSubject} bersama {$conflictTeacher} pada hari {$dayName} pukul {$startStr} - {$endStr}. Satu kelas tidak dapat menerima dua sesi tatap muka pada jam yang sama.";
        }

        return null;
    }
}
