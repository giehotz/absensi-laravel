<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentPackage;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\Assessment\AssessmentExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentController extends Controller
{
    /**
     * Menampilkan seluruh paket penilaian dari semua rombel dan guru.
     */
    public function index(Request $request): View
    {
        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $teacherId = $request->input('teacher_id');
        $status = $request->input('status');
        $academicYearId = $request->input('academic_year_id');

        $query = AssessmentPackage::with(['schoolClass', 'subject', 'teacher.user', 'academicYear'])
            ->latest();

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($status && in_array($status, ['draft', 'locked'])) {
            $query->where('status', $status);
        }

        $packages = $query->paginate(15)->withQueryString();

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with('user')->get()->sortBy('user.name')->values();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        // KPI
        $totalPackages = AssessmentPackage::count();
        $draftPackages = AssessmentPackage::where('status', 'draft')->count();
        $lockedPackages = AssessmentPackage::where('status', 'locked')->count();

        return view('admin.assessments.index', compact(
            'packages',
            'classes',
            'subjects',
            'teachers',
            'academicYears',
            'totalPackages',
            'draftPackages',
            'lockedPackages'
        ));
    }

    /**
     * Tampilkan detail matriks rekap nilai satu paket di panel admin.
     */
    public function show(AssessmentPackage $package): View
    {
        $package->load([
            'schoolClass.students.user',
            'subject',
            'teacher.user',
            'academicYear',
            'assessments.scores',
            'uploadBatches.uploadedByUser',
        ]);

        $students = $package->schoolClass->students->sortBy(function ($s) {
            return $s->user?->name ?? '';
        })->values();

        $assessmentsByNum = $package->assessments->keyBy('sheet_number');
        $activeAssessments = $package->getActiveAssessments();

        return view('admin.assessments.show', compact('package', 'students', 'assessmentsByNum', 'activeAssessments'));
    }

    /**
     * Admin membuka kunci paket nilai kembali ke status draft.
     */
    public function unlock(AssessmentPackage $package): RedirectResponse
    {
        $package->update([
            'status' => 'draft',
            'locked_at' => null,
            'locked_by' => null,
        ]);

        return back()->with('success', 'Kunci paket nilai berhasil dibuka. Guru pengampu kini dapat mengunggah atau memperbarui nilai kembali.');
    }

    /**
     * Ekspor rekap nilai dari panel admin.
     */
    public function exportExcel(AssessmentPackage $package, AssessmentExcelService $service): StreamedResponse
    {
        return $service->exportRecapExcel($package);
    }

    /**
     * Cetak dokumen rekap nilai ber-Kop Surat.
     */
    public function printPdf(AssessmentPackage $package): View
    {
        $package->load([
            'schoolClass.students.user',
            'schoolClass.homeroomTeacher.user',
            'subject',
            'teacher.user',
            'academicYear',
            'assessments.scores',
        ]);

        $students = $package->schoolClass->students->sortBy(function ($s) {
            return $s->user?->name ?? '';
        })->values();

        $assessmentsByNum = $package->assessments->keyBy('sheet_number');
        $activeAssessments = $package->getActiveAssessments();
        $setting = AttendanceSetting::first();

        $studentStats = [];
        foreach ($students as $student) {
            $scores = [];
            foreach ($activeAssessments as $asm) {
                $s = $asm->scores->firstWhere('student_id', $student->id)?->score;
                if ($s !== null) {
                    $scores[] = (float) $s;
                }
            }

            $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
            $studentStats[$student->id] = [
                'average' => $avg,
                'status' => $avg !== null ? ($avg >= $package->kktp_default ? 'Tuntas' : 'Remedial') : '-',
            ];
        }

        return view('reports.assessment-recap-print', compact(
            'package',
            'students',
            'assessmentsByNum',
            'activeAssessments',
            'studentStats',
            'setting'
        ));
    }

    /**
     * Admin menghapus paket nilai.
     */
    public function destroy(AssessmentPackage $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.penilaian.index')->with('success', 'Paket Penilaian berhasil dihapus.');
    }
}
