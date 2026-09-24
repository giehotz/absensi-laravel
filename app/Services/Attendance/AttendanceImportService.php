<?php

namespace App\Services\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceUploadBatch;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceImportService
{
    /**
     * Generate & stream pre-filled Excel template for the selected class and date.
     */
    public function downloadTemplate(SchoolClass $schoolClass, ?Schedule $schedule = null, ?string $date = null): StreamedResponse
    {
        $date = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();
        $formattedDate = Carbon::parse($date)->translatedFormat('d F Y');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Presensi');

        // Judul & Keterangan
        $sheet->setCellValue('A1', 'TEMPLATE UNGGAH PRESENSI SISWA');
        $sheet->setCellValue('A2', 'Kelas: '.$schoolClass->name.' (Tingkat: '.$schoolClass->level.') • Tanggal: '.$formattedDate.' ('.$date.')');

        $scheduleInfo = $schedule ? 'Mata Pelajaran: '.($schedule->subject->name ?? '-').' | Guru: '.($schedule->teacher->user->name ?? '-') : 'Mode: Presensi Harian Sekolah';
        $sheet->setCellValue('A3', $scheduleInfo);
        $sheet->setCellValue('A4', 'Petunjuk Status: H = Hadir, T = Terlambat, S = Sakit, I = Izin, A = Alpa (Bisa ditulis kode huruf atau kata lengkap).');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9)->getColor()->setRGB('4B5563');

