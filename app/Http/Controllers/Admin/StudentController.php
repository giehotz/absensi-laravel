<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\ImageUploadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

class StudentController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function index(Request $request): View
    {
        $selectedClassId = $request->query('class_id');
        $perPage = $request->query('per_page', '25');

        $totalStudentsCount = Student::count();

        $selectedClass = null;
        $classStudentsCount = null;
        if (! empty($selectedClassId)) {
            $selectedClass = SchoolClass::find($selectedClassId);
            if ($selectedClass) {
                $classStudentsCount = Student::where('school_class_id', $selectedClassId)->count();
            }
        }

        $query = Student::with(['user', 'schoolClass'])->latest();

        if (! empty($selectedClassId)) {
            $query->where('school_class_id', $selectedClassId);
        }

        if ($perPage === 'all' || $perPage === 'semua') {
            $perPageNum = max($totalStudentsCount, 1);
        } else {
            $perPageNum = in_array((int) $perPage, [25, 50, 100], true) ? (int) $perPage : 25;
        }

        $students = $query->paginate($perPageNum)->withQueryString();
        $classes = SchoolClass::orderBy('name')->get();
        $setting = AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
            'npsn' => '20102030',
            'level' => 'SMP',
        ]);

        return view('admin.students.index', compact(
            'students',
            'classes',
            'selectedClassId',
            'selectedClass',
            'totalStudentsCount',
            'classStudentsCount',
            'perPage',
            'setting'
        ));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $headers = [
            'A1' => 'No',
            'B1' => 'Nis Lokal',
            'C1' => 'NISN',
            'D1' => 'NAMA',
            'E1' => 'JENIS KELAMIN',
            'F1' => 'Tempat Lahir',
            'G1' => 'Tgl Lahir (dd-mm-yyyy)',
            'H1' => 'Password',
            'I1' => 'Alamat Siswa',
            'J1' => 'Agama',
            'K1' => 'Status Keluarga',
            'L1' => 'Anak Ke',
            'M1' => 'Nomor HP',
            'N1' => 'Sekolah Asal',
            'O1' => 'Tgl Terima',
            'P1' => 'Tingkat Awal',
            'Q1' => 'Nama Ayah',
            'R1' => 'Nama Ibu',
            'S1' => 'Pekerjaan Ayah',
            'T1' => 'Pekerjaan Ibu',
            'U1' => 'Alamat Orang Tua',
            'V1' => 'Nama Wali',
            'W1' => 'Pekerjaan Wali',
            'X1' => 'Alamat Wali',
            'Y1' => 'Kelas (Opsional)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

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
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:Y1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Sample Data Rows
        $sampleRows = [
            [
                1, '1001', '0081234561', 'Muhammad Rizki', 'L', 'Jakarta', '15-05-2010', '1001',
                'Jl. Merdeka No. 10, Jakarta', 'Islam', 'Anak Kandung', '1', '081234567891',
                'SD Negeri 01 Pagi', '15-07-2023', 'VII', 'Bambang Sudarsono', 'Siti Aminah',
                'PNS / Guru', 'Ibu Rumah Tangga', 'Jl. Merdeka No. 10, Jakarta', '', '', '', 'VII-A',
            ],
            [
                2, '1002', '0081234562', 'Nur Aini Putri', 'P', 'Bandung', '20-08-2010', '1002',
                'Jl. Asia Afrika No. 25, Bandung', 'Islam', 'Anak Kandung', '2', '081234567892',
                'SD Negeri 05 Bandung', '15-07-2023', 'VII', 'Ahmad Hidayat', 'Dewi Sartika',
                'Wiraswasta', 'Karyawan Swasta', 'Jl. Asia Afrika No. 25, Bandung', '', '', '', 'VII-A',
            ],
        ];

        $rowNum = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue('A'.$rowNum, $row[0]);
            $sheet->setCellValueExplicit('B'.$rowNum, (string) $row[1], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) $row[2], DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rowNum, $row[3]);
            $sheet->setCellValue('E'.$rowNum, $row[4]);
            $sheet->setCellValue('F'.$rowNum, $row[5]);
            $sheet->setCellValueExplicit('G'.$rowNum, (string) $row[6], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('H'.$rowNum, (string) $row[7], DataType::TYPE_STRING);
            $sheet->setCellValue('I'.$rowNum, $row[8]);
            $sheet->setCellValue('J'.$rowNum, $row[9]);
            $sheet->setCellValue('K'.$rowNum, $row[10]);
            $sheet->setCellValueExplicit('L'.$rowNum, (string) $row[11], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('M'.$rowNum, (string) $row[12], DataType::TYPE_STRING);
            $sheet->setCellValue('N'.$rowNum, $row[13]);
            $sheet->setCellValueExplicit('O'.$rowNum, (string) $row[14], DataType::TYPE_STRING);
            $sheet->setCellValue('P'.$rowNum, $row[15]);
            $sheet->setCellValue('Q'.$rowNum, $row[16]);
            $sheet->setCellValue('R'.$rowNum, $row[17]);
            $sheet->setCellValue('S'.$rowNum, $row[18]);
            $sheet->setCellValue('T'.$rowNum, $row[19]);
            $sheet->setCellValue('U'.$rowNum, $row[20]);
            $sheet->setCellValue('V'.$rowNum, $row[21]);
            $sheet->setCellValue('W'.$rowNum, $row[22]);
            $sheet->setCellValue('X'.$rowNum, $row[23]);
            $sheet->setCellValue('Y'.$rowNum, $row[24]);
            $rowNum++;
        }

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
        $sheet->getStyle('A2:Y'.($rowNum - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A2:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_siswa_lengkap_'.date('Ymd_His').'.xlsx';

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
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
            'school_class_id.exists' => 'Kelas yang dipilih tidak valid.',
        ]);

        $defaultClassId = $request->input('school_class_id');
        $allowUpsert = $request->boolean('upsert', true);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return redirect()->route('admin.students.index')->with('error', 'File Excel kosong atau tidak memiliki data baris siswa.');
            }

            $importedCount = 0;
            $updatedCount = 0;
            $skipped = [];

            $classesCache = SchoolClass::all()->keyBy('name');

            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue; // Skip header
                }

                $nis = trim((string) ($row['B'] ?? ''));
                $nisn = trim((string) ($row['C'] ?? ''));
                $name = trim((string) ($row['D'] ?? ''));
                $genderRaw = strtoupper(trim((string) ($row['E'] ?? 'L')));
                $gender = in_array($genderRaw, ['L', 'P']) ? $genderRaw : 'L';
                $birthPlace = trim((string) ($row['F'] ?? ''));
                $birthDateRaw = trim((string) ($row['G'] ?? ''));
                $passwordRaw = trim((string) ($row['H'] ?? ''));
                $address = trim((string) ($row['I'] ?? ''));
                $religion = trim((string) ($row['J'] ?? ''));
                $familyStatus = trim((string) ($row['K'] ?? ''));
                $childNumber = trim((string) ($row['L'] ?? ''));
                $phone = trim((string) ($row['M'] ?? ''));
                $previousSchool = trim((string) ($row['N'] ?? ''));
                $admissionDateRaw = trim((string) ($row['O'] ?? ''));
                $entryGrade = trim((string) ($row['P'] ?? ''));
                $fatherName = trim((string) ($row['Q'] ?? ''));
                $motherName = trim((string) ($row['R'] ?? ''));
                $fatherJob = trim((string) ($row['S'] ?? ''));
                $motherJob = trim((string) ($row['T'] ?? ''));
                $parentAddress = trim((string) ($row['U'] ?? ''));
                $guardianName = trim((string) ($row['V'] ?? ''));
                $guardianJob = trim((string) ($row['W'] ?? ''));
                $guardianAddress = trim((string) ($row['X'] ?? ''));
                $className = trim((string) ($row['Y'] ?? ''));

                // Skip if completely empty row
                if (empty($name) && empty($nis)) {
                    continue;
                }

                if (empty($name)) {
                    $skipped[] = "Baris $index: Nama siswa kosong.";

                    continue;
                }

                if (empty($nis)) {
                    $skipped[] = "Baris $index ($name): NIS kosong.";

                    continue;
                }

                // Determine target class
                $targetClassId = $defaultClassId;
                if (! empty($className)) {
                    if (isset($classesCache[$className])) {
                        $targetClassId = $classesCache[$className]->id;
                    } else {
                        $foundClass = SchoolClass::whereRaw('LOWER(name) = ?', [strtolower($className)])->first();
                        if ($foundClass) {
                            $targetClassId = $foundClass->id;
                            $classesCache[$className] = $foundClass;
                        }
                    }
                }

                if (empty($targetClassId)) {
                    $skipped[] = "Baris $index ($name): Kelas belum ditentukan atau tidak ditemukan.";

                    continue;
                }

                // Parse dates
                $birthDate = $this->parseDate($birthDateRaw);
                $admissionDate = $this->parseDate($admissionDateRaw);

                // Password logic: custom password or NIS fallback
                $plainPassword = ! empty($passwordRaw) ? $passwordRaw : $nis;

                // Check duplicate NIS
                $existingStudent = Student::with('user')->where('nis', $nis)->first();

                if ($existingStudent) {
                    if (! $allowUpsert) {
                        $skipped[] = "Baris $index ($name): NIS '$nis' sudah terdaftar.";

                        continue;
                    }

                    // Upsert existing student
                    DB::transaction(function () use (
                        $existingStudent, $name, $passwordRaw, $plainPassword, $targetClassId,
                        $nisn, $gender, $birthPlace, $birthDate, $phone, $address, $religion,
                        $familyStatus, $childNumber, $previousSchool, $admissionDate, $entryGrade,
                        $fatherName, $motherName, $fatherJob, $motherJob, $parentAddress,
                        $guardianName, $guardianJob, $guardianAddress
                    ) {
                        $userUpdate = ['name' => $name];
                        if (! empty($passwordRaw)) {
                            $userUpdate['password'] = Hash::make($plainPassword);
                        }
                        $existingStudent->user()->update($userUpdate);

                        $existingStudent->update([
                            'school_class_id' => $targetClassId,
                            'nisn' => ! empty($nisn) ? $nisn : $existingStudent->nisn,
                            'gender' => $gender,
                            'birth_place' => ! empty($birthPlace) ? $birthPlace : $existingStudent->birth_place,
                            'birth_date' => $birthDate ?? $existingStudent->birth_date,
                            'phone' => ! empty($phone) ? $phone : $existingStudent->phone,
                            'address' => ! empty($address) ? $address : $existingStudent->address,
                            'religion' => ! empty($religion) ? $religion : $existingStudent->religion,
                            'family_status' => ! empty($familyStatus) ? $familyStatus : $existingStudent->family_status,
                            'child_number' => ! empty($childNumber) ? $childNumber : $existingStudent->child_number,
                            'previous_school' => ! empty($previousSchool) ? $previousSchool : $existingStudent->previous_school,
                            'admission_date' => $admissionDate ?? $existingStudent->admission_date,
                            'entry_grade' => ! empty($entryGrade) ? $entryGrade : $existingStudent->entry_grade,
                            'father_name' => ! empty($fatherName) ? $fatherName : $existingStudent->father_name,
                            'mother_name' => ! empty($motherName) ? $motherName : $existingStudent->mother_name,
                            'father_job' => ! empty($fatherJob) ? $fatherJob : $existingStudent->father_job,
                            'mother_job' => ! empty($motherJob) ? $motherJob : $existingStudent->mother_job,
                            'parent_address' => ! empty($parentAddress) ? $parentAddress : $existingStudent->parent_address,
                            'guardian_name' => ! empty($guardianName) ? $guardianName : $existingStudent->guardian_name,
                            'guardian_job' => ! empty($guardianJob) ? $guardianJob : $existingStudent->guardian_job,
                            'guardian_address' => ! empty($guardianAddress) ? $guardianAddress : $existingStudent->guardian_address,
                        ]);
                    });

                    $updatedCount++;

                    continue;
                }

                // New student: build unique email
                $email = $nis.'@siswa.sekolah.sch.id';
                if (User::where('email', $email)->exists()) {
                    $email = $nis.'.'.rand(100, 999).'@siswa.sekolah.sch.id';
                }

                $qrCodeIdentifier = 'QR-'.$nis.'-'.strtoupper(bin2hex(random_bytes(3)));

                DB::transaction(function () use (
                    $name, $email, $plainPassword, $targetClassId, $nis, $nisn,
                    $qrCodeIdentifier, $gender, $birthPlace, $birthDate, $phone,
                    $address, $religion, $familyStatus, $childNumber, $previousSchool,
                    $admissionDate, $entryGrade, $fatherName, $motherName, $fatherJob,
                    $motherJob, $parentAddress, $guardianName, $guardianJob, $guardianAddress
                ) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => 'siswa',
                        'is_active' => true,
                    ]);

                    Student::create([
                        'user_id' => $user->id,
                        'school_class_id' => $targetClassId,
                        'nis' => $nis,
                        'nisn' => ! empty($nisn) ? $nisn : null,
                        'qr_code_identifier' => $qrCodeIdentifier,
                        'gender' => $gender,
                        'birth_place' => ! empty($birthPlace) ? $birthPlace : null,
                        'birth_date' => $birthDate,
                        'phone' => ! empty($phone) ? $phone : null,
                        'address' => ! empty($address) ? $address : null,
                        'religion' => ! empty($religion) ? $religion : null,
                        'family_status' => ! empty($familyStatus) ? $familyStatus : null,
                        'child_number' => ! empty($childNumber) ? $childNumber : null,
                        'previous_school' => ! empty($previousSchool) ? $previousSchool : null,
                        'admission_date' => $admissionDate,
                        'entry_grade' => ! empty($entryGrade) ? $entryGrade : null,
                        'father_name' => ! empty($fatherName) ? $fatherName : null,
                        'mother_name' => ! empty($motherName) ? $motherName : null,
                        'father_job' => ! empty($fatherJob) ? $fatherJob : null,
                        'mother_job' => ! empty($motherJob) ? $motherJob : null,
                        'parent_address' => ! empty($parentAddress) ? $parentAddress : null,
                        'guardian_name' => ! empty($guardianName) ? $guardianName : null,
                        'guardian_job' => ! empty($guardianJob) ? $guardianJob : null,
                        'guardian_address' => ! empty($guardianAddress) ? $guardianAddress : null,
                    ]);
                });

                $importedCount++;
            }

            $message = "Proses impor selesai. $importedCount siswa baru ditambahkan";
            if ($updatedCount > 0) {
                $message .= ", $updatedCount siswa diperbarui";
            }
            $message .= '.';

            if (! empty($skipped)) {
                $message .= ' '.count($skipped).' data dilewati: '.implode(', ', array_slice($skipped, 0, 3));
                if (count($skipped) > 3) {
                    $message .= ' dan '.(count($skipped) - 3).' baris lainnya.';
                }
            }

            if ($importedCount === 0 && $updatedCount === 0 && ! empty($skipped)) {
                return redirect()->route('admin.students.index')->with('error', 'Tidak ada data siswa yang berhasil diproses. '.implode(', ', array_slice($skipped, 0, 3)));
            }

            return redirect()->route('admin.students.index')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.students.index')->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }

    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        $trimmed = trim((string) $value);
        if (empty($trimmed)) {
            return null;
        }

        if (is_numeric($trimmed)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $trimmed)->format('Y-m-d');
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'address' => ['nullable', 'string'],
            'religion' => ['nullable', 'string', 'max:50'],
            'family_status' => ['nullable', 'string', 'max:50'],
            'child_number' => ['nullable', 'string', 'max:10'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'admission_date' => ['nullable', 'date'],
            'entry_grade' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'father_job' => ['nullable', 'string', 'max:100'],
            'mother_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_job' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan oleh siswa lain.',
            'school_class_id.required' => 'Pilih kelas siswa.',
            'gender.required' => 'Pilih jenis kelamin.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        // Email default otomatis dari NIS jika tidak diinput
        $email = $validated['email'] ?? ($validated['nis'].'@siswa.sekolah.sch.id');

        // Pastikan email unik jika fallback otomatis
        if (User::where('email', $email)->exists()) {
            $email = $validated['nis'].'.'.rand(100, 999).'@siswa.sekolah.sch.id';
        }

        // Generate QR code identifier permanen
        $qrCodeIdentifier = 'QR-'.$validated['nis'].'-'.strtoupper(bin2hex(random_bytes(3)));

        // Handle upload foto jika ada (otomatis dikonversi ke WebP)
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->imageUploadService->uploadAsWebp(
                $request->file('photo'),
                'students/photos',
                82,
                800,
                1000
            );
        }

        DB::transaction(function () use ($validated, $email, $qrCodeIdentifier, $photoPath) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'is_active' => true,
            ]);

            Student::create([
                'user_id' => $user->id,
                'school_class_id' => $validated['school_class_id'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'qr_code_identifier' => $qrCodeIdentifier,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'photo' => $photoPath,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'family_status' => $validated['family_status'] ?? null,
                'child_number' => $validated['child_number'] ?? null,
                'previous_school' => $validated['previous_school'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'entry_grade' => $validated['entry_grade'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'father_job' => $validated['father_job'] ?? null,
                'mother_job' => $validated['mother_job'] ?? null,
                'parent_address' => $validated['parent_address'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_job' => $validated['guardian_job'] ?? null,
                'guardian_address' => $validated['guardian_address'] ?? null,
            ]);
        });

        if ($request->filled('redirect_to')) {
            return redirect($request->input('redirect_to'))->with('success', 'Data siswa berhasil ditambahkan ke kelas.');
        }

        return redirect()->route('admin.students.index')->with('success', 'Data siswa dan kartu QR Code berhasil dibuat.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis,'.$student->id],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn,'.$student->id],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$student->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'address' => ['nullable', 'string'],
            'religion' => ['nullable', 'string', 'max:50'],
            'family_status' => ['nullable', 'string', 'max:50'],
            'child_number' => ['nullable', 'string', 'max:10'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'admission_date' => ['nullable', 'date'],
            'entry_grade' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'father_job' => ['nullable', 'string', 'max:100'],
            'mother_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_job' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'school_class_id.required' => 'Pilih kelas siswa.',
            'gender.required' => 'Pilih jenis kelamin.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        $photoPath = $student->photo;

        if ($request->hasFile('photo')) {
            $this->imageUploadService->deleteOldFile($student->photo);
            $photoPath = $this->imageUploadService->uploadAsWebp(
                $request->file('photo'),
                'students/photos',
                82,
                800,
                1000
            );
        } elseif ($request->boolean('remove_photo')) {
            $this->imageUploadService->deleteOldFile($student->photo);
            $photoPath = null;
        }

        DB::transaction(function () use ($validated, $student, $photoPath) {
            $userUpdate = ['name' => $validated['name']];
            if (! empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            if (! empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }
            $student->user()->update($userUpdate);

            $student->update([
                'school_class_id' => $validated['school_class_id'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'photo' => $photoPath,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'family_status' => $validated['family_status'] ?? null,
                'child_number' => $validated['child_number'] ?? null,
                'previous_school' => $validated['previous_school'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'entry_grade' => $validated['entry_grade'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'father_job' => $validated['father_job'] ?? null,
                'mother_job' => $validated['mother_job'] ?? null,
                'parent_address' => $validated['parent_address'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_job' => $validated['guardian_job'] ?? null,
                'guardian_address' => $validated['guardian_address'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $user = $student->user;
        $student->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function resetPassword(Request $request, Student $student): RedirectResponse
    {
        $defaultPassword = ! empty($student->nisn) ? (string) $student->nisn : (! empty($student->nis) ? (string) $student->nis : 'password');
        $label = ! empty($student->nisn) ? 'NISN' : (! empty($student->nis) ? 'NIS' : 'default');

        $student->user()->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Kata sandi siswa {$student->user->name} berhasil di-reset ke {$label} ({$defaultPassword}).");
    }
}
