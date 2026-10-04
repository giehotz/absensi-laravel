<?php

namespace App\Services\Assessment;

use App\Models\Assessment;
use App\Models\AssessmentPackage;
use App\Models\AssessmentScore;
use App\Models\AssessmentUploadBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssessmentExcelService
{
    /**
     * Hasilkan file template Excel 15 sheet (SUM 1 s.d. SUM 15) untuk paket penilaian.
     */
    public function downloadTemplate(AssessmentPackage $package): StreamedResponse
    {
        $package->loadMissing([
            'schoolClass.students.user',
            'subject',
            'teacher.user',
            'assessments.scores',
        ]);

        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0); // Buang sheet default kosong

        $students = $package->schoolClass->students->sortBy(function ($student) {
            return $student->user?->name ?? '';
        })->values();

        $teacherName = $package->teacher->user?->name ?? 'Guru Pengampu';
        $classSubject = $package->schoolClass->name.'/'.$package->subject->name;

        for ($sheetNum = 1; $sheetNum <= 15; $sheetNum++) {
            $sheet = $spreadsheet->createSheet();
            $sheetTitle = "SUM {$sheetNum}";
            $sheet->setTitle($sheetTitle);

            $existingAssessment = $package->assessments->firstWhere('sheet_number', $sheetNum);
            $materi = $existingAssessment?->materi ?? '';
            $kktp = $existingAssessment?->kktp ?? $package->kktp_default;

            // Baris 1: Judul
            $sheet->setCellValue('A1', 'Template Nilai Sumatif');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            // Baris 2: Informasi Guru & Rombel/Mapel
            $sheet->setCellValue('A2', 'Nama: '.$teacherName);
            $sheet->setCellValue('D2', 'Kelas/Mapel:');
            $sheet->setCellValue('E2', $classSubject);
            $sheet->getStyle('A2:E2')->getFont()->setBold(true);

            // Baris 3: Materi (Merge B3 s.d. F3)
            $sheet->setCellValue('A3', 'Materi');
            $sheet->setCellValue('B3', $materi);
            $sheet->mergeCells('B3:F3');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
            $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

            $sheet->getStyle('B3:F3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B3:F3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
            $sheet->getStyle('B3:F3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB('000000');
            $sheet->getRowDimension(3)->setRowHeight(24);

            // Baris 5: KKTP
            $sheet->setCellValue('A5', 'KKTP');
            $sheet->setCellValue('B5', $kktp);
            $sheet->getStyle('A5:B5')->getFont()->setBold(true);
            $sheet->getStyle('A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            $sheet->getStyle('A5:B5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
            $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Baris 6: Header Tabel
            $headers = ['No', 'ID Siswa', 'NIS', 'Nisn', 'Nama', 'Nilai'];
            $sheet->fromArray($headers, null, 'A6');
            $sheet->getStyle('A6:F6')->getFont()->setBold(true);
            $sheet->getStyle('A6:E6')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFF1F5F9');
            $sheet->getStyle('F6')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFE066'); // Highlight kolom Nilai dengan warna kuning cerah
            $sheet->getStyle('A6:F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(6)->setRowHeight(24);

            // Baris 7 dst: Data Siswa
            $rowIndex = 7;
            foreach ($students as $idx => $student) {
                $existingScore = $existingAssessment?->scores->firstWhere('student_id', $student->id)?->score;

                $sheet->setCellValue("A{$rowIndex}", $idx + 1);
                $sheet->setCellValueExplicit("B{$rowIndex}", (string) $student->id, DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C{$rowIndex}", (string) ($student->nis ?? '-'), DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("D{$rowIndex}", (string) ($student->nisn ?? '-'), DataType::TYPE_STRING);
                $sheet->setCellValue("E{$rowIndex}", $student->user?->name ?? '-');
                $sheet->setCellValue("F{$rowIndex}", $existingScore !== null ? (float) $existingScore : '');

                $sheet->getStyle("A{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("B{$rowIndex}:D{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($rowIndex)->setRowHeight(20);

                $rowIndex++;
            }

            // Styling border tabel data dengan warna hitam yang tegas & jelas
            $lastRow = max(6, $rowIndex - 1);

            // Border seluruh sel data tabel dengan garis hitam (BORDER_THIN solid black)
            $sheet->getStyle("A6:F{$lastRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('000000');

            // Outline tabel keseluruhan hitam tebal (BORDER_MEDIUM solid black)
            $sheet->getStyle("A6:F{$lastRow}")->getBorders()->getOutline()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('000000');

            // Border khusus kolom Input Nilai (F6 s.d. F{$lastRow}) warna hitam tebal dan sangat jelas
            $sheet->getStyle("F6:F{$lastRow}")->getBorders()->getLeft()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('000000');
            $sheet->getStyle("F6:F{$lastRow}")->getBorders()->getRight()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('000000');
            $sheet->getStyle("F6:F{$lastRow}")->getBorders()->getTop()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('000000');
            $sheet->getStyle("F6:F{$lastRow}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setRGB('000000');
            $sheet->getStyle("F7:F{$lastRow}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()->setRGB('000000');

            // Set lebar kolom otomatis, dan sediakan lebar lapang untuk kolom Nilai
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('F')->setAutoSize(false)->setWidth(16);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $cleanFileName = 'Template_Sumatif_'.str_replace([' ', '/', '\\'], '_', $classSubject).'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $cleanFileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Parsing file Excel unggahan untuk divalidasi in-memory dan disajikan di halaman pratinjau.
     *
     * @return array{is_valid: bool, error_message: ?string, summary: array, sheets: array}
     */
    public function parseUploadedExcel(UploadedFile $file, AssessmentPackage $package): array
    {
        $package->loadMissing(['schoolClass.students.user', 'subject']);

        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());

        $students = $package->schoolClass->students->keyBy('id');
        $studentsByNisn = $package->schoolClass->students->whereNotNull('nisn')->keyBy('nisn');
        $studentsByNis = $package->schoolClass->students->whereNotNull('nis')->keyBy('nis');

        $parsedSheets = [];
        $totalValidScores = 0;
        $totalErrorScores = 0;
        $totalEmptyScores = 0;
        $sheetsWithData = 0;

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $title = trim($sheet->getTitle());

            // Filter hanya sheet berformat SUM 1 s.d. SUM 15
            if (! preg_match('/^SUM\s*(\d+)$/i', $title, $matches)) {
                continue;
            }

            $sheetNum = (int) $matches[1];
            if ($sheetNum < 1 || $sheetNum > 15) {
                continue;
            }

            // Validasi kelas/mapel di E2 jika terisi
            $classSubjectCell = trim((string) $sheet->getCell('E2')->getValue());

            // Ekstrak materi (B3) dan KKTP (B5)
            $materi = trim((string) $sheet->getCell('B3')->getValue());
            $kktpRaw = $sheet->getCell('B5')->getValue();
            $kktp = is_numeric($kktpRaw) ? (int) $kktpRaw : (int) $package->kktp_default;

            $sheetRows = [];
            $sheetHasAnyScore = false;
            $sheetValidCount = 0;
            $sheetErrorCount = 0;
            $sheetEmptyCount = 0;

            $highestRow = $sheet->getHighestDataRow();

            for ($row = 7; $row <= $highestRow; $row++) {
                $no = $sheet->getCell("A{$row}")->getValue();
                $rawId = trim((string) $sheet->getCell("B{$row}")->getValue());
                $nis = trim((string) $sheet->getCell("C{$row}")->getValue());
                $nisn = trim((string) $sheet->getCell("D{$row}")->getValue());
                $name = trim((string) $sheet->getCell("E{$row}")->getValue());
                $scoreRaw = $sheet->getCell("F{$row}")->getValue();

                // Abaikan baris kosong di bagian bawah
                if ($rawId === '' && $nis === '' && $nisn === '' && $name === '' && $scoreRaw === null) {
                    continue;
                }

                // Matching Siswa
                $matchedStudent = null;
                $matchType = 'none';

                if ($rawId !== '' && isset($students[(int) $rawId])) {
                    $matchedStudent = $students[(int) $rawId];
                    $matchType = 'id';
                } elseif ($nisn !== '' && isset($studentsByNisn[$nisn])) {
                    $matchedStudent = $studentsByNisn[$nisn];
                    $matchType = 'nisn';
                } elseif ($nis !== '' && isset($studentsByNis[$nis])) {
                    $matchedStudent = $studentsByNis[$nis];
                    $matchType = 'nis';
                }

                $rowStatus = 'valid';
                $errorMessage = null;
                $numericScore = null;
                $completionStatus = 'belum_dinilai';

                if (! $matchedStudent) {
                    $rowStatus = 'error';
                    $errorMessage = "Siswa tidak ditemukan di kelas {$package->schoolClass->name}";
                    $sheetErrorCount++;
                    $totalErrorScores++;
                } elseif ($scoreRaw === null || trim((string) $scoreRaw) === '') {
                    $rowStatus = 'empty';
                    $sheetEmptyCount++;
                    $totalEmptyScores++;
                } else {
                    $scoreValue = str_replace(',', '.', trim((string) $scoreRaw));
                    if (! is_numeric($scoreValue) || (float) $scoreValue < 0 || (float) $scoreValue > 100) {
                        $rowStatus = 'error';
                        $errorMessage = 'Nilai harus berupa angka 0 - 100';
                        $sheetErrorCount++;
                        $totalErrorScores++;
                    } else {
                        $numericScore = round((float) $scoreValue, 2);
                        $completionStatus = $numericScore >= $kktp ? 'tuntas' : 'remedial';
                        $sheetValidCount++;
                        $totalValidScores++;
                        $sheetHasAnyScore = true;
                    }
                }

                $sheetRows[] = [
                    'row' => $row,
                    'student_id' => $matchedStudent?->id,
                    'student_name' => $matchedStudent?->user?->name ?? $name,
                    'nis' => $matchedStudent?->nis ?? $nis,
                    'nisn' => $matchedStudent?->nisn ?? $nisn,
                    'score' => $numericScore,
                    'kktp' => $kktp,
                    'row_status' => $rowStatus,
                    'completion_status' => $completionStatus,
                    'error_message' => $errorMessage,
                ];
            }

            if ($sheetHasAnyScore || ! empty($materi)) {
                $sheetsWithData++;
            }

            $parsedSheets[$sheetNum] = [
                'sheet_number' => $sheetNum,
                'sheet_title' => $title,
                'materi' => $materi,
                'kktp' => $kktp,
                'has_data' => $sheetHasAnyScore || ! empty($materi),
                'valid_count' => $sheetValidCount,
                'error_count' => $sheetErrorCount,
                'empty_count' => $sheetEmptyCount,
                'total_rows' => count($sheetRows),
                'rows' => $sheetRows,
            ];
        }

        if (empty($parsedSheets)) {
            return [
                'is_valid' => false,
                'error_message' => 'File Excel tidak memiliki lembar sumatif (sheet SUM 1 s.d. SUM 15) yang valid.',
                'summary' => [],
                'sheets' => [],
            ];
        }

        if ($sheetsWithData === 0) {
            return [
                'is_valid' => false,
                'error_message' => 'Seluruh sheet SUM 1 s.d. SUM 15 kosong atau belum diisi nilai.',
                'summary' => [],
                'sheets' => [],
            ];
        }

        return [
            'is_valid' => true,
            'error_message' => null,
            'summary' => [
                'sheets_with_data' => $sheetsWithData,
                'total_valid_scores' => $totalValidScores,
                'total_error_scores' => $totalErrorScores,
                'total_empty_scores' => $totalEmptyScores,
            ],
            'sheets' => $parsedSheets,
        ];
    }

    /**
     * Commit penyimpanan data pratinjau yang valid ke basis data dalam transaksi atomik.
     */
    public function commitImport(AssessmentPackage $package, array $sheetsData, int $userId, string $filename = 'upload.xlsx'): AssessmentUploadBatch
    {
        return DB::transaction(function () use ($package, $sheetsData, $userId, $filename) {
            $totalRows = 0;
            $successRows = 0;
            $failedRows = 0;
            $errorLog = [];

            foreach ($sheetsData as $sheetNum => $sheet) {
                // Lewati sheet yang sama sekali tidak memiliki data
                if (empty($sheet['has_data'])) {
                    continue;
                }

                $assessment = Assessment::firstOrCreate(
                    [
                        'assessment_package_id' => $package->id,
                        'sheet_number' => (int) $sheetNum,
                    ],
                    [
                        'sheet_name' => "SUM {$sheetNum}",
                        'kktp' => (int) ($sheet['kktp'] ?? $package->kktp_default),
                        'materi' => $sheet['materi'] ?? null,
                    ]
                );

                // Update materi dan kktp jika ada perubahan dari Excel, serta aktifkan sumatif ini
                $assessment->update([
                    'materi' => ! empty($sheet['materi']) ? $sheet['materi'] : $assessment->materi,
                    'kktp' => ! empty($sheet['kktp']) ? (int) $sheet['kktp'] : $assessment->kktp,
                    'is_active' => true,
                ]);

                foreach ($sheet['rows'] as $row) {
                    $totalRows++;

                    if ($row['row_status'] === 'error') {
                        $failedRows++;
                        $errorLog[] = "SUM {$sheetNum} Baris {$row['row']}: ".($row['error_message'] ?? 'Error');

                        continue;
                    }

                    if ($row['row_status'] === 'valid' && ! empty($row['student_id']) && $row['score'] !== null) {
                        AssessmentScore::updateOrCreate(
                            [
                                'assessment_id' => $assessment->id,
                                'student_id' => (int) $row['student_id'],
                            ],
                            [
                                'score' => (float) $row['score'],
                                'status' => $row['completion_status'] ?? 'belum_dinilai',
                            ]
                        );
                        $successRows++;
                    }
                }
            }

            // Catat ke riwayat batch upload
            return AssessmentUploadBatch::create([
                'assessment_package_id' => $package->id,
                'uploaded_by' => $userId,
                'filename' => $filename,
                'total_rows' => $totalRows,
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
                'error_log' => ! empty($errorLog) ? $errorLog : null,
            ]);
        });
    }

    /**
     * Ekspor rekapitulasi nilai sumatif seluruh siswa ke file Excel (hanya sumatif yang aktif).
     */
    public function exportRecapExcel(AssessmentPackage $package): StreamedResponse
    {
        $package->loadMissing([
            'schoolClass.students.user',
            'subject',
            'teacher.user',
            'academicYear',
            'assessments.scores',
        ]);

        $activeAssessments = $package->getActiveAssessments();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Sumatif');

        // Judul Laporan
        $sheet->setCellValue('A1', 'REKAPITULASI PENILAIAN SUMATIF SISWA');
        $sheet->setCellValue('A2', 'Mata Pelajaran: '.$package->subject->name.' | Kelas: '.$package->schoolClass->name);
        $sheet->setCellValue('A3', 'Tahun Ajaran: '.$package->academicYear->name.' ('.ucfirst($package->academicYear->semester).') | Guru: '.($package->teacher->user?->name ?? '-'));
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);

        // Header Kolom Dinamis Sesuai Sumatif Aktif
        $headers = ['No', 'NIS', 'Nama Siswa'];
        foreach ($activeAssessments as $asm) {
            $headers[] = "SUM {$asm->sheet_number}";
        }
        $headers[] = 'Rata-Rata';
        $headers[] = 'Status Akhir';

        $sheet->fromArray($headers, null, 'A5');
        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A5:{$lastColLetter}5")->getFont()->setBold(true);
        $sheet->getStyle("A5:{$lastColLetter}5")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        $students = $package->schoolClass->students->sortBy(function ($s) {
            return $s->user?->name ?? '';
        })->values();

        $rowNum = 6;

        foreach ($students as $idx => $student) {
            $rowValues = [
                $idx + 1,
                $student->nis ?? '-',
                $student->user?->name ?? '-',
            ];

            $scoresList = [];
            foreach ($activeAssessments as $asm) {
                $score = $asm->scores->firstWhere('student_id', $student->id)?->score;
                if ($score !== null) {
                    $rowValues[] = (float) $score;
                    $scoresList[] = (float) $score;
                } else {
                    $rowValues[] = '-';
                }
            }

            $avg = count($scoresList) > 0 ? round(array_sum($scoresList) / count($scoresList), 2) : 0;
            $rowValues[] = count($scoresList) > 0 ? $avg : '-';
            $rowValues[] = ($avg >= $package->kktp_default && count($scoresList) > 0) ? 'Tuntas' : (count($scoresList) > 0 ? 'Remedial' : 'Belum Dinilai');

            $sheet->fromArray($rowValues, null, "A{$rowNum}");
            $rowNum++;
        }

        $lastDataRow = max(5, $rowNum - 1);
        $sheet->getStyle("A5:{$lastColLetter}{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, count($headers)) as $colIdx) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = 'Rekap_Nilai_'.str_replace([' ', '/', '\\'], '_', $package->schoolClass->name.'_'.$package->subject->name).'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
