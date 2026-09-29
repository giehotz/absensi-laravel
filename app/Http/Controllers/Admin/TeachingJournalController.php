<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    /**
     * Display a listing of all teaching journals in the school.
     */
    public function index(Request $request): View
    {
        $teacherId = $request->input('teacher_id');
        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = TeachingJournal::with(['teacher.user', 'schoolClass', 'subject', 'schedule'])
            ->orderBy('date', 'desc')
            ->orderBy('meeting_number', 'desc');

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

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

        $journals = $query->paginate(20)->withQueryString();

        // Filters data
        $teachers = Teacher::with('user')->has('teachingJournals')->orWhereHas('schedules')->get();
        if ($teachers->isEmpty()) {
            $teachers = Teacher::with('user')->get();
        }

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        // Summary KPI stats for school admin
        $totalJournals = TeachingJournal::count();
        $activeTeachersCount = TeachingJournal::distinct('teacher_id')->count('teacher_id');
        $thisMonthJournals = TeachingJournal::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->count();
        $avgAttendance = TeachingJournal::avg('attendance_percentage') ?? 0;

        return view('admin.teaching-journals.index', compact(
            'journals',
            'teachers',
            'classes',
            'subjects',
            'teacherId',
            'classId',
            'subjectId',
            'startDate',
            'endDate',
            'totalJournals',
            'activeTeachersCount',
            'thisMonthJournals',
            'avgAttendance'
        ));
    }

    /**
     * Display the specified teaching journal details.
     */
    public function show(TeachingJournal $teachingJournal): View
    {
        $teachingJournal->load(['teacher.user', 'schoolClass', 'subject', 'schedule']);

        return view('admin.teaching-journals.show', compact('teachingJournal'));
    }

    /**
     * Remove the specified teaching journal from storage.
     */
    public function destroy(TeachingJournal $teachingJournal): RedirectResponse
    {
        $teachingJournal->delete();

        return redirect()->route('admin.teaching-journals.index')
            ->with('success', 'Jurnal Kegiatan Mengajar berhasil dihapus.');
    }

    /**
     * Printable view of teaching journals with official Kop Surat for Admin.
     */
    public function print(Request $request): View
    {
        $setting = AttendanceSetting::first();

        $teacherId = $request->input('teacher_id');
        $classId = $request->input('school_class_id');
        $subjectId = $request->input('subject_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = TeachingJournal::with(['teacher.user', 'schoolClass', 'subject'])
            ->orderBy('date', 'asc')
            ->orderBy('meeting_number', 'asc');

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

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

        $teacher = $teacherId ? Teacher::with('user')->find($teacherId) : null;
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
}
