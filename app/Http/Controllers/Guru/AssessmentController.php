<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentPackage;
use App\Models\AssessmentScore;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\Assessment\AssessmentExcelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentController extends Controller
{
    /**
     * Dapatkan instance Teacher untuk user yang sedang login.
     */
    protected function getTeacher(): Teacher
    {
        $user = Auth::user();

        return $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );
    }

    /**
     * Menampilkan daftar paket penilaian guru pengampu dan kelas binaan.
     */
    public function index(Request $request): View
    {
        $teacher = $this->getTeacher();
        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $status = $request->input('status');

        $query = AssessmentPackage::with(['schoolClass', 'subject', 'teacher.user', 'academicYear'])
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhereHas('schoolClass', function ($sq) use ($teacher) {
                        $sq->where('homeroom_teacher_id', $teacher->id);
                    });
            })
            ->latest();

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($status && in_array($status, ['draft', 'locked'])) {
            $query->where('status', $status);
        }

        $packages = $query->paginate(12)->withQueryString();

        // Opsi Filter
        $assignedClassIds = $teacher->assignedClasses()->pluck('school_classes.id');
        $homeroomClassIds = $teacher->homeroomClasses()->pluck('id');
        $packageClassIds = AssessmentPackage::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $allClassIds = $assignedClassIds->merge($homeroomClassIds)->merge($packageClassIds)->unique();

        $classes = SchoolClass::whereIn('id', $allClassIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $assignedSubjectIds = $teacher->assignedSubjects()->pluck('subjects.id');
        $packageSubjectIds = AssessmentPackage::where('teacher_id', $teacher->id)->pluck('subject_id');
        $allSubjectIds = $assignedSubjectIds->merge($packageSubjectIds)->unique();

        $subjects = Subject::whereIn('id', $allSubjectIds)
            ->orderBy('name')
            ->get();

        // KPI Metrics
        $totalPackages = AssessmentPackage::where('teacher_id', $teacher->id)->count();
        $draftPackages = AssessmentPackage::where('teacher_id', $teacher->id)->where('status', 'draft')->count();
        $lockedPackages = AssessmentPackage::where('teacher_id', $teacher->id)->where('status', 'locked')->count();
        $homeroomCount = $teacher->homeroomClasses()->count();

        return view('guru.assessments.index', compact(
            'packages',
            'classes',
            'subjects',
            'teacher',
            'totalPackages',
            'draftPackages',
            'lockedPackages',
            'homeroomCount'
        ));
    }

    /**
     * Form pembuatan paket penilaian baru.
     */
    public function create(): View
    {
        $teacher = $this->getTeacher();
        $activeYear = AcademicYear::activeSemester() ?? AcademicYear::latest('start_date')->first();

        // Batasi pilihan sesuai penugasan jika memiliki pembatasan
        if ($teacher->hasTeachingRestrictions()) {
            $classes = $teacher->assignedClasses()->orderBy('level')->orderBy('name')->get();
            $subjects = $teacher->assignedSubjects()->orderBy('name')->get();
        } else {
            $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
            $subjects = Subject::orderBy('name')->get();
        }

        return view('guru.assessments.create', compact('teacher', 'activeYear', 'classes', 'subjects'));
    }

    /**
     * Simpan paket penilaian baru dan inisialisasi 15 sheet sumatif.
     */
    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();

        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'kktp_default' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Verifikasi izin mengajar guru
        if (! $teacher->canTeach((int) $validated['subject_id'], (int) $validated['school_class_id'])) {
            return back()->withInput()->with('error', 'Anda tidak memiliki penugasan untuk mengajar mata pelajaran ini di kelas tersebut.');
        }

        $activeYear = AcademicYear::activeSemester() ?? AcademicYear::latest('start_date')->first();
        if (! $activeYear) {
            return back()->withInput()->with('error', 'Belum ada Tahun Ajaran aktif di sistem.');
        }

        // Cek duplikasi paket
        $existing = AssessmentPackage::where('academic_year_id', $activeYear->id)
            ->where('school_class_id', $validated['school_class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if ($existing) {
            return redirect()->route('guru.penilaian.show', $existing)
                ->with('info', 'Paket penilaian untuk kelas dan mapel ini sudah ada.');
        }

        $package = AssessmentPackage::create([
            'academic_year_id' => $activeYear->id,
            'school_class_id' => $validated['school_class_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher->id,
            'title' => $validated['title'],
            'kktp_default' => $validated['kktp_default'],
            'status' => 'draft',
        ]);

        // Inisialisasi 15 lembar sumatif (SUM 1 s.d. SUM 15), dengan SUM 1 aktif secara default
        for ($i = 1; $i <= 15; $i++) {
            Assessment::create([
                'assessment_package_id' => $package->id,
                'sheet_number' => $i,
                'sheet_name' => "SUM {$i}",
                'kktp' => $package->kktp_default,
                'max_score' => 100,
                'is_active' => ($i === 1),
            ]);
        }

        return redirect()->route('guru.penilaian.show', $package)
            ->with('success', 'Paket Penilaian Sumatif berhasil dibuat!');
    }

    /**
     * Tampilkan detail matriks rekapitulasi penilaian sumatif.
     */
    public function show(AssessmentPackage $package): View
    {
        Gate::authorize('view', $package);

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
        $maxActiveSheet = $activeAssessments->max('sheet_number') ?? 1;
        $nextSheetNum = $maxActiveSheet < 15 ? $maxActiveSheet + 1 : null;

        // Statistik Keseluruhan
        $totalStudents = $students->count();
        $studentAverages = [];
        $tuntasCount = 0;
        $remedialCount = 0;
        $belumDinilaiCount = 0;

        foreach ($students as $student) {
            $studentScores = [];
            foreach ($activeAssessments as $assessment) {
                $score = $assessment->scores->firstWhere('student_id', $student->id)?->score;
                if ($score !== null) {
                    $studentScores[] = (float) $score;
                }
            }

            if (count($studentScores) > 0) {
                $avg = round(array_sum($studentScores) / count($studentScores), 2);
                $studentAverages[$student->id] = $avg;
                if ($avg >= $package->kktp_default) {
                    $tuntasCount++;
                } else {
                    $remedialCount++;
                }
            } else {
                $studentAverages[$student->id] = null;
                $belumDinilaiCount++;
            }
        }

        // Rata-rata per Sumatif (hanya sumatif yang aktif)
        $sheetAverages = [];
        foreach ($activeAssessments as $asm) {
            $scores = $asm->scores->whereNotNull('score')->pluck('score');
            $sheetAverages[$asm->sheet_number] = $scores->count() > 0 ? round($scores->avg(), 1) : null;
        }

        return view('guru.assessments.show', compact(
            'package',
            'students',
            'assessmentsByNum',
            'activeAssessments',
            'nextSheetNum',
            'studentAverages',
            'sheetAverages',
            'totalStudents',
            'tuntasCount',
            'remedialCount',
            'belumDinilaiCount'
        ));
    }

    /**
     * Unduh file template Excel 15 sheet.
     */
    public function downloadTemplate(AssessmentPackage $package, AssessmentExcelService $service): StreamedResponse
    {
        Gate::authorize('view', $package);

        return $service->downloadTemplate($package);
    }

    /**
     * Upload dan validasi in-memory file Excel sumatif.
     */
    public function uploadExcel(Request $request, AssessmentPackage $package, AssessmentExcelService $service): RedirectResponse
    {
        Gate::authorize('update', $package);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $file = $request->file('file');
        $result = $service->parseUploadedExcel($file, $package);

        if (! $result['is_valid']) {
            return back()->with('error', $result['error_message'] ?? 'Format file Excel tidak sesuai.');
        }

        session([
            'assessment_preview_'.$package->id => $result['sheets'],
            'assessment_summary_'.$package->id => $result['summary'],
            'assessment_filename_'.$package->id => $file->getClientOriginalName(),
        ]);

        return redirect()->route('guru.penilaian.preview', $package);
    }

    /**
     * Tampilkan halaman pratinjau (preview) data Excel sebelum di-commit.
     */
    public function previewImport(AssessmentPackage $package): View|RedirectResponse
    {
        Gate::authorize('update', $package);

        $sheets = session('assessment_preview_'.$package->id);
        $summary = session('assessment_summary_'.$package->id, []);
        $filename = session('assessment_filename_'.$package->id, 'file.xlsx');

        if (empty($sheets)) {
            return redirect()->route('guru.penilaian.show', $package)
                ->with('error', 'Tidak ada data pratinjau impor yang sedang aktif.');
        }

        return view('guru.assessments.preview', compact('package', 'sheets', 'summary', 'filename'));
    }

    /**
     * Publikasikan / simpan data pratinjau ke database.
     */
    public function publishImport(Request $request, AssessmentPackage $package, AssessmentExcelService $service): RedirectResponse
    {
        Gate::authorize('update', $package);

        $sheets = session('assessment_preview_'.$package->id);
        $filename = session('assessment_filename_'.$package->id, 'upload.xlsx');

        if (empty($sheets)) {
            return redirect()->route('guru.penilaian.show', $package)
                ->with('error', 'Tidak ada data pratinjau yang ditemukan untuk disimpan.');
        }

        $batch = $service->commitImport($package, $sheets, Auth::id(), $filename);

        session()->forget([
            'assessment_preview_'.$package->id,
            'assessment_summary_'.$package->id,
            'assessment_filename_'.$package->id,
        ]);

        $message = "Berhasil mengimpor nilai sumatif! ({$batch->success_rows} nilai siswa tersimpan).";
        if ($batch->failed_rows > 0) {
            $message .= " Terdapat {$batch->failed_rows} baris yang dilewati karena tidak sesuai.";
        }

        return redirect()->route('guru.penilaian.show', $package)->with('success', $message);
    }

    /**
     * Update cepat nilai siswa secara inline via web (tanpa harus upload Excel lagi).
     */
    public function quickUpdateScore(Request $request, AssessmentPackage $package): JsonResponse
    {
        Gate::authorize('update', $package);

        $validated = $request->validate([
            'assessment_id' => ['required', 'exists:assessments,id'],
            'student_id' => ['required', 'exists:students,id'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $assessment = Assessment::where('assessment_package_id', $package->id)
            ->findOrFail($validated['assessment_id']);

        $scoreVal = $validated['score'] !== null ? round((float) $validated['score'], 2) : null;
        $status = 'belum_dinilai';

        if ($scoreVal !== null) {
            $status = $scoreVal >= $assessment->kktp ? 'tuntas' : 'remedial';
        }

        $record = AssessmentScore::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => $validated['student_id'],
            ],
            [
                'score' => $scoreVal,
                'status' => $status,
            ]
        );

        $assessment->update(['is_active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Nilai berhasil diperbarui.',
            'score' => $record->score,
            'status' => $record->status,
        ]);
    }

    /**
     * Mengaktifkan lembar sumatif berikutnya secara manual via antarmuka web.
     */
    public function addAssessment(Request $request, AssessmentPackage $package): RedirectResponse
    {
        Gate::authorize('update', $package);

        if ($package->isLocked()) {
            return back()->with('error', 'Paket penilaian telah dikunci.');
        }

        $validated = $request->validate([
            'sheet_number' => ['required', 'integer', 'min:1', 'max:15'],
            'materi' => ['nullable', 'string', 'max:255'],
            'kktp' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $assessment = Assessment::firstOrCreate(
            [
                'assessment_package_id' => $package->id,
                'sheet_number' => (int) $validated['sheet_number'],
            ],
            [
                'sheet_name' => "SUM {$validated['sheet_number']}",
                'kktp' => (int) ($validated['kktp'] ?? $package->kktp_default),
                'max_score' => 100,
            ]
        );

        $assessment->update([
            'is_active' => true,
            'materi' => ! empty($validated['materi']) ? $validated['materi'] : $assessment->materi,
            'kktp' => ! empty($validated['kktp']) ? (int) $validated['kktp'] : $assessment->kktp,
        ]);

        return back()->with('success', "Kolom Penilaian Sumatif {$assessment->sheet_number} (SUM {$assessment->sheet_number}) berhasil diaktifkan!");
    }

    /**
     * Kunci paket penilaian (finalisasi).
     */
    public function lock(AssessmentPackage $package): RedirectResponse
    {
        Gate::authorize('lock', $package);

        $package->update([
            'status' => 'locked',
            'locked_at' => now(),
            'locked_by' => Auth::id(),
        ]);

        return back()->with('success', 'Paket Penilaian telah dikunci (Final). Nilai kini resmi dan dapat diakses pada portal Siswa & Orang Tua.');
    }

    /**
     * Ekspor rekapitulasi nilai ke Excel.
     */
    public function exportExcel(AssessmentPackage $package, AssessmentExcelService $service): StreamedResponse
    {
        Gate::authorize('view', $package);

        return $service->exportRecapExcel($package);
    }

    /**
     * Cetak dokumen rekapitulasi nilai resmi ber-Kop Surat (A4 Landscape).
     */
    public function printPdf(AssessmentPackage $package): View
    {
        Gate::authorize('view', $package);

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

        // Hitung rata-rata
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
     * Hapus paket penilaian (hanya jika masih berstatus draft).
     */
    public function destroy(AssessmentPackage $package): RedirectResponse
    {
        Gate::authorize('delete', $package);

        $package->delete();

        return redirect()->route('guru.penilaian.index')->with('success', 'Paket Penilaian berhasil dihapus.');
    }
}
