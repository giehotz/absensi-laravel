<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AcademicCalendar\EffectiveWeekService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EffectiveWeekController extends Controller
{
    public function __construct(
        protected EffectiveWeekService $service
    ) {}

    /**
     * Halaman Analisis Minggu Efektif (ganjil + genap) beserta filter parameter di Portal Admin.
     */
    public function index(Request $request): View
    {
        $report = $this->buildReport($request);

        $teachers = Teacher::with('user')->get()->sortBy(fn (Teacher $teacher) => $teacher->user?->name ?? '')->values();
        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();

        return view('admin.effective-weeks.index', array_merge($report, compact('teachers', 'subjects', 'classes')));
    }

    /**
     * Versi cetak dokumen resmi (A4) dengan Kop Surat sekolah.
     */
    public function print(Request $request): View
    {
        $report = $this->buildReport($request);
        $setting = AttendanceSetting::first();
        $printSemester = $request->query('print_semester', 'all');

        // Filter semester jika hanya ingin mencetak salah satu
        if ($printSemester !== 'all' && isset($report['semesters'][$printSemester])) {
            $report['semesters'] = [$printSemester => $report['semesters'][$printSemester]];
        }

        return view('admin.effective-weeks.print', array_merge($report, compact('setting', 'printSemester')));
    }

    /**
     * Helper untuk menyusun struktur data laporan analisis minggu efektif.
     *
     * @return array<string, mixed>
     */
    protected function buildReport(Request $request): array
    {
        $validated = $request->validate([
            'academic_year_name' => ['nullable', 'string', 'max:50'],
            'school_days' => ['nullable', 'integer', 'in:5,6'],
            'min_effective_days' => ['nullable', 'integer', 'min:1', 'max:6'],
            'jp_per_week' => ['nullable', 'integer', 'min:1', 'max:60'],
            'exam_hours' => ['nullable', 'integer', 'min:0', 'max:200'],
            'reserve_hours' => ['nullable', 'integer', 'min:0', 'max:200'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'school_class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
        ]);

        $recordedYears = AcademicYear::distinctYearNames();
        $yearName = $validated['academic_year_name']
            ?? AcademicYear::activeYearName()
            ?? $this->defaultYearName();

        $availableYears = array_values(array_unique(array_merge([$yearName], $recordedYears)));

        $subjectId = isset($validated['subject_id']) ? (int) $validated['subject_id'] : null;
        $teacherId = isset($validated['teacher_id']) ? (int) $validated['teacher_id'] : null;
        $classId = isset($validated['school_class_id']) ? (int) $validated['school_class_id'] : null;

        // Deteksi otomatis JP dari jadwal KBM jika subject_id terpilih dan jp_per_week tidak diinput khusus
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
            'teacher' => $teacherId ? Teacher::with('user')->find($teacherId) : null,
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
