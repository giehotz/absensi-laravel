<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SlotTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Tampilkan jadwal mengajar guru dan matriks jadwal per kelas (ala Simpatika).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $academicYear = AcademicYear::where('is_active', true)->first();

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

        $myStats = [
            'total_sessions' => $mySchedules->count(),
            'total_classes' => $mySchedules->pluck('school_class_id')->unique()->count(),
            'total_subjects' => $mySchedules->pluck('subject_id')->unique()->count(),
        ];

        // 2. Data Jadwal Per Rombel / Kelas (Tampilan Matriks Simpatika)
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher.user'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $selectedClassId = (int) $request->input('school_class_id', $classes->first()?->id ?? 0);
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

        $now = Carbon::now('Asia/Jakarta');
        $currentDayOfWeek = (int) $now->dayOfWeekIso;
        $currentTimeStr = $now->format('H:i:s');

        $activeTab = $request->query('tab', 'my'); // 'my' atau 'class'

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
            'activeTab'
        ));
    }
}
