<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPackage;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    /**
     * Tampilkan capaian nilai sumatif siswa untuk paket-paket yang sudah berstatus locked.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $student = $user->student ?? Student::where('user_id', $user->id)->firstOrFail();

        $packages = AssessmentPackage::with([
            'subject',
            'teacher.user',
            'academicYear',
            'assessments' => function ($q) use ($student) {
                $q->with(['scores' => function ($sq) use ($student) {
                    $sq->where('student_id', $student->id);
                }]);
            },
        ])
            ->where('school_class_id', $student->school_class_id)
            ->where('status', 'locked')
            ->get();

        return view('siswa.grades.index', compact('student', 'packages'));
    }
}
