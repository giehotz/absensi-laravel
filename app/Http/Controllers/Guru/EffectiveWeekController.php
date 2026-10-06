<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\AcademicCalendar\EffectiveWeekService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EffectiveWeekController extends Controller
{
    public function __construct(
        protected EffectiveWeekService $service
    ) {}

    /**
     * Halaman Analisis Minggu Efektif khusus Guru.
     */
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacher;
        $report = $this->buildReport($request, $teacher?->id);

        $classes = $teacher ? $teacher->getAccessibleClasses() : SchoolClass::orderBy('level')->orderBy('name')->get();

        $subjectIds = collect();
        if ($teacher) {
            $assigned = $teacher->assignedSubjects()->pluck('subjects.id');
            $scheduled = $teacher->schedules()->pluck('subject_id');
            $subjectIds = $assigned->merge($scheduled)->unique()->filter()->values();
        }

        $subjects = $subjectIds->isNotEmpty()
            ? Subject::whereIn('id', $subjectIds)->orderBy('name')->get()
            : Subject::orderBy('name')->get();

        return view('guru.effective-weeks.index', array_merge($report, compact('teacher', 'subjects', 'classes')));
    }

    /**
     * Versi cetak dokumen resmi (A4) dengan Kop Surat sekolah untuk Guru.
     */
    public function print(Request $request): View
    {
        $teacher = Auth::user()->teacher;
        $report = $this->buildReport($request, $teacher?->id);
        $setting = AttendanceSetting::first();
        $printSemester = $request->query('print_semester', 'all');

        if ($printSemester !== 'all' && isset($report['semesters'][$printSemester])) {
            $report['semesters'] = [$printSemester => $report['semesters'][$printSemester]];
        }

        return view('admin.effective-weeks.print', array_merge($report, compact('setting', 'teacher', 'printSemester')));
    }

    /**
     * Helper menyusun laporan analisis minggu efektif dengan scope guru.
     *
     * @return array<string, mixed>
     */
    protected function buildReport(Request $request, ?int $teacherId): array
    {
        $validated = $request->validate([
            'academic_year_name' => ['nullable', 'string', 'max:50'],
            'school_days' => ['nullable', 'integer', 'in:5,6'],
            'min_effective_days' => ['nullable', 'integer', 'min:1', 'max:6'],
            'jp_per_week' => ['nullable', 'integer', 'min:1', 'max:60'],
            'exam_hours' => ['nullable', 'integer', 'min:0', 'max:200'],
            'reserve_hours' => ['nullable', 'integer', 'min:0', 'max:200'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
        ]);

        $recordedYears = AcademicYear::distinctYearNames();
        $yearName = $validated['academic_year_name']
            ?? AcademicYear::activeYearName()
            ?? $this->defaultYearName();

        $availableYears = array_values(array_unique(array_merge([$yearName], $recordedYears)));

        $subjectId = isset($validated['subject_id']) ? (int) $validated['subject_id'] : null;
        $classId = isset($validated['school_class_id']) ? (int) $validated['school_class_id'] : null;

        // Deteksi otomatis JP dari jadwal KBM guru
        $detectedJp = $this->service->detectJpPerWeek($subjectId, $teacherId, $classId);
        $jpPerWeek = isset($validated['jp_per_week'])
            ? (int) $validated['jp_per_week']
            : ($detectedJp ?? 3);

        $params = [
            'school_days' => (int) ($validated['school_days'] ?? 5),
            'min_effective_days' => (int) ($validated['min_effective_days'] ?? 3),
            'jp_per_week' => $jpPerWeek,
            'detected_jp' => $detectedJp,
            'exam_hours' => (int) ($validated['exam_hours'] ?? 0),
            'reserve_hours' => (int) ($validated['reserve_hours'] ?? 0),
        ];

        $semesters = [];

        foreach (['ganjil' => 'Gasal (1)', 'genap' => 'Genap (2)'] as $semester => $label) {
            $analysis = $this->service->analyze($yearName, $semester, $params['school_days'], $params['min_effective_days']);

            $semesters[$semester] = [
                'label' => $label,
                'analysis' => $analysis,
                'hours' => $this->service->calculateHours(
                    $analysis['total_effective_weeks'],
                    $params['jp_per_week'],
                    $params['exam_hours'],
                    $params['reserve_hours']
                ),
            ];
        }

        return [
            'selectedYear' => $yearName,
            'availableYears' => $availableYears,
            'params' => $params,
            'semesters' => $semesters,
            'subject' => $subjectId ? Subject::find($subjectId) : null,
            'selectedClass' => $classId ? SchoolClass::find($classId) : null,
        ];
    }

    private function defaultYearName(): string
    {
        $now = now();
        $startYear = $now->month >= 7 ? $now->year : $now->year - 1;

        return $startYear.'/'.($startYear + 1);
    }
}
