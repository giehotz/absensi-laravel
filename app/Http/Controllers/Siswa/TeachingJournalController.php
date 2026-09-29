<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeachingJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    /**
     * Display a listing of shared teaching journals for the logged-in student.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('user_id', $user->id)->firstOrFail();

        $subjectId = $request->input('subject_id');

        $query = TeachingJournal::with(['teacher.user', 'subject'])
            ->where('school_class_id', $student->school_class_id)
            ->where('is_shared_with_students', true)
            ->orderBy('date', 'desc')
            ->orderBy('meeting_number', 'desc');

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $journals = $query->paginate(10)->withQueryString();

        // Subjects that have shared journals for this student's class
        $subjectIds = TeachingJournal::where('school_class_id', $student->school_class_id)
            ->where('is_shared_with_students', true)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        return view('siswa.teaching-journals.index', compact(
            'student',
            'journals',
            'subjects',
            'subjectId'
        ));
    }
}
