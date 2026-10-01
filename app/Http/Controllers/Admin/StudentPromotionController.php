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
    public function index(Request $request): View|RedirectResponse
    {
        $allAcademicYears = AcademicYear::orderByDesc('start_date')->get();
        $activeAcademicYear = $allAcademicYears->firstWhere('is_active', true) ?? $allAcademicYears->first();

        // 1. Tahun Ajaran Asal (HANYA Semester Genap)
        $sourceAcademicYears = $allAcademicYears->where('semester', 'genap')->values();

        // Default asal: semester Genap dari tahun yang sedang aktif, atau semester Genap terbaru
        $defaultSourceYear = $sourceAcademicYears->firstWhere('name', $activeAcademicYear?->name)
            ?? $sourceAcademicYears->first()
            ?? $activeAcademicYear;

        $requestedSourceId = (int) $request->query('source_academic_year_id');

        // Jika user menginputkan ID semester Ganjil, otomatis arahkan ke pasangan Genap-nya
        $requestedSourceObj = $allAcademicYears->firstWhere('id', $requestedSourceId);
        if ($requestedSourceObj && $requestedSourceObj->semester === 'ganjil') {
            $genapPair = $sourceAcademicYears->firstWhere('name', $requestedSourceObj->name);
            if ($genapPair) {
                return redirect()->route('admin.students.promotion.index', array_merge(
                    $request->query(),
                    ['source_academic_year_id' => $genapPair->id]
                ));
            }
        }

        $sourceAcademicYear = $sourceAcademicYears->firstWhere('id', $requestedSourceId) ?? $defaultSourceYear;

        // 2. Tahun Ajaran Tujuan (HANYA Semester Ganjil di masa depan yang lebih baru dari tahun asal)
        $targetAcademicYears = $allAcademicYears
            ->where('semester', 'ganjil')
            ->filter(function ($ay) use ($sourceAcademicYear) {
                return $sourceAcademicYear
                    ? ($ay->start_date > $sourceAcademicYear->start_date && $ay->name !== $sourceAcademicYear->name)
                    : true;
            })
            ->sortBy('start_date')
            ->values();

        $defaultTargetYear = $targetAcademicYears->first()
            ?? $allAcademicYears->where('semester', 'ganjil')->firstWhere('name', '!=', $sourceAcademicYear?->name);

        $requestedTargetId = (int) $request->query('target_academic_year_id');
        $targetAcademicYear = $targetAcademicYears->firstWhere('id', $requestedTargetId) ?? $defaultTargetYear;

        // Status apakah semester aktif sekolah saat ini masih Ganjil
        $isActiveSemesterStillGanjil = ($activeAcademicYear?->semester === 'ganjil' && $activeAcademicYear?->name === $sourceAcademicYear?->name);
        $activeSemesterName = $activeAcademicYear ? "{$activeAcademicYear->name} (".ucfirst($activeAcademicYear->semester).')' : '-';

        // 3. Daftar Kelas di Tahun Ajaran Asal & Tujuan (berdasarkan tahun pelajaran induk)
        $sourceClasses = SchoolClass::forAcademicYearName($sourceAcademicYear?->name ?? '')
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $targetClasses = SchoolClass::forAcademicYearName($targetAcademicYear?->name ?? '')
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // 4. Kelas Asal Terpilih
        $selectedSourceClassId = (int) $request->query('source_class_id', 0);
        $selectedSourceClass = $sourceClasses->firstWhere('id', $selectedSourceClassId);

        $students = collect();
        if ($selectedSourceClass) {
            $students = Student::with('user')
                ->where('school_class_id', $selectedSourceClass->id)
                ->get()
                ->sortBy(fn ($s) => $s->user->name ?? '', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();

            // Ambil akumulasi presensi siswa 1 tahun penuh (Ganjil + Genap dari tahun asal yang dipilih)
            if ($students->isNotEmpty()) {
                $ganjilSemesterOfSource = $allAcademicYears
                    ->where('name', $sourceAcademicYear?->name)
                    ->firstWhere('semester', 'ganjil');

                $yearStartDate = $ganjilSemesterOfSource?->start_date ?? $sourceAcademicYear?->start_date;
                $yearEndDate = $sourceAcademicYear?->end_date;

                $attendanceQuery = Attendance::whereIn('student_id', $students->pluck('id'));
                if ($yearStartDate && $yearEndDate) {
                    $attendanceQuery->whereBetween('date', [
                        $yearStartDate->format('Y-m-d'),
                        $yearEndDate->format('Y-m-d'),
                    ]);
                }

                $attendanceStats = $attendanceQuery
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

        return view('admin.students.promotion.index', [
            'academicYears' => $allAcademicYears,
            'sourceAcademicYears' => $sourceAcademicYears,
            'targetAcademicYears' => $targetAcademicYears,
            'sourceAcademicYear' => $sourceAcademicYear,
            'targetAcademicYear' => $targetAcademicYear,
            'sourceClasses' => $sourceClasses,
            'targetClasses' => $targetClasses,
            'selectedSourceClass' => $selectedSourceClass,
            'students' => $students,
            'isActiveSemesterStillGanjil' => $isActiveSemesterStillGanjil,
            'activeSemesterName' => $activeSemesterName,
        ]);
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

                if ($sourceClass->academicYear?->name === $targetClass->academicYear?->name) {
                    return redirect()->back()->with('error', "Kenaikan kelas hanya dapat dilakukan antar-tahun ajaran yang berbeda. Kelas {$sourceClass->name} dan {$targetClass->name} berada dalam tahun ajaran yang sama ({$sourceClass->academicYear?->name}). Gunakan menu Mutasi Rombel jika ingin memindahkan rombel siswa dalam tahun yang sama.");
                }

                if ($targetClass->academicYear?->semester !== 'ganjil') {
                    return redirect()->back()->with('error', 'Kenaikan kelas hanya dapat ditargetkan ke Semester Ganjil pada tahun ajaran baru.');
                }

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

        $fromYear = AcademicYear::findOrFail($fromYearId);
        $toYear = AcademicYear::findOrFail($toYearId);

        if ($fromYear->name === $toYear->name) {
            return back()->with('error', "Tahun ajaran asal ({$fromYear->name}) dan tujuan ({$toYear->name}) berada dalam tahun pelajaran yang sama. Salin struktur kelas hanya diperlukan saat berganti ke tahun pelajaran baru (contoh: 2026/2027 ke 2027/2028).");
        }

        $sourceClasses = SchoolClass::forAcademicYearName($fromYear->name)
            ->where('name', '!=', 'Alumni')
            ->get();

        if ($sourceClasses->isEmpty()) {
            return back()->with('error', 'Tidak ada kelas pada Tahun Ajaran Asal yang dapat disalin.');
        }

        $copiedCount = 0;
        foreach ($sourceClasses as $src) {
            $exists = SchoolClass::forAcademicYearName($toYear->name)
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

        $targetYear = AcademicYear::findOrFail($request->input('academic_year_id'));

        $exists = SchoolClass::forAcademicYearName($targetYear->name)
            ->where('name', $request->input('name'))
            ->exists();

        if ($exists) {
            return back()->with('error', "Kelas dengan nama '{$request->input('name')}' sudah ada pada tahun ajaran ini.");
        }

        SchoolClass::create([
            'academic_year_id' => $targetYear->id,
            'name' => $request->input('name'),
            'level' => $request->input('level'),
            'homeroom_teacher_id' => null,
        ]);

        return back()->with('success', "Rombel kelas '{$request->input('name')}' berhasil ditambahkan!");
    }
}
