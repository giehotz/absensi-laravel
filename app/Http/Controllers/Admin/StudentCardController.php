<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\ImageUploadService;
use App\Services\StudentCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCardController extends Controller
{
    public function __construct(
        protected StudentCardService $studentCardService,
        protected ImageUploadService $imageUploadService
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

        foreach ($students as $student) {
            $this->studentCardService->prepareStudent($student);
        }

        $setting = $this->studentCardService->getSetting();

        return view('admin.students.cards.index', [
            'students' => $students,
            'classes' => $classes,
            'selectedClassId' => $selectedClassId,
            'search' => $search,
            'setting' => $setting,
        ]);
    }

    /**
     * Menampilkan halaman studio pengaturan desain kartu tanda pelajar.
     */
    public function settings(): View
    {
        $setting = $this->studentCardService->getSetting();

        // Ambil 1 contoh siswa untuk live preview kartu
        $sampleStudent = Student::with(['user', 'schoolClass'])->first();

        if (! $sampleStudent) {
            $sampleStudent = new Student([
                'nis' => '111118060002191095',
                'nisn' => '3122517325',
                'qr_code_identifier' => 'STD-2026-0001',
                'gender' => 'P',
                'birth_place' => 'SUKARAJA',
                'birth_date' => now()->subYears(12),
                'address' => 'Gerobang Mandi, Gisting Permai',
            ]);
            $sampleStudent->setRelation('user', new User(['name' => 'BAZLA BATRISYIA ISMAIL']));
            $sampleStudent->setRelation('schoolClass', new SchoolClass(['name' => 'Kelas 3B', 'level' => '3']));
        }

        $sampleStudent = $this->studentCardService->prepareStudent($sampleStudent);

        return view('admin.students.cards.settings', compact('setting', 'sampleStudent'));
    }

    /**
     * Memperbarui konfigurasi desain kartu tanda pelajar.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_school_name' => 'nullable|string|max:255',
            'card_title' => 'required|string|max:100',
            'card_validity_text' => 'required|string|max:100',
            'card_back_instructions' => 'nullable|string|max:500',
            'card_width_cm' => 'required|numeric|min:5|max:20',
            'card_height_cm' => 'required|numeric|min:3|max:15',
            'card_theme_color' => 'required|string|max:20',
            'card_show_back_token' => 'nullable',
            'card_show_signature' => 'nullable',
            'card_principal_name' => 'nullable|string|max:255',
            'card_principal_nip' => 'nullable|string|max:50',
            'card_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'card_signature_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $setting = AttendanceSetting::firstOrCreate([]);

        if ($request->hasFile('card_logo')) {
            $this->imageUploadService->deleteOldFile($setting->card_logo);
            $validated['card_logo'] = $this->imageUploadService->uploadAsWebp(
                $request->file('card_logo'),
                'card_assets',
                85,
                600,
                600
            );
        }

        if ($request->hasFile('card_signature_image')) {
            $this->imageUploadService->deleteOldFile($setting->card_signature_image);
            $validated['card_signature_image'] = $this->imageUploadService->uploadAsWebp(
                $request->file('card_signature_image'),
                'card_assets',
                85,
                600,
                400
            );
        }

        $validated['card_show_back_token'] = $request->boolean('card_show_back_token');
        $validated['card_show_signature'] = $request->boolean('card_show_signature');

        $setting->update($validated);

        return redirect()->route('admin.students.cards.settings')
            ->with('success', 'Pengaturan desain kartu tanda pelajar berhasil diperbarui!');
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
            $this->studentCardService->prepareStudent($student);
        }

        $setting = $this->studentCardService->getSetting();

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
        $student = $this->studentCardService->prepareStudent($student);
        $setting = $this->studentCardService->getSetting();

        return view('admin.students.cards.single', [
            'student' => $student,
            'setting' => $setting,
        ]);
    }
}
