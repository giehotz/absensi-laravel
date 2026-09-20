<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher.user', 'students'])->latest()->paginate(15);
        $teachers = Teacher::with('user')->get();
        $academicYears = AcademicYear::all();

        return view('admin.classes.index', compact('classes', 'teachers', 'academicYears'));
    }

    public function transferView(Request $request): View
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher.user'])
            ->withCount('students')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->query('from_class_id');

        return view('admin.classes.transfer', compact('classes', 'selectedClassId'));
    }

    public function getTransferData(SchoolClass $class): JsonResponse
    {
        $class->load(['academicYear', 'homeroomTeacher.user']);

        $students = Student::with('user')
            ->where('school_class_id', $class->id)
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->user?->name ?? 'Tanpa Nama',
                    'nis' => $s->nis ?? '-',
                    'nisn' => $s->nisn ?? '-',
                    'gender' => $s->gender ?? '-',
                ];
            });

        $targetClasses = SchoolClass::with(['homeroomTeacher.user'])
            ->withCount('students')
            ->where('academic_year_id', $class->academic_year_id)
            ->where('id', '!=', $class->id)
            ->orderBy('name')
            ->get()
            ->map(function ($tc) {
                return [
                    'id' => $tc->id,
                    'name' => $tc->name,
                    'level' => $tc->level,
                    'homeroom_teacher' => $tc->homeroomTeacher?->user?->name ?? 'Belum Ditentukan',
                    'students_count' => $tc->students_count,
                ];
            });

        return response()->json([
            'source_class' => [
                'id' => $class->id,
                'name' => $class->name,
                'level' => $class->level,
                'academic_year' => $class->academicYear ? $class->academicYear->name.' ('.ucfirst($class->academicYear->semester).')' : '-',
                'homeroom_teacher' => $class->homeroomTeacher?->user?->name ?? 'Belum Ditentukan',
            ],
            'students' => $students,
            'target_classes' => $targetClasses,
        ]);
    }

    public function executeTransfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_class_id' => ['required', 'exists:school_classes,id'],
            'target_class_id' => ['required', 'exists:school_classes,id', 'different:source_class_id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:students,id'],
        ], [
            'source_class_id.required' => 'Kelas asal wajib dipilih.',
            'target_class_id.required' => 'Kelas tujuan wajib dipilih.',
            'target_class_id.different' => 'Kelas tujuan tidak boleh sama dengan kelas asal.',
            'student_ids.required' => 'Pilih minimal satu siswa untuk dipindahkan.',
            'student_ids.min' => 'Pilih minimal satu siswa untuk dipindahkan.',
        ]);

        $sourceClass = SchoolClass::findOrFail($validated['source_class_id']);
        $targetClass = SchoolClass::findOrFail($validated['target_class_id']);

        if ($sourceClass->academic_year_id !== $targetClass->academic_year_id) {
            return back()->with('error', 'Kelas asal dan kelas tujuan harus berada dalam tahun ajaran yang sama.');
        }

        $count = 0;
        DB::transaction(function () use ($validated, $sourceClass, $targetClass, &$count) {
            $count = Student::where('school_class_id', $sourceClass->id)
                ->whereIn('id', $validated['student_ids'])
                ->update(['school_class_id' => $targetClass->id]);
        });

        return redirect()
            ->route('admin.classes.transfer', ['from_class_id' => $targetClass->id])
            ->with('success', "Berhasil memindahkan {$count} siswa dari {$sourceClass->name} ke {$targetClass->name}.");
    }

    public function students(SchoolClass $class): View
    {
        $class->load(['academicYear', 'homeroomTeacher.user']);
        $students = Student::with('user')
            ->where('school_class_id', $class->id)
            ->latest()
            ->get();

        $studentsTotal = $class->students()->count();
        $maleCount = $class->students()->where('gender', 'L')->count();
        $femaleCount = $class->students()->where('gender', 'P')->count();

        return view('admin.classes.students', compact('class', 'students', 'studentsTotal', 'maleCount', 'femaleCount'));
    }

    public function downloadStudentsTemplate(SchoolClass $class): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa '.substr($class->name, 0, 15));

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
                'PNS / Guru', 'Ibu Rumah Tangga', 'Jl. Merdeka No. 10, Jakarta', '', '', '', $class->name,
            ],
            [
                2, '1002', '0081234562', 'Nur Aini Putri', 'P', 'Bandung', '20-08-2010', '1002',
                'Jl. Asia Afrika No. 25, Bandung', 'Islam', 'Anak Kandung', '2', '081234567892',
                'SD Negeri 05 Bandung', '15-07-2023', 'VII', 'Ahmad Hidayat', 'Dewi Sartika',
                'Wiraswasta', 'Karyawan Swasta', 'Jl. Asia Afrika No. 25, Bandung', '', '', '', $class->name,
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
        $sanitizedClassName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $class->name);
        $filename = 'template_siswa_'.$sanitizedClassName.'_'.date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function importStudentsExcel(Request $request, SchoolClass $class): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau .csv.',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
        ]);

        $allowUpsert = $request->boolean('upsert', true);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return redirect()->route('admin.classes.students', $class)->with('error', 'File Excel kosong atau tidak memiliki data baris siswa.');
            }

            $importedCount = 0;
            $updatedCount = 0;
            $skipped = [];

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

                // Parse dates
                $birthDate = $this->parseDate($birthDateRaw);
                $admissionDate = $this->parseDate($admissionDateRaw);

                // Password logic
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
                        $existingStudent, $name, $passwordRaw, $plainPassword, $class,
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
                            'school_class_id' => $class->id,
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
                    $name, $email, $plainPassword, $class, $nis, $nisn,
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
                        'school_class_id' => $class->id,
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

            $message = "Berhasil mengimpor $importedCount siswa ke kelas {$class->name}";
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
                return redirect()->route('admin.classes.students', $class)->with('error', 'Tidak ada data siswa yang berhasil diimpor. '.implode(', ', array_slice($skipped, 0, 3)));
            }

            return redirect()->route('admin.classes.students', $class)->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.classes.students', $class)->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
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
            'name' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:20'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
        ]);

        if (empty($validated['level'])) {
            $setting = AttendanceSetting::first();
            $validated['level'] = $setting?->level ?? 'SMP';
        }

        if (empty($validated['academic_year_id'])) {
            $activeYear = AcademicYear::where('is_active', true)->first();
            $validated['academic_year_id'] = $activeYear ? $activeYear->id : 1;
        }

        SchoolClass::create($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:20'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
        ]);

        if (empty($validated['level'])) {
            $setting = AttendanceSetting::first();
            $validated['level'] = $setting?->level ?? $class->level ?? 'SMP';
        }

        $class->update($validated);

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil dihapus.');
    }
}
