<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = Teacher::with(['user', 'homeroomClasses'])->latest()->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['required', 'string', 'max:50', 'unique:teachers,nip'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar pada sistem.',
            'nip.required' => 'NIP / PEGID wajib diisi.',
            'nip.unique' => 'NIP / PEGID sudah digunakan.',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? 'password'),
                'role' => 'guru',
                'is_active' => true,
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$teacher->user_id],
            'nip' => ['required', 'string', 'max:50', 'unique:teachers,nip,'.$teacher->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'nip.required' => 'NIP / PEGID wajib diisi.',
            'nip.unique' => 'NIP / PEGID sudah digunakan.',
        ]);

        DB::transaction(function () use ($validated, $teacher) {
            $userUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            $teacher->user()->update($userUpdate);

            $teacher->update([
                'nip' => $validated['nip'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $user = $teacher->user;
        $teacher->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.teachers.index')->with('success', 'Data guru berhasil dihapus.');
    }

    public function downloadTemplate(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Data Guru');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap',
            'C1' => 'NIP / PEGID',
            'D1' => 'Email',
            'E1' => 'No. Telepon',
            'F1' => 'Password',
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
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Data Rows
        $sampleData = [
            [1, 'Ahmad Dahlan, S.Pd.', '198501012010011001', 'ahmad.dahlan@sekolah.sch.id', '081234567890', 'password'],
            [2, 'Siti Khadijah, M.Pd.', '198702022012022002', 'siti.khadijah@sekolah.sch.id', '081234567891', 'password'],
            [3, 'Budi Santoso, S.Kom.', '199003032015031003', 'budi.santoso@sekolah.sch.id', '081234567892', 'password'],
        ];

        $rowNum = 2;
        foreach ($sampleData as $row) {
            $sheet->setCellValue('A'.$rowNum, $row[0]);
            $sheet->setCellValue('B'.$rowNum, $row[1]);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) $row[2], DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rowNum, $row[3]);
            $sheet->setCellValueExplicit('E'.$rowNum, (string) $row[4], DataType::TYPE_STRING);
            $sheet->setCellValue('F'.$rowNum, $row[5]);
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
        $sheet->getStyle('A2:F'.($rowNum - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A2:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-fit columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'template_guru_'.date('Ymd_His').'.xlsx';

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
                return redirect()->route('admin.teachers.index')->with('error', 'File Excel kosong atau tidak memiliki data baris.');
            }

            $importedCount = 0;
            $skipped = [];

            foreach ($rows as $index => $row) {
                if ($index === 1) {
                    continue; // Skip header row
                }

                $name = trim((string) ($row['B'] ?? ''));
                $nip = trim((string) ($row['C'] ?? ''));
                $email = trim((string) ($row['D'] ?? ''));
                $phone = trim((string) ($row['E'] ?? ''));
                $password = trim((string) ($row['F'] ?? ''));

                // Skip row if completely empty
                if (empty($name) && empty($nip) && empty($email)) {
                    continue;
                }

                // Validation checks
                if (empty($name)) {
                    $skipped[] = "Baris $index: Nama guru kosong.";

                    continue;
                }
                if (empty($nip)) {
                    $skipped[] = "Baris $index ($name): NIP / PEGID kosong.";

                    continue;
                }
                if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped[] = "Baris $index ($name): Email tidak valid.";

                    continue;
                }

                // Check duplicate Email in users
                if (User::where('email', $email)->exists()) {
                    $skipped[] = "Baris $index ($name): Email '$email' sudah digunakan.";

                    continue;
                }

                // Check duplicate NIP in teachers
                if (Teacher::where('nip', $nip)->exists()) {
                    $skipped[] = "Baris $index ($name): NIP / PEGID '$nip' sudah digunakan.";

                    continue;
                }

                // Create user and teacher
                DB::transaction(function () use ($name, $email, $nip, $phone, $password) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(! empty($password) ? $password : 'password'),
                        'role' => 'guru',
                        'is_active' => true,
                    ]);

                    Teacher::create([
                        'user_id' => $user->id,
                        'nip' => $nip,
                        'phone' => ! empty($phone) ? $phone : null,
                    ]);
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
                return redirect()->route('admin.teachers.index')->with('error', 'Tidak ada data guru yang berhasil diimpor. '.implode(', ', array_slice($skipped, 0, 3)));
            }

            return redirect()->route('admin.teachers.index')->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->route('admin.teachers.index')->with('error', 'Gagal memproses file Excel: '.$e->getMessage());
        }
    }
}
