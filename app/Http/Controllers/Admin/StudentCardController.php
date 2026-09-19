<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCardController extends Controller
{
    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Menampilkan studio kartu tanda pelajar & QR code absensi dengan filter dan multi-select.
     */
    public function index(Request $request): View
    {
        $selectedClassId = $request->query('class_id');
        $search = $request->query('search');

        $classes = SchoolClass::orderBy('level')->orderBy('name')->get();

        $query = Student::with(['user', 'schoolClass']);

        if (! empty($selectedClassId) && $selectedClassId !== 'all') {
            $query->where('school_class_id', $selectedClassId);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query->paginate(24)->withQueryString();

        // Generate QR code data URI untuk setiap siswa
        foreach ($students as $student) {
            $student->qr_data_uri = $this->qrCodeService->generateDataUri($student->qr_code_identifier, 160, 2);
        }

        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
            'school_address' => 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri',
        ]);

        return view('admin.students.cards.index', [
            'students' => $students,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'search' => $search,
            'setting' => $setting,
        ]);
    }

    /**
     * Menampilkan halaman cetak massal lembar A4 (grid 2x4 = 8 kartu per lembar).
     */
    public function print(Request $request): View
    {
        $studentIds = $request->input('student_ids', []);
        $classId = $request->input('class_id');

        $query = Student::with(['user', 'schoolClass']);

        if (! empty($studentIds)) {
            $query->whereIn('id', (array) $studentIds);
        } elseif (! empty($classId) && $classId !== 'all') {
            $query->where('school_class_id', $classId);
        }

        $students = $query->get();

        foreach ($students as $student) {
            $student->qr_data_uri = $this->qrCodeService->generateDataUri($student->qr_code_identifier, 200, 2);
        }

        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
            'school_address' => 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri',
        ]);

        return view('admin.students.cards.print', [
            'students' => $students,
            'setting' => $setting,
        ]);
    }

    /**
     * Menampilkan dan mencetak kartu satuan untuk satu siswa tertentu.
     */
    public function single(Student $student): View
    {
        $student->load(['user', 'schoolClass']);
        $student->qr_data_uri = $this->qrCodeService->generateDataUri($student->qr_code_identifier, 220, 2);

        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
            'school_address' => 'Jl. Pendidikan No. 45, Kompleks Pelajar Mandiri',
        ]);

        return view('admin.students.cards.single', [
            'student' => $student,
            'setting' => $setting,
        ]);
    }
}
