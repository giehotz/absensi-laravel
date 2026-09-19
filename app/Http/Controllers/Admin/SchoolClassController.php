<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    public function students(SchoolClass $class): View
    {
        $class->load(['academicYear', 'homeroomTeacher.user']);
        $students = Student::with('user')
            ->where('school_class_id', $class->id)
            ->latest()
            ->paginate(20);

        $studentsTotal = $class->students()->count();
        $maleCount = $class->students()->where('gender', 'L')->count();
        $femaleCount = $class->students()->where('gender', 'P')->count();

        return view('admin.classes.students', compact('class', 'students', 'studentsTotal', 'maleCount', 'femaleCount'));
    }

    public function downloadStudentsTemplate(SchoolClass $class): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa '.substr($class->name, 0, 20));

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap Siswa',
            'C1' => 'NIS',
            'D1' => 'NISN',
            'E1' => 'Jenis Kelamin (L/P)',
            'F1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'G1' => 'No. Telepon / WA',
            'H1' => 'Email',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Header Styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF3BF'],
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
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Data Rows
        $sampleRows = [
            [1, 'Muhammad Rizki', '1001', '0081234561', 'L', '2010-05-15', '081234567891', 'rizki@siswa.sekolah.sch.id'],
            [2, 'Nur Aini Putri', '1002', '0081234562', 'P', '2010-08-20', '081234567892', 'nuraini@siswa.sekolah.sch.id'],
            [3, 'Fajar Ramadhan', '1003', '0081234563', 'L', '2010-03-10', '081234567893', 'fajar@siswa.sekolah.sch.id'],
        ];

        $rowNum = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue('A'.$rowNum, $row[0]);
            $sheet->setCellValue('B'.$rowNum, $row[1]);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) $row[2], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$rowNum, (string) $row[3], DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$rowNum, $row[4]);
            $sheet->setCellValue('F'.$rowNum, $row[5]);
            $sheet->setCellValueExplicit('G'.$rowNum, (string) $row[6], DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$rowNum, $row[7]);
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
        $sheet->getStyle('A2:H'.($rowNum - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A2:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'H') as $col) {
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

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            if (count($rows) <= 1) {
                return redirect()->route('admin.classes.students', $class)->with('error', 'File Excel kosong atau tidak memiliki data baris siswa.');
            }

            $importedCount = 0;
            $skipped = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue; // Skip header
                }

                $name = trim((string) ($row['B'] ?? ''));
                $nis = trim((string) ($row['C'] ?? ''));
                $nisn = trim((string) ($row['D'] ?? ''));
                $genderRaw = strtoupper(trim((string) ($row['E'] ?? 'L')));
                $gender = in_array($genderRaw, ['L', 'P']) ? $genderRaw : 'L';
                $birthDate = trim((string) ($row['F'] ?? ''));
                $phone = trim((string) ($row['G'] ?? ''));
                $email = trim((string) ($row['H'] ?? ''));

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

                // Check duplicate NIS
                if (Student::where('nis', $nis)->exists()) {
                    $skipped[] = "Baris $index ($name): NIS '$nis' sudah terdaftar.";

                    continue;
                }

                // Check or build unique Email
                if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email = $nis.'@siswa.sekolah.sch.id';
                }

                if (User::where('email', $email)->exists()) {
                    $email = $nis.'.'.rand(100, 999).'@siswa.sekolah.sch.id';
                }

                // Parse birth_date safely
                $formattedBirthDate = null;
                if (! empty($birthDate)) {
                    $parsed = strtotime($birthDate);
                    if ($parsed !== false) {
                        $formattedBirthDate = date('Y-m-d', $parsed);
                    }
                }

                $qrCodeIdentifier = 'QR-'.$nis.'-'.strtoupper(bin2hex(random_bytes(3)));

                DB::transaction(function () use ($name, $email, $nis, $nisn, $class, $qrCodeIdentifier, $gender, $formattedBirthDate, $phone) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('password'),
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
                        'birth_date' => $formattedBirthDate,
                        'phone' => ! empty($phone) ? $phone : null,
                    ]);
                });

                $importedCount++;
            }

            $message = "Berhasil mengimpor $importedCount siswa ke kelas {$class->name}.";
            if (! empty($skipped)) {
                $message .= ' '.count($skipped).' data dilewati: '.implode(', ', array_slice($skipped, 0, 3));
                if (count($skipped) > 3) {
                    $message .= ' dan '.(count($skipped) - 3).' baris lainnya.';
                }
            }

            if ($importedCount === 0 && ! empty($skipped)) {
                return redirect()->route('admin.classes.students', $class)->with('error', 'Tidak ada data siswa yang berhasil diimpor. '.implode(', ', array_slice($skipped, 0, 3)));
            }

            return redirect()->route('admin.classes.students', $class)->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.classes.students', $class)->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
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
