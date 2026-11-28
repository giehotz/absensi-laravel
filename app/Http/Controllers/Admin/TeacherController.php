<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Supervisi\SupervisiSupervisor;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\ImageUploadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $requestedTab = (string) $request->query('tab', Teacher::STAFF_TYPE_GURU);
        $tab = in_array($requestedTab, [Teacher::STAFF_TYPE_GURU, Teacher::STAFF_TYPE_TU, Teacher::STAFF_TYPE_KEPALA], true)
            ? $requestedTab
            : Teacher::STAFF_TYPE_GURU;
        $search = trim((string) $request->query('search', ''));
        $sort = (string) $request->query('sort', 'name_asc');

        $guruCount = Teacher::where('staff_type', Teacher::STAFF_TYPE_GURU)->count();
        $tuCount = Teacher::where('staff_type', Teacher::STAFF_TYPE_TU)->count();
        $kepalaCount = Teacher::where('staff_type', Teacher::STAFF_TYPE_KEPALA)->count();

        $query = Teacher::with(['user', 'homeroomClasses', 'assignments.subject', 'assignments.schoolClass', 'managedSavingsClasses'])
            ->select('teachers.*')
            ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
            ->byStaffType($tab);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('teachers.nuptk', 'like', "%{$search}%")
                    ->orWhere('teachers.nip', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'name_desc' => $query->orderBy('users.name', 'desc'),
            'latest' => $query->latest('teachers.created_at')->orderBy('teachers.id', 'desc'),
            'oldest' => $query->oldest('teachers.created_at')->orderBy('teachers.id', 'asc'),
            default => $query->orderBy('users.name', 'asc'),
        };

        $teachers = $query->paginate(15)->withQueryString();
        if ($teachers->currentPage() > $teachers->lastPage() && $teachers->lastPage() >= 1) {
            if ($request->session()->has('success') || $request->session()->has('error')) {
                $request->session()->reflash();
            }

            return redirect()->route('admin.teachers.index', array_merge($request->query(), ['page' => $teachers->lastPage()]));
        }

        $subjects = Subject::orderBy('name')->get();
        $classes = SchoolClass::ordered()->get();

        return view('admin.teachers.index', compact(
            'teachers',
            'subjects',
            'classes',
            'search',
            'sort',
            'tab',
            'guruCount',
            'tuCount',
            'kepalaCount'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'staff_type' => ['nullable', 'in:guru,tu,kepala'],
            'nuptk' => ['nullable', 'string', 'max:50', 'unique:teachers,nuptk'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'last_education' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
            'nuptk.unique' => 'NUPTK sudah digunakan oleh guru lain.',
            'nip.unique' => 'NIP sudah digunakan oleh guru lain.',
            'email.unique' => 'Email sudah terdaftar pada sistem.',
        ]);

        $staffType = $validated['staff_type'] ?? Teacher::STAFF_TYPE_GURU;

        DB::transaction(function () use ($validated, $staffType) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => ! empty($validated['email']) ? $validated['email'] : null,
                'password' => Hash::make(! empty($validated['password']) ? $validated['password'] : 'password'),
                'role' => $staffType === Teacher::STAFF_TYPE_KEPALA ? 'kepala' : 'guru',
                'is_active' => true,
            ]);

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'staff_type' => $staffType,
                'nuptk' => ! empty($validated['nuptk']) ? $validated['nuptk'] : null,
                'nip' => ! empty($validated['nip']) ? $validated['nip'] : null,
                'gender' => $validated['gender'],
                'birth_place' => ! empty($validated['birth_place']) ? $validated['birth_place'] : null,
                'birth_date' => ! empty($validated['birth_date']) ? $validated['birth_date'] : null,
                'last_education' => ! empty($validated['last_education']) ? $validated['last_education'] : null,
                'phone' => ! empty($validated['phone']) ? $validated['phone'] : null,
            ]);

            if ($staffType === Teacher::STAFF_TYPE_KEPALA) {
                $this->syncKepalaState($teacher, $staffType);
            }
        });

        $label = match ($staffType) {
            Teacher::STAFF_TYPE_KEPALA => 'Data Kepala Madrasah',
            Teacher::STAFF_TYPE_TU => 'Data Tata Usaha (TU)',
            default => 'Data guru',
        };

        return $this->redirectToTeachersIndex($request, $staffType)->with('success', "{$label} baru berhasil ditambahkan.");
    }

    /**
     * Tampilkan halaman penuh Edit Data Guru.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load([
            'user',
            'homeroomClasses',
            'assignments.subject',
            'assignments.schoolClass',
            'managedSavingsClasses',
        ]);

        $classes = SchoolClass::withCount('students')->ordered()->get();

        return view('admin.teachers.edit', compact('teacher', 'classes'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'staff_type' => ['nullable', 'in:guru,tu,kepala'],
            'nuptk' => ['nullable', 'string', 'max:50', 'unique:teachers,nuptk,'.$teacher->id],
            'nip' => ['nullable', 'string', 'max:50', 'unique:teachers,nip,'.$teacher->id],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'last_education' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$teacher->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
            'is_petugas_mbg' => ['nullable', 'boolean'],
            'is_savings_officer' => ['nullable', 'boolean'],
            'savings_scope' => ['nullable', 'in:all,restricted'],
            'savings_class_ids' => ['nullable', 'array'],
            'savings_class_ids.*' => ['exists:school_classes,id'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'nuptk.unique' => 'NUPTK sudah digunakan oleh guru lain.',
            'nip.unique' => 'NIP sudah digunakan oleh guru lain.',
            'email.unique' => 'Email sudah digunakan.',
            'photo.image' => 'File foto profil harus berupa gambar.',
            'photo.mimes' => 'Format foto yang didukung: JPG, JPEG, PNG, dan WEBP.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $previousStaffType = $teacher->staff_type ?: Teacher::STAFF_TYPE_GURU;
        $staffType = $validated['staff_type'] ?? $previousStaffType;
        $isKepala = $staffType === Teacher::STAFF_TYPE_KEPALA;

        $isSavingsOfficer = ! $isKepala && $request->boolean('is_savings_officer');
        $savingsScope = $isSavingsOfficer ? ($validated['savings_scope'] ?? 'all') : 'all';
        $isPetugasMbg = ! $isKepala && $request->boolean('is_petugas_mbg');

        DB::transaction(function () use ($request, $validated, $teacher, $isSavingsOfficer, $savingsScope, $isPetugasMbg, $staffType, $previousStaffType) {
            $userUpdate = [
                'name' => $validated['name'],
                'email' => ! empty($validated['email']) ? $validated['email'] : null,
                'is_active' => (bool) $validated['is_active'],
                'role' => $staffType === Teacher::STAFF_TYPE_KEPALA ? 'kepala' : 'guru',
            ];

            if (! empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            if ($teacher->user) {
                $teacher->user->update($userUpdate);
            }

            $photoPath = $teacher->photo;
            if ($request->boolean('remove_photo')) {
                $this->imageUploadService->deleteOldFile($teacher->photo);
                $photoPath = null;
            } elseif ($request->hasFile('photo')) {
                $this->imageUploadService->deleteOldFile($teacher->photo);
                $photoPath = $this->imageUploadService->uploadAsWebp(
                    $request->file('photo'),
                    'teachers',
                    82,
                    800,
                    1000
                );
            }

            $teacher->update([
                'staff_type' => $staffType,
                'nuptk' => ! empty($validated['nuptk']) ? $validated['nuptk'] : null,
                'nip' => ! empty($validated['nip']) ? $validated['nip'] : null,
                'gender' => $validated['gender'],
                'birth_place' => ! empty($validated['birth_place']) ? $validated['birth_place'] : null,
                'birth_date' => ! empty($validated['birth_date']) ? $validated['birth_date'] : null,
                'last_education' => ! empty($validated['last_education']) ? $validated['last_education'] : null,
                'phone' => ! empty($validated['phone']) ? $validated['phone'] : null,
                'photo' => $photoPath,
                'is_petugas_mbg' => $isPetugasMbg,
                'is_savings_officer' => $isSavingsOfficer,
                'savings_scope' => $savingsScope,
            ]);

            if ($isSavingsOfficer && $savingsScope === 'restricted') {
                $teacher->managedSavingsClasses()->sync($validated['savings_class_ids'] ?? []);
            } else {
                $teacher->managedSavingsClasses()->sync([]);
            }

            $this->syncKepalaState($teacher->fresh('user'), $staffType, $previousStaffType);
        });

        $label = match ($staffType) {
            Teacher::STAFF_TYPE_KEPALA => 'Data Kepala Madrasah',
            Teacher::STAFF_TYPE_TU => 'Data Tata Usaha (TU)',
            default => 'Data guru',
        };

        return $this->redirectToTeachersIndex($request, $staffType)->with('success', "{$label} berhasil diperbarui.");
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        $user = $teacher->user;
        $teacher->delete();
        if ($user) {
            $user->delete();
        }

        return $this->redirectToTeachersIndex($request)->with('success', 'Data guru berhasil dihapus.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Data Guru');

        // Headers 10 Kolom (kolom J opsional: Guru/TU/Kepala)
        $headers = [
            'A1' => 'No',
            'B1' => 'NUPTK',
            'C1' => 'NIP',
            'D1' => 'NAMA',
            'E1' => 'JENIS KELAMIN',
            'F1' => 'Tempat Lahir',
            'G1' => 'Tgl Lahir (dd-mm-yyyy)',
            'H1' => 'Pendidikan Terakhir',
            'I1' => 'Password',
            'J1' => 'Jenis (Guru/TU/Kepala)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style Headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Data Rows realistis
        $sampleData = [
            [1, '1234567890123456', '198501012010011001', 'Ahmad Dahlan, S.Pd.', 'L', 'Yogyakarta', '01-01-1985', 'S1', 'password', 'Guru'],
            [2, '2345678901234567', '', 'Siti Khadijah, M.Pd.', 'P', 'Surabaya', '02-02-1987', 'S2', 'password', 'Guru'],
            [3, '', '199003032015031003', 'Budi Santoso, S.Kom.', 'L', 'Bandung', '03-03-1990', 'S1', 'password', 'TU'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $row) {
            $sheet->setCellValue('A'.$rowNum, $row[0]);
            $sheet->setCellValueExplicit('B'.$rowNum, (string) $row[1], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) $row[2], DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rowNum, $row[3]);
            $sheet->setCellValue('E'.$rowNum, $row[4]);
            $sheet->setCellValue('F'.$rowNum, $row[5]);
            $sheet->setCellValue('G'.$rowNum, $row[6]);
            $sheet->setCellValue('H'.$rowNum, $row[7]);
            $sheet->setCellValue('I'.$rowNum, $row[8]);
            $sheet->setCellValue('J'.$rowNum, $row[9]);
            $rowNum++;
        }

        // Data rows borders
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A2:J'.($rowNum - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A2:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:J'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-fit columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_guru_9_kolom_'.date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'default_staff_type' => ['nullable', 'in:guru,tu,kepala'],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        $defaultStaffType = $request->input('default_staff_type', Teacher::STAFF_TYPE_GURU);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return $this->redirectToTeachersIndex($request)->with('error', 'File Excel kosong atau tidak memiliki baris data.');
            }

            $importedCount = 0;
            $skipped = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue; // Skip header
                }

                $nuptk = trim((string) ($row['B'] ?? ''));
                $nip = trim((string) ($row['C'] ?? ''));
                $name = trim((string) ($row['D'] ?? ''));
                $rawGender = trim((string) ($row['E'] ?? ''));
                $birthPlace = trim((string) ($row['F'] ?? ''));
                $rawBirthDate = trim((string) ($row['G'] ?? ''));
                $lastEducation = trim((string) ($row['H'] ?? ''));
                $password = trim((string) ($row['I'] ?? ''));
                $rawStaffType = trim((string) ($row['J'] ?? ''));

                // Skip row if completely empty
                if (empty($name) && empty($nuptk) && empty($nip)) {
                    continue;
                }

                // Validation checks
                if (empty($name)) {
                    $skipped[] = "Baris $index: Nama guru kosong.";

                    continue;
                }

                // Check duplicate NUPTK
                if (! empty($nuptk) && Teacher::where('nuptk', $nuptk)->exists()) {
                    $skipped[] = "Baris $index ($name): NUPTK '$nuptk' sudah digunakan.";

                    continue;
                }

                // Check duplicate NIP
                if (! empty($nip) && Teacher::where('nip', $nip)->exists()) {
                    $skipped[] = "Baris $index ($name): NIP '$nip' sudah digunakan.";

                    continue;
                }

                // Normalize Gender
                $gender = $this->normalizeGender($rawGender);

                // Parse Birth Date & Staff Type
                $birthDate = $this->parseDateValue($rawBirthDate);
                $staffType = $this->normalizeStaffType($rawStaffType, $defaultStaffType);

                // Create user (email nullable) and teacher
                DB::transaction(function () use ($name, $nuptk, $nip, $gender, $birthPlace, $birthDate, $lastEducation, $password, $staffType) {
                    $user = User::create([
                        'name' => $name,
                        'email' => null,
                        'password' => Hash::make(! empty($password) ? $password : 'password'),
                        'role' => $staffType === Teacher::STAFF_TYPE_KEPALA ? 'kepala' : 'guru',
                        'is_active' => true,
                    ]);

                    $teacher = Teacher::create([
                        'user_id' => $user->id,
                        'staff_type' => $staffType,
                        'nuptk' => ! empty($nuptk) ? $nuptk : null,
                        'nip' => ! empty($nip) ? $nip : null,
                        'gender' => $gender,
                        'birth_place' => ! empty($birthPlace) ? $birthPlace : null,
                        'birth_date' => $birthDate,
                        'last_education' => ! empty($lastEducation) ? $lastEducation : null,
                    ]);

                    if ($staffType === Teacher::STAFF_TYPE_KEPALA) {
                        $this->syncKepalaState($teacher, $staffType);
                    }
                });

                $importedCount++;
            }

            $message = "Berhasil mengimpor $importedCount data guru.";
            if (! empty($skipped)) {
                $message .= ' '.count($skipped).' data dilewati: '.implode(', ', array_slice($skipped, 0, 3));
                if (count($skipped) > 3) {
                    $message .= ' dan '.(count($skipped) - 3).' baris lainnya.';
                }
            }

            if ($importedCount === 0 && ! empty($skipped)) {
                return $this->redirectToTeachersIndex($request)->with('error', 'Tidak ada data guru yang berhasil diimpor. '.implode(', ', array_slice($skipped, 0, 3)));
            }

            return $this->redirectToTeachersIndex($request)->with('success', $message);
        } catch (\Throwable $e) {
            return $this->redirectToTeachersIndex($request)->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }

    /**
     * Parse nilai tanggal dari format string atau serial number Excel.
     */
    protected function parseDateValue(?string $rawDate): ?string
    {
        if (empty($rawDate)) {
            return null;
        }

        $trimmed = trim($rawDate);

        // Jika berupa nilai serial numerik Excel (misal 31234)
        if (is_numeric($trimmed) && (float) $trimmed > 1000) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $trimmed))->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'j-n-Y', 'j/n/Y'];
        foreach ($formats as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $trimmed);
                if ($date !== false) {
                    return $date->format('Y-m-d');
                }
            } catch (\Throwable) {
            }
        }

        $timestamp = strtotime($trimmed);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Normalisasi nilai gender menjadi L atau P.
     */
    protected function normalizeGender(?string $rawGender): string
    {
        if (empty($rawGender)) {
            return 'L';
        }

        $upper = strtoupper(trim($rawGender));
        if (str_starts_with($upper, 'P') || str_contains($upper, 'PEREMPUAN') || str_contains($upper, 'WANITA')) {
            return 'P';
        }

        return 'L';
    }

    /**
     * Normalisasi kategori PTK menjadi 'guru', 'tu', atau 'kepala'.
     */
    protected function normalizeStaffType(?string $rawType, string $fallback = Teacher::STAFF_TYPE_GURU): string
    {
        $normalized = strtolower(trim((string) $rawType));
        $validFallback = in_array($fallback, [Teacher::STAFF_TYPE_GURU, Teacher::STAFF_TYPE_TU, Teacher::STAFF_TYPE_KEPALA], true)
            ? $fallback
            : Teacher::STAFF_TYPE_GURU;

        if ($normalized === '') {
            return $validFallback;
        }

        if (str_contains($normalized, 'kepala') || $normalized === 'kamad' || $normalized === 'kepsek') {
            return Teacher::STAFF_TYPE_KEPALA;
        }

        if (str_contains($normalized, 'tu') || str_contains($normalized, 'tata usaha') || str_contains($normalized, 'tendik')) {
            return Teacher::STAFF_TYPE_TU;
        }

        if (str_contains($normalized, 'guru') || str_contains($normalized, 'pendidik')) {
            return Teacher::STAFF_TYPE_GURU;
        }

        return $validFallback;
    }

    /**
     * Ambil data penugasan mengajar guru untuk form modal (JSON).
     */
    public function getAssignments(Teacher $teacher)
    {
        $teacher->load(['user', 'homeroomClasses', 'assignments.subject', 'assignments.schoolClass']);

        // Kelompokkan kelas per mapel terlebih dahulu
        $subjectClasses = [];
        $subjectNames = [];
        foreach ($teacher->assignments as $assignment) {
            $subId = $assignment->subject_id;
            $classId = $assignment->school_class_id;
            $subjectClasses[$subId][] = $classId;
            if (! isset($subjectNames[$subId])) {
                $subjectNames[$subId] = $assignment->subject?->name ?? 'Mapel #'.$subId;
            }
        }

        // Kelompokkan mapel yang memiliki himpunan rombel kelas persis sama ke satu kartu
        $groupedByClasses = [];
        foreach ($subjectClasses as $subId => $classIds) {
            sort($classIds);
            $signature = implode(',', $classIds);
            if (! isset($groupedByClasses[$signature])) {
                $groupedByClasses[$signature] = [
                    'subject_id' => $subId,
                    'subject_ids' => [],
                    'subject_names' => [],
                    'class_ids' => $classIds,
                ];
            }
            $groupedByClasses[$signature]['subject_ids'][] = $subId;
            $groupedByClasses[$signature]['subject_names'][] = $subjectNames[$subId];
        }

        return response()->json([
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->user?->name ?? '-',
                'nip' => $teacher->nip ?? '-',
                'nuptk' => $teacher->nuptk ?? '-',
                'is_homeroom' => $teacher->homeroomClasses->isNotEmpty(),
                'homeroom_classes' => $teacher->homeroomClasses->pluck('name')->all(),
                'homeroom_class_ids' => $teacher->homeroomClasses->pluck('id')->all(),
                'homeroom_class_id' => $teacher->homeroomClasses->first()?->id,
            ],
            'has_restrictions' => $teacher->assignments->isNotEmpty(),
            'assignments' => array_values($groupedByClasses),
        ]);
    }

    /**
     * Simpan pengaturan penugasan mengajar guru (Mata Pelajaran & Rombel Kelas).
     */
    public function updateAssignments(Request $request, Teacher $teacher): RedirectResponse
    {
        if ($teacher->isKepala()) {
            return $this->redirectToTeachersIndex($request)
                ->with('error', 'Kepala Madrasah tidak memiliki penugasan mengajar atau wali kelas.');
        }
        $validated = $request->validate([
            'mode' => ['required', 'in:unrestricted,restricted'],
            'homeroom_class_id' => ['nullable', 'exists:school_classes,id'],
            'has_homeroom_update' => ['nullable'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.subject_id' => ['nullable', 'exists:subjects,id'],
            'assignments.*.subject_ids' => ['nullable', 'array'],
            'assignments.*.subject_ids.*' => ['exists:subjects,id'],
            'assignments.*.class_ids' => ['required_if:mode,restricted', 'array', 'min:1'],
            'assignments.*.class_ids.*' => ['exists:school_classes,id'],
        ], [
            'assignments.*.class_ids.required_if' => 'Minimal 1 kelas harus dipilih untuk setiap baris penugasan.',
        ]);

        if ($validated['mode'] === 'restricted' && ! empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $idx => $row) {
                $hasSingle = ! empty($row['subject_id']);
                $hasMulti = ! empty($row['subject_ids']) && is_array($row['subject_ids']) && count($row['subject_ids']) > 0;
                if (! $hasSingle && ! $hasMulti) {
                    throw ValidationException::withMessages([
                        "assignments.{$idx}.subject_ids" => 'Minimal 1 mata pelajaran harus dipilih untuk setiap baris penugasan.',
                    ]);
                }
            }
        }

        DB::transaction(function () use ($teacher, $validated, $request) {
            // Perbarui penetapan wali kelas jika form menyertakan parameter has_homeroom_update
            if ($request->has('has_homeroom_update')) {
                SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);

                if (! empty($validated['homeroom_class_id'])) {
                    SchoolClass::where('id', $validated['homeroom_class_id'])->update(['homeroom_teacher_id' => $teacher->id]);
                }
            }

            $teacher->assignments()->delete();

            if ($validated['mode'] === 'restricted' && ! empty($validated['assignments'])) {
                foreach ($validated['assignments'] as $row) {
                    $subjectIds = [];
                    if (! empty($row['subject_ids']) && is_array($row['subject_ids'])) {
                        $subjectIds = array_map('intval', $row['subject_ids']);
                    }
                    if (! empty($row['subject_id'])) {
                        $subjectIds[] = (int) $row['subject_id'];
                    }
                    $subjectIds = array_unique(array_filter($subjectIds));
                    $classIds = array_unique(array_filter(array_map('intval', $row['class_ids'] ?? [])));

                    foreach ($subjectIds as $subjectId) {
                        foreach ($classIds as $classId) {
                            TeacherAssignment::firstOrCreate([
                                'teacher_id' => $teacher->id,
                                'subject_id' => $subjectId,
                                'school_class_id' => $classId,
                            ]);
                        }
                    }
                }
            }
        });

        return $this->redirectToTeachersIndex($request)
            ->with('success', 'Pengaturan penugasan mengajar untuk guru '.$teacher->user?->name.' berhasil disimpan.');
    }

    /**
     * Tunjuk atau lepas jabatan Guru sebagai Pengelola Tabungan Siswa (Quick toggle).
     */
    public function toggleSavingsOfficer(Teacher $teacher): RedirectResponse
    {
        if ($teacher->isKepala()) {
            return back()->with('error', 'Kepala Madrasah tidak dapat ditugaskan sebagai Pengelola Tabungan Siswa.');
        }
        $newState = ! (bool) $teacher->is_savings_officer;
        $teacher->update([
            'is_savings_officer' => $newState,
            'savings_scope' => $newState ? ($teacher->savings_scope ?: 'all') : 'all',
        ]);

        $teacherName = $teacher->user?->name ?? 'Guru';
        $message = $newState
            ? "Guru {$teacherName} berhasil ditunjuk sebagai Pengelola Tabungan Siswa."
            : "Status Pengelola Tabungan Siswa untuk {$teacherName} berhasil dinonaktifkan.";

        return back()->with('success', $message);
    }

    /**
     * Simpan wewenang & penugasan kelas pengelola tabungan siswa.
     */
    public function updateSavingsAssignment(Request $request, Teacher $teacher): RedirectResponse
    {
        if ($teacher->isKepala()) {
            return $this->redirectToTeachersIndex($request)
                ->with('error', 'Kepala Madrasah tidak dapat ditugaskan sebagai Pengelola Tabungan Siswa.');
        }
        $validated = $request->validate([
            'is_savings_officer' => ['required', 'boolean'],
            'savings_scope' => ['required_if:is_savings_officer,1', 'in:all,restricted'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
        ], [
            'savings_scope.required_if' => 'Pilih cakupan wewenang kelas (Semua Kelas atau Pilihan Kelas).',
        ]);

        $isOfficer = (bool) $validated['is_savings_officer'];
        $scope = $isOfficer ? ($validated['savings_scope'] ?? 'all') : 'all';

        DB::transaction(function () use ($teacher, $isOfficer, $scope, $validated) {
            $teacher->update([
                'is_savings_officer' => $isOfficer,
                'savings_scope' => $scope,
            ]);

            if ($isOfficer && $scope === 'restricted') {
                $teacher->managedSavingsClasses()->sync($validated['class_ids'] ?? []);
            } else {
                $teacher->managedSavingsClasses()->sync([]);
            }
        });

        $teacherName = $teacher->user?->name ?? 'Guru';
        $message = $isOfficer
            ? "Wewenang Pengelola Tabungan untuk {$teacherName} berhasil disimpan."
            : "Status Pengelola Tabungan untuk {$teacherName} berhasil dinonaktifkan.";

        return $this->redirectToTeachersIndex($request)->with('success', $message);
    }

    /**
     * Mengambil parameter query tab/halaman/pencarian/urutan yang aman dari input return_query.
     *
     * @return array<string, string>
     */
    protected function extractReturnQueryParams(Request $request): array
    {
        $rawQuery = ltrim(trim((string) $request->input('return_query', '')), '?');
        if ($rawQuery === '') {
            return [];
        }

        parse_str($rawQuery, $parsed);
        $allowed = [];

        foreach (['tab', 'page', 'search', 'sort'] as $key) {
            if (isset($parsed[$key]) && is_scalar($parsed[$key]) && (string) $parsed[$key] !== '') {
                $allowed[$key] = (string) $parsed[$key];
            }
        }

        $validTabs = [Teacher::STAFF_TYPE_GURU, Teacher::STAFF_TYPE_TU, Teacher::STAFF_TYPE_KEPALA];
        if (isset($allowed['tab']) && ! in_array($allowed['tab'], $validTabs, true)) {
            unset($allowed['tab']);
        }

        return $allowed;
    }

    /**
     * Mengalihkan kembali ke daftar guru/TU/kepala dengan parameter tab, halaman, pencarian, dan urutan yang dipilih.
     */
    protected function redirectToTeachersIndex(Request $request, ?string $targetTab = null): RedirectResponse
    {
        $validTabs = [Teacher::STAFF_TYPE_GURU, Teacher::STAFF_TYPE_TU, Teacher::STAFF_TYPE_KEPALA];
        $params = $this->extractReturnQueryParams($request);
        if (empty($params)) {
            $refererQuery = parse_url((string) $request->headers->get('referer', ''), PHP_URL_QUERY);
            if (is_string($refererQuery) && $refererQuery !== '') {
                parse_str($refererQuery, $parsed);
                foreach (['tab', 'page', 'search', 'sort'] as $key) {
                    if (isset($parsed[$key]) && is_scalar($parsed[$key]) && (string) $parsed[$key] !== '') {
                        $params[$key] = (string) $parsed[$key];
                    }
                }
            }
        }

        if (isset($params['tab']) && ! in_array($params['tab'], $validTabs, true)) {
            unset($params['tab']);
        }

        if ($targetTab !== null && in_array($targetTab, $validTabs, true)) {
            $currentTab = $params['tab'] ?? Teacher::STAFF_TYPE_GURU;
            if ($currentTab !== $targetTab) {
                $params['tab'] = $targetTab;
                unset($params['page']);
            }
        }

        return redirect()->route('admin.teachers.index', $params);
    }

    /**
     * Menyinkronkan tugas tambahan & pengaturan Kepala Madrasah ketika staff_type menjadi/keluar dari 'kepala'.
     */
    protected function syncKepalaState(Teacher $teacher, string $staffType, ?string $previousStaffType = null): void
    {
        $setting = AttendanceSetting::first();

        if ($staffType === Teacher::STAFF_TYPE_KEPALA) {
            $teacher->assignments()->delete();
            SchoolClass::where('homeroom_teacher_id', $teacher->id)->update(['homeroom_teacher_id' => null]);
            $teacher->managedSavingsClasses()->sync([]);

            if ($setting) {
                $setting->headmaster_teacher_id = $teacher->id;
                $setting->card_principal_name = $teacher->user?->name ?? $setting->card_principal_name;
                $setting->card_principal_nip = $teacher->nip ?? $setting->card_principal_nip;
                $setting->save();
            }

            SupervisiSupervisor::syncKepala($teacher);

            return;
        }

        if ($previousStaffType === Teacher::STAFF_TYPE_KEPALA && $setting && (int) $setting->headmaster_teacher_id === (int) $teacher->id) {
            $setting->update(['headmaster_teacher_id' => null]);
        }

        if ($previousStaffType === Teacher::STAFF_TYPE_KEPALA) {
            SupervisiSupervisor::deactivateExKepala((int) $teacher->id);
        }
    }
}
