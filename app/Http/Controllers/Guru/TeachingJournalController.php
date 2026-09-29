<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeachingJournalRequest;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Services\TeachingJournal\TeachingJournalExcelService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeachingJournalController extends Controller
{
    protected array $dayNames = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Helper to get current authenticated teacher.
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
     * Display a listing of the teacher's journals.
     */
    public function index(Request $request): View
    {
        $teacher = $this->getTeacher();

        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = TeachingJournal::with(['schoolClass', 'subject', 'schedule'])
            ->forTeacher($teacher->id)
            ->orderBy('date', 'desc')
            ->orderBy('meeting_number', 'desc');

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $journals = $query->paginate(15)->withQueryString();

        // Data filter options: classes & subjects taught by this teacher
        $scheduleClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $journalClassIds = TeachingJournal::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $classes = SchoolClass::whereIn('id', $scheduleClassIds->merge($journalClassIds)->unique())
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $scheduleSubjectIds = Schedule::where('teacher_id', $teacher->id)->pluck('subject_id');
        $journalSubjectIds = TeachingJournal::where('teacher_id', $teacher->id)->pluck('subject_id');
        $subjects = Subject::whereIn('id', $scheduleSubjectIds->merge($journalSubjectIds)->unique())
            ->orderBy('name')
            ->get();

        // KPI Metrics
        $totalJournals = TeachingJournal::where('teacher_id', $teacher->id)->count();
        $thisMonthJournals = TeachingJournal::where('teacher_id', $teacher->id)
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->count();
        $avgAttendance = TeachingJournal::where('teacher_id', $teacher->id)->avg('attendance_percentage') ?? 0;

        return view('guru.teaching-journals.index', compact(
            'journals',
            'classes',
            'subjects',
            'classId',
            'subjectId',
            'startDate',
            'endDate',
            'totalJournals',
            'thisMonthJournals',
            'avgAttendance'
        ));
    }

    /**
     * Show the form for creating a new journal.
     */
    public function create(Request $request): View
    {
        $teacher = $this->getTeacher();
        $date = $request->input('date', Carbon::today()->toDateString());
        $selectedClassId = $request->input('school_class_id');
        $selectedSubjectId = $request->input('subject_id');
        $selectedScheduleId = $request->input('schedule_id');

        // Teacher's schedules
        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // If schedule_id is provided, auto-fill class and subject
        if ($selectedScheduleId) {
            $schedule = $schedules->firstWhere('id', $selectedScheduleId);
            if ($schedule) {
                $selectedClassId = $schedule->school_class_id;
                $selectedSubjectId = $schedule->subject_id;
            }
        }

        // Distinct classes and subjects taught by teacher
        $classes = SchoolClass::whereIn('id', $schedules->pluck('school_class_id')->unique())
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        }

        $subjects = Subject::whereIn('id', $schedules->pluck('subject_id')->unique())
            ->orderBy('name')
            ->get();

        if ($subjects->isEmpty()) {
            $subjects = Subject::orderBy('name')->get();
        }

        return view('guru.teaching-journals.create', compact(
            'schedules',
            'classes',
            'subjects',
            'date',
            'selectedClassId',
            'selectedSubjectId',
            'selectedScheduleId'
        ));
    }

    /**
     * AJAX endpoint to check attendance for a class on a specific date.
     */
    public function checkAttendance(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher();
        $classId = (int) $request->input('school_class_id');
        $date = $request->input('date');
        $subjectId = (int) $request->input('subject_id');

        if (! $classId || ! $date) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas dan Tanggal wajib diisi.',
            ], 422);
        }

        $attendances = Attendance::whereDate('date', $date)
            ->whereHas('student', fn ($q) => $q->where('school_class_id', $classId))
            ->get();

        if ($attendances->isEmpty()) {
            return response()->json([
                'success' => true,
                'has_attendance' => false,
                'message' => 'Presensi siswa pada tanggal dan kelas ini belum diisi oleh siapapun.',
                'manual_attendance_url' => route('guru.attendance.manual', [
                    'school_class_id' => $classId,
                    'date' => $date,
                ]),
            ]);
        }

        // Calculate attendance summary
        $totalStudents = Student::where('school_class_id', $classId)->count();
        if ($totalStudents === 0) {
            $totalStudents = $attendances->count();
        }

        $countHadir = $attendances->where('status', 'hadir')->count();
        $countTerlambat = $attendances->where('status', 'terlambat')->count();
        $countSakit = $attendances->where('status', 'sakit')->count();
        $countIzin = $attendances->where('status', 'izin')->count();
        $countAlpa = $attendances->where('status', 'alpa')->count();

        $presentCount = $countHadir + $countTerlambat;
        $percentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0;

        // Auto recommend next meeting number
        $nextMeeting = 1;
        if ($subjectId) {
            $lastMeeting = TeachingJournal::where('teacher_id', $teacher->id)
                ->where('school_class_id', $classId)
                ->where('subject_id', $subjectId)
                ->max('meeting_number');

            $nextMeeting = $lastMeeting ? ((int) $lastMeeting + 1) : 1;
        }

        return response()->json([
            'success' => true,
            'has_attendance' => true,
            'stats' => [
                'total_students' => $totalStudents,
                'count_hadir' => $countHadir,
                'count_terlambat' => $countTerlambat,
                'count_sakit' => $countSakit,
                'count_izin' => $countIzin,
                'count_alpa' => $countAlpa,
                'attendance_percentage' => $percentage,
            ],
            'next_meeting' => $nextMeeting,
        ]);
    }

    /**
     * Store a newly created journal in storage.
     */
    public function store(TeachingJournalRequest $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        $validated = $request->validated();

        $classId = (int) $validated['school_class_id'];
        $date = $validated['date'];

        // Strict Prerequisite Check: Attendance must exist
        $attendances = Attendance::whereDate('date', $date)
            ->whereHas('student', fn ($q) => $q->where('school_class_id', $classId))
            ->get();

        if ($attendances->isEmpty()) {
            return back()
                ->withInput()
                ->with('error_attendance', [
                    'message' => 'Data presensi siswa untuk kelas dan tanggal tersebut belum diisi!',
                    'url' => route('guru.attendance.manual', ['school_class_id' => $classId, 'date' => $date]),
                ]);
        }

        // Compute authoritative stats from database
        $totalStudents = Student::where('school_class_id', $classId)->count();
        if ($totalStudents === 0) {
            $totalStudents = $attendances->count();
        }

        $countHadir = $attendances->where('status', 'hadir')->count();
        $countTerlambat = $attendances->where('status', 'terlambat')->count();
        $countSakit = $attendances->where('status', 'sakit')->count();
        $countIzin = $attendances->where('status', 'izin')->count();
        $countAlpa = $attendances->where('status', 'alpa')->count();

        $presentCount = $countHadir + $countTerlambat;
        $percentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0;

        $carbonDate = Carbon::parse($date);
        $dayOfWeekIso = (int) $carbonDate->dayOfWeekIso;
        $dayName = $this->dayNames[$dayOfWeekIso] ?? 'Senin';

        TeachingJournal::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $classId,
            'subject_id' => $validated['subject_id'],
            'schedule_id' => $validated['schedule_id'] ?? null,
            'date' => $date,
            'day_name' => $dayName,
            'meeting_number' => $validated['meeting_number'],
            'learning_objective' => $validated['learning_objective'],
            'teaching_activity' => $validated['teaching_activity'],
            'teaching_problem' => $validated['teaching_problem'] ?? null,
            'total_students' => $totalStudents,
            'count_hadir' => $countHadir,
            'count_terlambat' => $countTerlambat,
            'count_sakit' => $countSakit,
            'count_izin' => $countIzin,
            'count_alpa' => $countAlpa,
            'attendance_percentage' => $percentage,
            'is_shared_with_students' => (bool) ($request->input('is_shared_with_students', 0)),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('guru.teaching-journals.index')
            ->with('success', 'Jurnal Kegiatan Harian Mengajar berhasil disimpan!');
    }

    /**
     * Show the form for editing the specified journal.
     */
    public function edit(TeachingJournal $teachingJournal): View
    {
        $teacher = $this->getTeacher();
        abort_unless($teachingJournal->teacher_id === $teacher->id, 403);

        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('guru.teaching-journals.edit', compact(
            'teachingJournal',
            'schedules',
            'classes',
            'subjects'
        ));
    }

    /**
     * Update the specified journal in storage.
     */
    public function update(TeachingJournalRequest $request, TeachingJournal $teachingJournal): RedirectResponse
    {
        $teacher = $this->getTeacher();
        abort_unless($teachingJournal->teacher_id === $teacher->id, 403);

        $validated = $request->validated();
        $classId = (int) $validated['school_class_id'];
        $date = $validated['date'];

        // Re-check attendance
        $attendances = Attendance::whereDate('date', $date)
            ->whereHas('student', fn ($q) => $q->where('school_class_id', $classId))
            ->get();

        if ($attendances->isEmpty()) {
            return back()
                ->withInput()
                ->with('error_attendance', [
                    'message' => 'Data presensi siswa untuk kelas dan tanggal tersebut belum diisi!',
                    'url' => route('guru.attendance.manual', ['school_class_id' => $classId, 'date' => $date]),
                ]);
        }

        $totalStudents = Student::where('school_class_id', $classId)->count();
        if ($totalStudents === 0) {
            $totalStudents = $attendances->count();
        }

        $countHadir = $attendances->where('status', 'hadir')->count();
        $countTerlambat = $attendances->where('status', 'terlambat')->count();
        $countSakit = $attendances->where('status', 'sakit')->count();
        $countIzin = $attendances->where('status', 'izin')->count();
        $countAlpa = $attendances->where('status', 'alpa')->count();

        $presentCount = $countHadir + $countTerlambat;
        $percentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0;

        $carbonDate = Carbon::parse($date);
        $dayOfWeekIso = (int) $carbonDate->dayOfWeekIso;
        $dayName = $this->dayNames[$dayOfWeekIso] ?? 'Senin';

        $teachingJournal->update([
            'school_class_id' => $classId,
            'subject_id' => $validated['subject_id'],
            'schedule_id' => $validated['schedule_id'] ?? null,
            'date' => $date,
            'day_name' => $dayName,
            'meeting_number' => $validated['meeting_number'],
            'learning_objective' => $validated['learning_objective'],
            'teaching_activity' => $validated['teaching_activity'],
            'teaching_problem' => $validated['teaching_problem'] ?? null,
            'total_students' => $totalStudents,
            'count_hadir' => $countHadir,
            'count_terlambat' => $countTerlambat,
            'count_sakit' => $countSakit,
            'count_izin' => $countIzin,
            'count_alpa' => $countAlpa,
            'attendance_percentage' => $percentage,
            'is_shared_with_students' => (bool) ($request->input('is_shared_with_students', 0)),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('guru.teaching-journals.index')
            ->with('success', 'Jurnal Kegiatan Harian Mengajar berhasil diperbarui!');
    }

    /**
     * Remove the specified journal from storage.
     */
    public function destroy(TeachingJournal $teachingJournal): RedirectResponse
    {
        $teacher = $this->getTeacher();
        abort_unless($teachingJournal->teacher_id === $teacher->id, 403);

        $teachingJournal->delete();

        return redirect()->route('guru.teaching-journals.index')
            ->with('success', 'Jurnal Kegiatan Mengajar berhasil dihapus.');
    }

    /**
     * Printable view of teaching journals with official Kop Surat.
     */
    public function print(Request $request): View
    {
        $teacher = $this->getTeacher();
        $setting = AttendanceSetting::first();

        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = TeachingJournal::with(['schoolClass', 'subject', 'teacher.user'])
            ->forTeacher($teacher->id)
            ->orderBy('date', 'asc')
            ->orderBy('meeting_number', 'asc');

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $journals = $query->get();

        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        $selectedSubject = $subjectId ? Subject::find($subjectId) : null;

        return view('reports.teaching-journal-print', compact(
            'journals',
            'teacher',
            'setting',
            'selectedClass',
            'selectedSubject',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Unduh template file Excel khusus jadwal mengajar guru.
     */
    public function downloadTemplate(TeachingJournalExcelService $service): StreamedResponse
    {
        $teacher = $this->getTeacher();

        return $service->downloadTemplate($teacher);
    }

    /**
     * Unggah file Excel jurnal dan alihkan ke halaman pratinjau/review.
     */
    public function uploadExcel(Request $request, TeachingJournalExcelService $service): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib dipilih.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        $teacher = $this->getTeacher();
        $rows = $service->parseExcel($request->file('file'), $teacher);

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki baris data KBM yang valid.');
        }

        session(['teaching_journal_import_rows' => $rows]);

        return redirect()->route('guru.teaching-journals.import.preview');
    }

    /**
     * Tampilkan halaman pratinjau form edit baris Excel sebelum dipublish.
     */
    public function previewImport(): View|RedirectResponse
    {
        $rows = session('teaching_journal_import_rows', []);
        if (empty($rows)) {
            return redirect()->route('guru.teaching-journals.index')
                ->with('error', 'Tidak ada data impor yang sedang aktif.');
        }

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('guru.teaching-journals.preview-import', compact('rows', 'classes', 'subjects'));
    }

    /**
     * Publikasikan (simpan ke database) baris-baris jurnal yang telah diperiksa/diedit.
     */
    public function publishImport(Request $request): RedirectResponse
    {
        $teacher = $this->getTeacher();
        $items = $request->input('items', []);

        if (empty($items)) {
            return redirect()->route('guru.teaching-journals.index')
                ->with('error', 'Tidak ada data jurnal yang dikirim untuk dipublikasikan.');
        }

        $publishedCount = 0;
        $skippedCount = 0;
        $skippedReasons = [];

        foreach ($items as $item) {
            $classId = (int) ($item['school_class_id'] ?? 0);
            $subjectId = (int) ($item['subject_id'] ?? 0);
            $date = $item['date'] ?? null;
            $meetingNumber = (int) ($item['meeting_number'] ?? 1);
            $objective = trim((string) ($item['learning_objective'] ?? ''));
            $activity = trim((string) ($item['teaching_activity'] ?? ''));
            $problem = trim((string) ($item['teaching_problem'] ?? ''));
            $isShared = ! empty($item['is_shared_with_students']);

            if (! $classId || ! $subjectId || ! $date || empty($objective) || empty($activity)) {
                $skippedCount++;

                continue;
            }

            // Cek prasyarat presensi
            $attendances = Attendance::whereDate('date', $date)
                ->whereHas('student', fn ($q) => $q->where('school_class_id', $classId))
                ->get();

            if ($attendances->isEmpty()) {
                $skippedCount++;
                $className = SchoolClass::find($classId)?->name ?? 'Kelas '.$classId;
                $skippedReasons[] = "Kelas {$className} pada tanggal {$date} dilewati karena presensi siswa belum diisi.";

                continue;
            }

            // Hitung statistik presensi
            $totalStudents = Student::where('school_class_id', $classId)->count();
            if ($totalStudents === 0) {
                $totalStudents = $attendances->count();
            }

            $countHadir = $attendances->where('status', 'hadir')->count();
            $countTerlambat = $attendances->where('status', 'terlambat')->count();
            $countSakit = $attendances->where('status', 'sakit')->count();
            $countIzin = $attendances->where('status', 'izin')->count();
            $countAlpa = $attendances->where('status', 'alpa')->count();

            $presentCount = $countHadir + $countTerlambat;
            $percentage = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0;

            $carbonDate = Carbon::parse($date);
            $dayName = $this->dayNames[(int) $carbonDate->dayOfWeekIso] ?? 'Senin';

            TeachingJournal::create([
                'teacher_id' => $teacher->id,
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
                'schedule_id' => ! empty($item['schedule_id']) ? (int) $item['schedule_id'] : null,
                'date' => $date,
                'day_name' => $dayName,
                'meeting_number' => $meetingNumber,
                'learning_objective' => $objective,
                'teaching_activity' => $activity,
                'teaching_problem' => ! empty($problem) ? $problem : null,
                'total_students' => $totalStudents,
                'count_hadir' => $countHadir,
                'count_terlambat' => $countTerlambat,
                'count_sakit' => $countSakit,
                'count_izin' => $countIzin,
                'count_alpa' => $countAlpa,
                'attendance_percentage' => $percentage,
                'is_shared_with_students' => $isShared,
            ]);

            $publishedCount++;
        }

        session()->forget('teaching_journal_import_rows');

        $message = "Berhasil mempublikasikan {$publishedCount} jurnal kegiatan KBM!";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} baris dilewati karena data tidak lengkap atau presensi belum diisi).";
        }

        return redirect()->route('guru.teaching-journals.index')->with('success', $message);
    }

    /**
     * Toggle cepat visibilitas publikasi jurnal ke siswa langsung dari tabel indeks.
     */
    public function toggleShare(TeachingJournal $teachingJournal): RedirectResponse
    {
        $teacher = $this->getTeacher();
        abort_unless($teachingJournal->teacher_id === $teacher->id, 403);

        $teachingJournal->update([
            'is_shared_with_students' => ! $teachingJournal->is_shared_with_students,
        ]);

        $statusText = $teachingJournal->is_shared_with_students
            ? 'Jurnal sekarang DITAMPILKAN ke siswa kelas terkait.'
            : 'Jurnal sekarang DISEMBUNYIKAN dari siswa.';

        return back()->with('success', $statusText);
    }
}