        // Header Kolom
        $headers = [
            'A6' => 'No',
            'B6' => 'NIS',
            'C6' => 'Nama Siswa',
            'D6' => 'Kelas',
            'E6' => 'Tanggal (YYYY-MM-DD)',
            'F6' => 'Status (H/T/S/I/A)',
            'G6' => 'Keterangan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'], // Warna Kuning Neo
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A6:G6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Ambil daftar siswa aktif di kelas
        $students = Student::with('user')
            ->where('school_class_id', $schoolClass->id)
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '');

        $rowNum = 7;
        $no = 1;
        foreach ($students as $student) {
            $sheet->setCellValueExplicit('A'.$rowNum, $no++, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('B'.$rowNum, $student->nis, DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$rowNum, $student->user->name ?? '-');
            $sheet->setCellValue('D'.$rowNum, $schoolClass->name);
            $sheet->setCellValueExplicit('E'.$rowNum, $date, DataType::TYPE_STRING);
            $sheet->setCellValue('F'.$rowNum, 'H'); // Default kehadiran Hadir
            $sheet->setCellValue('G'.$rowNum, '');

            $rowNum++;
        }

        $lastRow = max(7, $rowNum - 1);

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A7:G'.$lastRow)->applyFromArray($dataStyle);
        $sheet->getStyle('A7:A'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7:B'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D7:F'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto width untuk setiap kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeClassName = Str::slug($schoolClass->name);
        $filename = "Template_Presensi_{$safeClassName}_{$date}.xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Memproses file Excel/CSV absensi yang diunggah.
     *
     * @return array{batch: AttendanceUploadBatch, success_count: int, failed_count: int, errors: array}
     */
    public function importFile(
        UploadedFile $file,
        SchoolClass $schoolClass,
        ?Schedule $schedule,
        ?string $fallbackDate,
        User $user
    ): array {
        $fallbackDate = $fallbackDate ? Carbon::parse($fallbackDate)->toDateString() : Carbon::today()->toDateString();
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, true);

        // Cari baris header
        $headerRowIndex = null;
        $columnMap = [
            'nis' => null,
            'tanggal' => null,
            'status' => null,
            'keterangan' => null,
        ];

        foreach ($rawRows as $index => $row) {
            foreach ($row as $colLetter => $cellVal) {
                $clean = strtolower(trim((string) $cellVal));
                if (in_array($clean, ['nis', 'no_induk', 'nomor induk', 'nis siswa'])) {
                    $headerRowIndex = $index;
                    $columnMap['nis'] = $colLetter;
                }
            }

            if ($headerRowIndex !== null) {
                // Map kolom lain pada baris header ini
                foreach ($row as $colLetter => $cellVal) {
                    $clean = strtolower(trim((string) $cellVal));
                    if (str_contains($clean, 'tanggal') || str_contains($clean, 'date')) {
                        $columnMap['tanggal'] = $colLetter;
                    } elseif (str_contains($clean, 'status') || str_contains($clean, 'kehadiran')) {
                        $columnMap['status'] = $colLetter;
                    } elseif (str_contains($clean, 'keterangan') || str_contains($clean, 'catatan') || str_contains($clean, 'notes')) {
                        $columnMap['keterangan'] = $colLetter;
                    }
                }
                break;
            }
        }

        // Fallback jika tidak menemukan header terstruktur: gunakan format standar template
        if ($headerRowIndex === null) {
            $headerRowIndex = 6;
            $columnMap = [
                'nis' => 'B',
                'tanggal' => 'E',
                'status' => 'F',
                'keterangan' => 'G',
            ];
        } else {
            // Isi fallback default jika ada kolom yang tidak terdeteksi
            $columnMap['nis'] = $columnMap['nis'] ?? 'B';
            $columnMap['tanggal'] = $columnMap['tanggal'] ?? 'E';
            $columnMap['status'] = $columnMap['status'] ?? 'F';
            $columnMap['keterangan'] = $columnMap['keterangan'] ?? 'G';
        }

        // Cache siswa dalam kelas untuk lookup cepat berdasarkan NIS
        $studentsInClass = Student::where('school_class_id', $schoolClass->id)
            ->get()
            ->keyBy(fn ($s) => trim((string) $s->nis));

        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        $validRecords = [];

        foreach ($rawRows as $rowIndex => $row) {
            if ($rowIndex <= $headerRowIndex) {
                continue; // Lewati header dan judul
            }

            $nis = trim((string) ($row[$columnMap['nis']] ?? ''));
            if ($nis === '') {
                continue; // Lewati baris kosong
            }

            $rawDate = $row[$columnMap['tanggal']] ?? null;
            $rawStatus = trim((string) ($row[$columnMap['status']] ?? ''));
            $notes = trim((string) ($row[$columnMap['keterangan']] ?? ''));

            // 1. Validasi Siswa
            $student = $studentsInClass->get($nis);
            if (! $student) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'nis' => $nis,
                    'error' => "NIS '{$nis}' tidak terdaftar di kelas {$schoolClass->name}.",
                ];

                continue;
            }

            // 2. Validasi Tanggal
            $parsedDate = $this->parseDate($rawDate) ?? $fallbackDate;
            if (! $parsedDate) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'nis' => $nis,
                    'error' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
                ];

                continue;
            }

            // 3. Validasi & Normalisasi Status
            $normalizedStatus = $this->normalizeStatus($rawStatus);
            if (! $normalizedStatus) {
                $failedCount++;
                $errors[] = [
                    'row' => $rowIndex,
                    'nis' => $nis,
                    'error' => "Status '{$rawStatus}' tidak dikenali. Gunakan: Hadir (H), Terlambat (T), Sakit (S), Izin (I), Alpa (A).",
                ];

                continue;
            }

            $validRecords[] = [
                'student' => $student,
                'date' => $parsedDate,
                'status' => $normalizedStatus,
                'notes' => $notes !== '' ? $notes : null,
            ];
        }

        $totalProcessed = $successCount + $failedCount + count($validRecords);

        // Eksekusi penyimpanan ke database
        return DB::transaction(function () use (
            $file,
            $schoolClass,
            $schedule,
            $fallbackDate,
            $user,
            $validRecords,
            $errors,
            $failedCount,
            $totalProcessed
        ) {
            $batchUuid = (string) Str::uuid();

            $batch = AttendanceUploadBatch::create([
                'batch_uuid' => $batchUuid,
                'uploaded_by' => $user->id,
                'school_class_id' => $schoolClass->id,
                'schedule_id' => $schedule?->id,
                'date' => $fallbackDate,
                'original_filename' => $file->getClientOriginalName(),
                'total_rows' => $totalProcessed,
                'success_rows' => 0,
                'failed_rows' => $failedCount,
                'status' => 'completed',
                'error_log' => ! empty($errors) ? $errors : null,
            ]);

            $now = Carbon::now();
            $successCount = 0;

            foreach ($validRecords as $item) {
                $student = $item['student'];
                $date = $item['date'];
                $status = $item['status'];
                $notes = $item['notes'];

                $existing = Attendance::where('student_id', $student->id)
                    ->where('schedule_id', $schedule?->id)
                    ->whereDate('date', $date)
                    ->first();

                $checkInTime = in_array($status, ['hadir', 'terlambat'])
                    ? ($existing?->check_in_time ?? $now)
                    : null;

                if ($existing) {
                    $existing->update([
                        'status' => $status,
                        'method' => 'upload',
                        'recorded_by' => $user->id,
                        'upload_batch_id' => $batch->id,
                        'check_in_time' => $checkInTime,
                        'notes' => $notes ?? $existing->notes,
                    ]);
                } else {
                    Attendance::create([
                        'student_id' => $student->id,
                        'schedule_id' => $schedule?->id,
                        'date' => $date,
                        'status' => $status,
                        'method' => 'upload',
                        'recorded_by' => $user->id,
                        'upload_batch_id' => $batch->id,
                        'check_in_time' => $checkInTime,
                        'notes' => $notes,
                    ]);
                }

                $successCount++;
            }

            // Perbarui status akhir batch
            $finalStatus = 'completed';
            if ($failedCount > 0 && $successCount > 0) {
                $finalStatus = 'completed_with_errors';
            } elseif ($failedCount > 0 && $successCount === 0) {
                $finalStatus = 'failed';
            }

            $batch->update([
                'success_rows' => $successCount,
                'failed_rows' => $failedCount,
                'status' => $finalStatus,
            ]);

            return [
                'batch' => $batch,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ];
        });
    }

    /**
     * Normalisasi status presensi dari string atau kode singkatan.
     */
    public function normalizeStatus(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $cleaned = strtolower(trim($input));

        return match ($cleaned) {
            'h', 'hadir', 'present' => 'hadir',
            't', 'terlambat', 'late' => 'terlambat',
            's', 'sakit', 'sick' => 'sakit',
            'i', 'izin', 'ijin', 'permission' => 'izin',
            'a', 'alpa', 'alpha', 'absen', 'absent' => 'alpa',
            default => null,
        };
    }

    /**
     * Parse tanggal dari berbagai format Excel maupun teks.
     */
    public function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }

        // Jika merupakan angka numerik dari serial tanggal Excel
        if (is_numeric($trimmed) && (float) $trimmed > 25569) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $trimmed)->format('Y-m-d');
            } catch (\Throwable) {
            }
        }

        $formats = [
            'Y-m-d',
            'd-m-Y',
            'd/m/Y',
            'Y/m/d',
            'j-n-Y',
            'j/n/Y',
            'Ymd',
        ];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $trimmed);
                if ($d !== false && $d->year > 2000) {
                    return $d->format('Y-m-d');
                }
            } catch (\Throwable) {
            }
        }

        $timestamp = strtotime($trimmed);
        if ($timestamp !== false && date('Y', $timestamp) > 2000) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }
}
