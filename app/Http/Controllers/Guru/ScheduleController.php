<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\Schedule;
use App\Models\SlotTemplate;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Tampilkan jadwal mengajar guru dan matriks jadwal per kelas (ala Simpatika),
     * serta tab khusus Plot Jadwal Kelas bagi Wali Kelas jika bertugas.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $academicYear = AcademicYear::where('is_active', true)->first();
        $setting = AttendanceSetting::firstOrCreate([]);
        $canHomeroomEdit = $setting->isHomeroomScheduleEditOpen();
        $homeroomDeadline = $setting->homeroom_schedule_deadline;
        $isDeadlineExpired = $setting->isHomeroomScheduleDeadlineExpired();

        $daysMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // 1. Data Jadwal Mengajar Guru yang sedang login
        $mySchedules = collect();
        $mySchedulesByDay = [];
        foreach ($daysMap as $dNum => $dName) {
            $mySchedulesByDay[$dNum] = [
                'name' => $dName,
                'items' => collect(),
            ];
        }

        if ($teacher) {
            $mySchedules = Schedule::with(['subject', 'schoolClass'])
                ->where('teacher_id', $teacher->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            foreach ($daysMap as $dNum => $dName) {
                $mySchedulesByDay[$dNum]['items'] = $mySchedules->where('day_of_week', $dNum)->values();
            }
        }

        $totalMinutes = $mySchedules->sum(function ($sch) {
            return Carbon::parse($sch->start_time)->diffInMinutes(Carbon::parse($sch->end_time));
        });
        $totalJtm = max(0, (int) round($totalMinutes / 40));

        $myStats = [
            'total_sessions' => $mySchedules->count(),
            'total_jtm' => $totalJtm,
            'total_classes' => $mySchedules->pluck('school_class_id')->unique()->count(),
            'total_subjects' => $mySchedules->pluck('subject_id')->unique()->count(),
        ];

        // 2. Data Jadwal Per Rombel / Kelas (Tampilan Matriks Simpatika)
        $classes = $teacher ? $teacher->getAccessibleClasses($academicYear?->id) : collect();

        $requestedClassId = (int) $request->input('school_class_id', 0);
        if ($requestedClassId > 0 && $classes->contains('id', $requestedClassId)) {
            $selectedClassId = $requestedClassId;
        } else {
            $selectedClassId = (int) ($classes->first()?->id ?? 0);
        }
        $selectedClass = $classes->firstWhere('id', $selectedClassId);

        $classSchedules = collect();
        if ($selectedClassId > 0) {
            $classSchedules = Schedule::with(['subject', 'teacher.user', 'schoolClass'])
                ->where('school_class_id', $selectedClassId)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        $classSchedulesByDay = [];
        foreach ($daysMap as $dayNum => $dayName) {
            $classSchedulesByDay[$dayNum] = [
                'name' => $dayName,
                'items' => $classSchedules->where('day_of_week', $dayNum)->values(),
            ];
        }

        // Slot Templates KBM & Non-KBM untuk jadwal per kelas
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

        // 3. Khusus Wali Kelas: Matriks Jadwal Rombel Binaan Sendiri
        $isHomeroom = $teacher && $teacher->isHomeroom();
        $homeroomClass = null;
        $homeroomSchedules = collect();
        $homeroomSchedulesByDay = [];
        $teachers = collect();
        $subjects = collect();
        $teacherAssignmentsMap = collect();
        $nonKbmSlotsByDay = [];
        $kbmSlotsByDay = [];
        $maxJam = 10;
        $presets = [];

        if ($isHomeroom) {
            $homeroomClass = $teacher->homeroomClasses()
                ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
                ->first() ?: $teacher->homeroomClasses()->first();

            if ($homeroomClass) {
                $homeroomSchedules = Schedule::with(['subject', 'teacher.user', 'schoolClass'])
                    ->where('school_class_id', $homeroomClass->id)
                    ->orderBy('day_of_week')
                    ->orderBy('start_time')
                    ->get();

                // Hitung Beban Regulasi JP untuk kelas binaan
                $totalMinutes = $homeroomSchedules->sum(function ($sch) {
                    return Carbon::parse($sch->start_time)->diffInMinutes(Carbon::parse($sch->end_time));
                });
                $targetJp = 38;
                $homeroomClass->total_jp = max(0, (int) round($totalMinutes / 40));
                $homeroomClass->total_sessions = $homeroomSchedules->count();
                $homeroomClass->target_jp = $targetJp;
                $homeroomClass->percent_jp = min(100, (int) round(($homeroomClass->total_jp / max(1, $targetJp)) * 100));
                $homeroomClass->shortage_jp = max(0, $targetJp - $homeroomClass->total_jp);

                foreach ($daysMap as $dayNum => $dayName) {
                    $homeroomSchedulesByDay[$dayNum] = [
                        'name' => $dayName,
                        'items' => $homeroomSchedules->where('day_of_week', $dayNum)->values(),
                    ];
                }

                // Data Guru & Mapel untuk Modal Input/Edit Jadwal
                $teachers = Teacher::with(['user', 'assignments'])->get()->sortBy(fn ($t) => $t->user->name ?? '');
                $subjects = Subject::orderBy('name')->get();

                $teacherAssignmentsMap = $teachers->mapWithKeys(function ($t) {
                    return [
                        $t->id => [
                            'has_restrictions' => $t->assignments->isNotEmpty(),
                            'allowed_subjects' => $t->assignments->pluck('subject_id')->unique()->values()->all(),
                            'allowed_classes' => $t->assignments->pluck('school_class_id')->unique()->values()->all(),
                        ],
                    ];
                });

                foreach ($daysMap as $dayNum => $dayName) {
                    $daySlots = $allSlotsByDay[$dayNum] ?? collect();
                    $nonKbmSlotsByDay[$dayNum] = $daySlots->where('k_jadwal', '!=', SlotTemplate::K_KBM)->values();
                    $kbmSlotsByDay[$dayNum] = $daySlots->where('k_jadwal', SlotTemplate::K_KBM)
                        ->values()
                        ->map(fn ($s) => [
                            'label' => ($s->name ?: 'Jam ke-'.$s->jam_ke).' ('.$s->getShortStartTime().' - '.$s->getShortEndTime().')',
                            'start' => $s->getShortStartTime(),
                            'end' => $s->getShortEndTime(),
                        ])->all();
                }

                foreach ($allSlotsByDay as $daySlots) {
                    if ($daySlots->isNotEmpty()) {
                        $dayMax = (int) $daySlots->max('jam_ke');
                        if ($dayMax > $maxJam) {
                            $maxJam = $dayMax;
                        }
                    }
                }
                $maxJam = min(12, max(10, $maxJam));

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
                ];
            }
        }

        $now = Carbon::now('Asia/Jakarta');
        $currentDayOfWeek = (int) $now->dayOfWeekIso;
        $currentTimeStr = $now->format('H:i:s');

        // Tab default
        $activeTab = $request->query('tab', ($isHomeroom && $request->has('tab')) ? $request->query('tab') : 'my');

        return view('guru.jadwal', compact(
            'teacher',
            'academicYear',
            'daysMap',
            'mySchedules',
            'mySchedulesByDay',
            'myStats',
            'classes',
            'selectedClass',
            'selectedClassId',
            'classSchedules',
            'classSchedulesByDay',
            'allSlotsByDay',
            'currentDayOfWeek',
            'currentTimeStr',
            'activeTab',
            'isHomeroom',
            'homeroomClass',
            'canHomeroomEdit',
            'homeroomSchedules',
            'homeroomSchedulesByDay',
            'teachers',
            'subjects',
            'teacherAssignmentsMap',
            'nonKbmSlotsByDay',
            'kbmSlotsByDay',
            'maxJam',
            'presets',
            'homeroomDeadline',
            'isDeadlineExpired'
        ));
    }

    /**
     * Simpan jadwal pelajaran baru oleh Wali Kelas untuk rombel binaannya.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = Auth::user()?->teacher;
        if (! $teacher || ! $teacher->isHomeroom()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Wali Kelas.');
        }

        $setting = AttendanceSetting::firstOrCreate([]);
        if (! $setting->isHomeroomScheduleEditOpen()) {
            $expiredMsg = $setting->isHomeroomScheduleDeadlineExpired()
                ? 'Batas waktu pengisian jadwal pelajaran telah berakhir pada '.Carbon::parse($setting->homeroom_schedule_deadline)->translatedFormat('d F Y').'.'
                : 'Pengisian jadwal oleh Wali Kelas sedang ditutup oleh Administrator.';

            return redirect()
                ->route('guru.jadwal', ['tab' => 'homeroom'])
                ->with('error', $expiredMsg);
        }

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        if (! $teacher->isHomeroomFor((int) $validated['school_class_id'])) {
            abort(403, 'Anda hanya berhak mengelola jadwal untuk kelas binaan Anda.');
        }

        $targetTeacher = Teacher::with(['assignments.subject', 'assignments.schoolClass', 'user'])->find($validated['teacher_id']);
        if ($targetTeacher && $targetTeacher->hasTeachingRestrictions()) {
            if (! $targetTeacher->canTeach((int) $validated['subject_id'], (int) $validated['school_class_id'])) {
                $allowedSubjects = $targetTeacher->assignments->pluck('subject.name')->unique()->filter()->join(', ');
                $allowedClasses = $targetTeacher->assignments->pluck('schoolClass.name')->unique()->filter()->join(', ');
                $teacherName = $targetTeacher->user?->name ?? 'Guru ini';

                $assignmentError = "Guru {$teacherName} memiliki penugasan khusus. Hanya diizinkan mengajar [{$allowedSubjects}] pada rombel [{$allowedClasses}].";

                return redirect()
                    ->route('guru.jadwal', ['tab' => 'homeroom'])
                    ->withInput()
                    ->with('conflict_error', $assignmentError)
                    ->withErrors(['teacher_id' => $assignmentError]);
            }
        }

        $conflict = $this->checkScheduleConflict(
            schoolClassId: (int) $validated['school_class_id'],
            teacherId: (int) $validated['teacher_id'],
            dayOfWeek: (int) $validated['day_of_week'],
            startTime: $validated['start_time'].':00',
            endTime: $validated['end_time'].':00'
        );

        if ($conflict) {
            return redirect()
                ->route('guru.jadwal', ['tab' => 'homeroom'])
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
            ->route('guru.jadwal', ['tab' => 'homeroom'])
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jadwal pelajaran oleh Wali Kelas untuk rombel binaannya.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $teacher = Auth::user()?->teacher;
        if (! $teacher || ! $teacher->isHomeroom()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Wali Kelas.');
        }

        $setting = AttendanceSetting::firstOrCreate([]);
        if (! $setting->isHomeroomScheduleEditOpen()) {
            $expiredMsg = $setting->isHomeroomScheduleDeadlineExpired()
                ? 'Batas waktu pengisian jadwal pelajaran telah berakhir pada '.Carbon::parse($setting->homeroom_schedule_deadline)->translatedFormat('d F Y').'.'
                : 'Pengisian jadwal oleh Wali Kelas sedang ditutup oleh Administrator.';

            return redirect()
                ->route('guru.jadwal', ['tab' => 'homeroom'])
                ->with('error', $expiredMsg);
        }

        if (! $teacher->isHomeroomFor($schedule->school_class_id)) {
            abort(403, 'Anda hanya berhak mengelola jadwal untuk kelas binaan Anda.');
        }

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // Pastikan tidak mengubah kelas ke kelas lain
        if ((int) $validated['school_class_id'] !== (int) $schedule->school_class_id) {
            abort(403, 'Anda tidak dapat memindahkan jadwal ke kelas lain.');
        }

        $targetTeacher = Teacher::with(['assignments.subject', 'assignments.schoolClass', 'user'])->find($validated['teacher_id']);
        if ($targetTeacher && $targetTeacher->hasTeachingRestrictions()) {
            if (! $targetTeacher->canTeach((int) $validated['subject_id'], (int) $validated['school_class_id'])) {
                $allowedSubjects = $targetTeacher->assignments->pluck('subject.name')->unique()->filter()->join(', ');
                $allowedClasses = $targetTeacher->assignments->pluck('schoolClass.name')->unique()->filter()->join(', ');
                $teacherName = $targetTeacher->user?->name ?? 'Guru ini';

                $assignmentError = "Guru {$teacherName} memiliki penugasan khusus. Hanya diizinkan mengajar [{$allowedSubjects}] pada rombel [{$allowedClasses}].";

                return redirect()
                    ->route('guru.jadwal', ['tab' => 'homeroom'])
                    ->withInput()
                    ->with('conflict_error', $assignmentError)
                    ->withErrors(['teacher_id' => $assignmentError]);
            }
        }

        $conflict = $this->checkScheduleConflict(
            schoolClassId: (int) $validated['school_class_id'],
            teacherId: (int) $validated['teacher_id'],
            dayOfWeek: (int) $validated['day_of_week'],
            startTime: $validated['start_time'].':00',
            endTime: $validated['end_time'].':00',
            excludeScheduleId: $schedule->id
        );

        if ($conflict) {
            return redirect()
                ->route('guru.jadwal', ['tab' => 'homeroom'])
                ->withInput()
                ->with('conflict_error', $conflict)
                ->withErrors(['conflict' => $conflict]);
        }

        $schedule->update([
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return redirect()
            ->route('guru.jadwal', ['tab' => 'homeroom'])
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jadwal pelajaran oleh Wali Kelas untuk rombel binaannya.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $teacher = Auth::user()?->teacher;
        if (! $teacher || ! $teacher->isHomeroom()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Wali Kelas.');
        }

        $setting = AttendanceSetting::firstOrCreate([]);
        if (! $setting->isHomeroomScheduleEditOpen()) {
            $expiredMsg = $setting->isHomeroomScheduleDeadlineExpired()
                ? 'Batas waktu pengisian jadwal pelajaran telah berakhir pada '.Carbon::parse($setting->homeroom_schedule_deadline)->translatedFormat('d F Y').'.'
                : 'Pengisian jadwal oleh Wali Kelas sedang ditutup oleh Administrator.';

            return redirect()
                ->route('guru.jadwal', ['tab' => 'homeroom'])
                ->with('error', $expiredMsg);
        }

        if (! $teacher->isHomeroomFor($schedule->school_class_id)) {
            abort(403, 'Anda hanya berhak mengelola jadwal untuk kelas binaan Anda.');
        }

        $schedule->delete();

        return redirect()
            ->route('guru.jadwal', ['tab' => 'homeroom'])
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
