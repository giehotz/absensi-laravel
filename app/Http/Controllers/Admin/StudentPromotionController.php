<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentPromotionController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan Kenaikan Kelas & Kelulusan Siswa.
     */
    public function index(Request $request): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $activeAcademicYear = $academicYears->firstWhere('is_active', true) ?? $academicYears->first();

        // 1. Tahun Ajaran Asal & Tujuan
        $sourceAcademicYearId = (int) $request->query('source_academic_year_id', $activeAcademicYear?->id ?? 0);
        $sourceAcademicYear = $academicYears->firstWhere('id', $sourceAcademicYearId) ?? $activeAcademicYear;

        $targetAcademicYearId = (int) $request->query('target_academic_year_id', $activeAcademicYear?->id ?? 0);
        $targetAcademicYear = $academicYears->firstWhere('id', $targetAcademicYearId) ?? $activeAcademicYear;

        // 2. Daftar Kelas di Tahun Ajaran Asal & Tujuan
        $sourceClasses = SchoolClass::where('academic_year_id', $sourceAcademicYear?->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $targetClasses = SchoolClass::where('academic_year_id', $targetAcademicYear?->id)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // 3. Kelas Asal Terpilih
        $selectedSourceClassId = (int) $request->query('source_class_id', 0);
        $selectedSourceClass = $sourceClasses->firstWhere('id', $selectedSourceClassId);

        $students = collect();
        if ($selectedSourceClass) {
            $students = Student::with('user')
                ->where('school_class_id', $selectedSourceClass->id)
                ->get()
                ->sortBy(fn ($s) => $s->user->name ?? '', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            // Ambil akumulasi presensi siswa untuk bahan pertimbangan kelayakan
            if ($students->isNotEmpty()) {
                $attendanceStats = Attendance::whereIn('student_id', $students->pluck('id'))
                    ->selectRaw('student_id, status, count(*) as total')
                    ->groupBy('student_id', 'status')
                    ->get()
                    ->groupBy('student_id');

                $students->each(function ($student) use ($attendanceStats) {
                    $stats = $attendanceStats->get($student->id, collect());
                    $hadir = (int) ($stats->firstWhere('status', 'hadir')?->total ?? 0);
                    $terlambat = (int) ($stats->firstWhere('status', 'terlambat')?->total ?? 0);
                    $izin = (int) ($stats->firstWhere('status', 'izin')?->total ?? 0);
                    $sakit = (int) ($stats->firstWhere('status', 'sakit')?->total ?? 0);
                    $alpa = (int) ($stats->firstWhere('status', 'alpa')?->total ?? 0);

                    $totalDays = $hadir + $terlambat + $izin + $sakit + $alpa;
                    $effectiveAttendance = $hadir + $terlambat;
                    $rate = $totalDays > 0 ? round(($effectiveAttendance / $totalDays) * 100, 1) : 0;

                    $student->att_hadir = $hadir;
                    $student->att_terlambat = $terlambat;
                    $student->att_izin = $izin;
                    $student->att_sakit = $sakit;
                    $student->att_alpa = $alpa;
                    $student->att_total = $totalDays;
                    $student->att_rate = $rate;
                });
            }
        }

        return view('admin.students.promotion.index', compact(
            'academicYears',
            'sourceAcademicYear',
            'targetAcademicYear',
            'sourceClasses',
            'targetClasses',
            'selectedSourceClass',
            'students'
        ));
    }

    /**
     * Proses Kenaikan Kelas atau Kelulusan Siswa secara massal (Batch).
     */
    public function promote(Request $request): RedirectResponse
    {
        $request->validate([
            'source_class_id' => 'required|exists:school_classes,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'action_type' => 'required|in:promote,graduate',
            'target_class_id' => 'required_if:action_type,promote|nullable|exists:school_classes,id',
        ], [
            'student_ids.required' => 'Pilih minimal satu siswa yang akan diproses.',
            'target_class_id.required_if' => 'Pilih kelas tujuan untuk kenaikan kelas.',
        ]);

        $studentIds = $request->input('student_ids');
        $actionType = $request->input('action_type');
        $sourceClass = SchoolClass::findOrFail($request->input('source_class_id'));

        return DB::transaction(function () use ($studentIds, $actionType, $sourceClass, $request) {
            $count = count($studentIds);

            if ($actionType === 'promote') {
                $targetClass = SchoolClass::with('academicYear')->findOrFail($request->input('target_class_id'));

                Student::whereIn('id', $studentIds)->update([
                    'school_class_id' => $targetClass->id,
                ]);

                $message = "Berhasil menaikkan {$count} siswa dari {$sourceClass->name} ke {$targetClass->name} ({$targetClass->academicYear?->name})!";
            } else {
                // Kelulusan: Pindahkan ke rombel khusus 'Alumni' dan nonaktifkan akun login
                $alumniClass = SchoolClass::firstOrCreate(
                    [
                        'name' => 'Alumni',
                        'level' => 'Alumni',
                    ],
                    [
                        'academic_year_id' => $sourceClass->academic_year_id,
                    ]
                );

                $students = Student::whereIn('id', $studentIds)->get();

                Student::whereIn('id', $studentIds)->update([
                    'school_class_id' => $alumniClass->id,
                ]);

                User::whereIn('id', $students->pluck('user_id'))->update([
                    'is_active' => false,
                ]);

                $message = "Berhasil meluluskan {$count} siswa dari {$sourceClass->name} menjadi Alumni!";
            }

            return redirect()->route('admin.students.promotion.index', [
                'source_academic_year_id' => $sourceClass->academic_year_id,
                'target_academic_year_id' => $request->input('target_academic_year_id', $sourceClass->academic_year_id),
                'source_class_id' => $sourceClass->id,
            ])->with('success', $message);
        });
    }

    /**
     * Salin seluruh struktur kelas dari Tahun Ajaran Asal ke Tahun Ajaran Tujuan.
     */
    public function copyClasses(Request $request): RedirectResponse
    {
        $request->validate([
            'from_academic_year_id' => 'required|exists:academic_years,id',
            'to_academic_year_id' => 'required|exists:academic_years,id|different:from_academic_year_id',
        ], [
            'to_academic_year_id.different' => 'Tahun ajaran asal dan tujuan harus berbeda untuk menyalin struktur kelas.',
        ]);

        $fromYearId = $request->input('from_academic_year_id');
        $toYearId = $request->input('to_academic_year_id');

        $sourceClasses = SchoolClass::where('academic_year_id', $fromYearId)
            ->where('name', '!=', 'Alumni')
            ->get();

        if ($sourceClasses->isEmpty()) {
            return back()->with('error', 'Tidak ada kelas pada Tahun Ajaran Asal yang dapat disalin.');
        }

        $copiedCount = 0;
        foreach ($sourceClasses as $src) {
            $exists = SchoolClass::where('academic_year_id', $toYearId)
                ->where('name', $src->name)
                ->exists();

            if (! $exists) {
                SchoolClass::create([
                    'academic_year_id' => $toYearId,
                    'name' => $src->name,
                    'level' => $src->level,
                    'homeroom_teacher_id' => null, // Reset wali kelas untuk tahun baru
                ]);
                $copiedCount++;
            }
        }

        $toYear = AcademicYear::find($toYearId);

        return back()->with('success', "Berhasil menyalin {$copiedCount} struktur kelas ke Tahun Ajaran {$toYear->name} ({$toYear->semester})!");
    }

    /**
     * Tambah Rombel / Kelas Baru secara cepat langsung dari halaman kenaikan kelas.
     */
    public function quickStoreClass(Request $request): RedirectResponse
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:50',
            'level' => 'required|string|max:20',
        ]);

        $exists = SchoolClass::where('academic_year_id', $request->input('academic_year_id'))
            ->where('name', $request->input('name'))
            ->exists();

        if ($exists) {
            return back()->with('error', "Kelas dengan nama '{$request->input('name')}' sudah ada pada tahun ajaran ini.");
        }

        SchoolClass::create([
            'academic_year_id' => $request->input('academic_year_id'),
            'name' => $request->input('name'),
            'level' => $request->input('level'),
            'homeroom_teacher_id' => null,
        ]);

        return back()->with('success', "Rombel kelas '{$request->input('name')}' berhasil ditambahkan!");
    }
}
