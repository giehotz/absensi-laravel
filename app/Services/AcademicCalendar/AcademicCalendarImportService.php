<?php

namespace App\Services\AcademicCalendar;

use App\Models\AcademicCalendar;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicCalendarImportService
{
    /**
     * Unduh template file Excel Kalender Pendidikan.
     * Kolom: A (No), B (Hari), C (Tanggal Mulai), D (Tanggal Selesai), E (Keterangan Kegiatan), F (Kategori)
     */
    public function downloadTemplate(string $academicYearName): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kalender Pendidikan');

        // Judul Template
        $sheet->setCellValue('A1', 'TEMPLATE KALENDER PENDIDIKAN TAHUN AJARAN '.$academicYearName);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setName('Arial');
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Petunjuk Pengisian
        $sheet->setCellValue('A2', 'Petunjuk: Kolom Hari opsional (akan otomatis dihitung jika dikosongkan). Format Tanggal Mulai (Cell C) dan Tanggal Selesai (Cell D) adalah DD/MM/YYYY. Jika kegiatan hanya 1 hari, Tanggal Selesai boleh dikosongkan. Kategori: kegiatan, libur, ujian, rapat, umum.');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('555555'));
        $sheet->getStyle('A2')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(24);

        // Header Tabel Kolom
        $headers = [
            'A3' => 'No',
            'B3' => 'Hari (Opsional)',
            'C3' => 'Tanggal Mulai (dd/mm/yyyy)',
            'D3' => 'Tanggal Selesai (dd/mm/yyyy)',
            'E3' => 'Keterangan Kegiatan',
            'F3' => 'Kategori (kegiatan/libur/ujian/rapat)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'], // Kuning Neo-brutalist
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
        $sheet->getStyle('A3:F3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // Contoh Data Bawaan
        $sampleData = [
            [1, 'Senin - Rabu', '13/07/2026', '15/07/2026', 'Masa Pengenalan Lingkungan Sekolah (MPLS)', 'kegiatan'],
            [2, 'Senin', '17/08/2026', '', 'Hari Kemerdekaan Republik Indonesia ke-81', 'libur'],
            [3, 'Selasa - Sabtu', '01/12/2026', '12/12/2026', 'Penilaian Akhir Semester (PAS) Ganjil', 'ujian'],
            [4, 'Sabtu', '19/12/2026', '', 'Pembagian Buku Laporan Pendidikan (Rapor) Semester Ganjil', 'kegiatan'],
            [5, 'Senin - Sabtu', '21/12/2026', '02/01/2027', 'Libur Semester Ganjil', 'libur'],
            [6, 'Senin', '04/01/2027', '', 'Hari Pertama Masuk Sekolah Semester Genap', 'kegiatan'],
            [7, 'Senin - Sabtu', '01/03/2027', '06/03/2027', 'Penilaian Tengah Semester (PTS) Genap', 'ujian'],
            [8, 'Senin - Sabtu', '07/06/2027', '19/06/2027', 'Penilaian Akhir Tahun (PAT) / Asesmen Akhir', 'ujian'],
            [9, 'Sabtu', '26/06/2027', '', 'Pembagian Rapor Semester Genap / Kenaikan Kelas', 'kegiatan'],
            [10, 'Senin - Sabtu', '28/06/2027', '10/07/2027', 'Libur Kenaikan Kelas & Akhir Tahun Pelajaran', 'libur'],
        ];

        $rowNum = 4;
        foreach ($sampleData as $row) {
            $sheet->setCellValue('A'.$rowNum, $row[0]);
            $sheet->setCellValueExplicit('B'.$rowNum, (string) $row[1], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) $row[2], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D'.$rowNum, (string) $row[3], DataType::TYPE_STRING);
            $sheet->setCellValue('E'.$rowNum, $row[4]);
            $sheet->setCellValue('F'.$rowNum, $row[5]);
            $rowNum++;
        }

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D0D0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A4:F'.($rowNum - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A4:A'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B4:D'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F4:F'.($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Lebar Kolom
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(26);
        $sheet->getColumnDimension('E')->setWidth(55);
        $sheet->getColumnDimension('F')->setWidth(22);

        $writer = new Xlsx($spreadsheet);
        $safeName = str_replace(['/', '\\'], '-', $academicYearName);
        $filename = 'template_kalender_pendidikan_'.$safeName.'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Memproses impor data kalender dari file Excel.
     * Mendukung format baru: B (Hari), C (Tgl Mulai), D (Tgl Selesai), E (Keterangan), F (Kategori).
     * Serta mendukung fallback ke format lama B (Tanggal), C (Hari), D (Keterangan), E (Kategori).
     *
     * @return array{inserted: int, mode: string}
     */
    public function import(UploadedFile $file, string $academicYearName, string $mode = 'append'): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Deteksi format kolom berdasarkan baris header
        $headerRow = 3;
        $isSeparateDatesFormat = true; // default ke format baru (Cell C: Mulai, Cell D: Selesai)

        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $valB = strtolower((string) $sheet->getCell('B'.$r)->getValue());
            $valC = strtolower((string) $sheet->getCell('C'.$r)->getValue());
            $valD = strtolower((string) $sheet->getCell('D'.$r)->getValue());
            $valE = strtolower((string) $sheet->getCell('E'.$r)->getValue());

            if (str_contains($valC, 'mulai') || str_contains($valD, 'selesai')) {
                $headerRow = $r;
                $isSeparateDatesFormat = true;
                break;
            }

            if (str_contains($valB, 'tanggal') && str_contains($valD, 'keterangan')) {
                $headerRow = $r;
                $isSeparateDatesFormat = false;
                break;
            }
        }

        if ($mode === 'replace') {
            AcademicCalendar::where('academic_year_name', $academicYearName)->delete();
        }

        $insertedCount = 0;

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            if ($isSeparateDatesFormat) {
                // Format Baru: B (Hari), C (Tgl Mulai), D (Tgl Selesai), E (Keterangan), F (Kategori)
                $rawDay = trim((string) $sheet->getCell('B'.$row)->getValue());
                $rawStartDate = trim((string) $sheet->getCell('C'.$row)->getValue());
                $rawEndDate = trim((string) $sheet->getCell('D'.$row)->getValue());
                $description = trim((string) $sheet->getCell('E'.$row)->getValue());
                $category = strtolower(trim((string) $sheet->getCell('F'.$row)->getValue()));

                if (empty($rawStartDate) && empty($description)) {
                    continue;
                }

                if (empty($description)) {
                    continue;
                }

                // Parsing Tanggal Mulai
                $startDate = $this->parseSingleDate($rawStartDate, $sheet->getCell('C'.$row));
                if (! $startDate) {
                    continue;
                }

                // Parsing Tanggal Selesai (jika ada)
                $endDate = ! empty($rawEndDate) ? $this->parseSingleDate($rawEndDate, $sheet->getCell('D'.$row)) : null;

                // Jika tanggal selesai sama dengan tanggal mulai, jadikan null
                if ($endDate && $endDate->format('Y-m-d') === $startDate->format('Y-m-d')) {
                    $endDate = null;
                }
            } else {
                // Fallback Format Lama: B (Tanggal/Rentang), C (Hari), D (Keterangan), E (Kategori)
                $rawDate = trim((string) $sheet->getCell('B'.$row)->getValue());
                $rawDay = trim((string) $sheet->getCell('C'.$row)->getValue());
                $description = trim((string) $sheet->getCell('D'.$row)->getValue());
                $category = strtolower(trim((string) $sheet->getCell('E'.$row)->getValue()));

                if (empty($rawDate) && empty($description)) {
                    continue;
                }

                if (empty($description)) {
                    continue;
                }

                $parsedDate = $this->parseDateOrRange($rawDate, $sheet->getCell('B'.$row));
                if (! $parsedDate) {
                    continue;
                }

                [$startDate, $endDate] = $parsedDate;
            }

            // Hitung nama hari jika kosong
            $dayName = $rawDay ?: $this->resolveDayName($startDate, $endDate);

            // Tentukan semester otomatis berdasarkan tanggal dan tahun ajaran
            $semester = $this->resolveSemester($startDate, $academicYearName);

            // Sanitasi kategori
            $validCategories = ['kegiatan', 'libur', 'ujian', 'rapat', 'umum'];
            $sanitizedCategory = in_array($category, $validCategories, true) ? $category : 'kegiatan';

            AcademicCalendar::create([
                'academic_year_name' => $academicYearName,
                'semester' => $semester,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : null,
                'day_name' => $dayName,
                'description' => $description,
                'category' => $sanitizedCategory,
            ]);

            $insertedCount++;
        }

        return [
            'inserted' => $insertedCount,
            'mode' => $mode,
        ];
    }

    /**
     * Parse string tanggal tunggal atau rentang tanggal.
     *
     * @return array{0: Carbon, 1: ?Carbon}|null
     */
    protected function parseDateOrRange(string $rawDate, ?Cell $cell = null): ?array
    {
        // Cek jika cell adalah nomor serial tanggal Excel
        if (is_numeric($rawDate) && (float) $rawDate > 1000) {
            try {
                $dt = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $rawDate));

                return [$dt, null];
            } catch (\Throwable) {
                // Abaikan jika gagal
            }
        }

        // Cek rentang tanggal pemisah "-" atau "s/d" atau "sampai"
        $separators = [' - ', ' s/d ', ' s.d. ', ' sampai '];
        foreach ($separators as $sep) {
            if (str_contains($rawDate, $sep)) {
                $parts = explode($sep, $rawDate, 2);
                $start = $this->parseSingleDate(trim($parts[0]));
                $end = $this->parseSingleDate(trim($parts[1]));

                if ($start) {
                    return [$start, $end ?: null];
                }
            }
        }

        // Tanggal tunggal biasa
        $single = $this->parseSingleDate($rawDate, $cell);

        return $single ? [$single, null] : null;
    }

    /**
     * Parse tanggal dari format string DD/MM/YYYY, YYYY-MM-DD, dsb atau serial number Excel.
     */
    protected function parseSingleDate(string $str, ?Cell $cell = null): ?Carbon
    {
        $str = trim($str);
        if (empty($str)) {
            return null;
        }

        // Cek jika numeric serial number Excel
        if (is_numeric($str) && (float) $str > 1000) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $str));
            } catch (\Throwable) {
                // Lanjut ke format string
            }
        }

        $formats = [
            'd/m/Y',
            'd-m-Y',
            'Y-m-d',
            'd.m.Y',
            'j/n/Y',
            'j-n-Y',
            'Y/m/d',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $str);
                if ($dt && $dt->format($fmt) === $str) {
                    return $dt;
                }
            } catch (\Throwable) {
                // Coba format berikutnya
            }
        }

        try {
            return Carbon::parse($str);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Hitung nama hari bahasa Indonesia (contoh: "Senin" atau "Senin - Rabu").
     */
    protected function resolveDayName(Carbon $startDate, ?Carbon $endDate = null): string
    {
        $startDay = $startDate->locale('id')->isoFormat('dddd');

        if ($endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')) {
            $endDay = $endDate->locale('id')->isoFormat('dddd');

            return "{$startDay} - {$endDay}";
        }

        return $startDay;
    }

    /**
     * Tentukan semester (ganjil / genap) berdasarkan tanggal.
     * Periode Ganjil: Juli - Desember (bulan 7-12).
     * Periode Genap: Januari - Juli (bulan 1-7 di paruh kedua tahun ajaran).
     */
    protected function resolveSemester(Carbon $date, string $academicYearName): string
    {
        $month = (int) $date->format('m');
        $year = (int) $date->format('Y');

        // Pecah tahun ajaran, misal "2026/2027" -> 2026 dan 2027
        $years = explode('/', $academicYearName);
        $startYear = isset($years[0]) && is_numeric($years[0]) ? (int) $years[0] : null;

        if ($month >= 8 && $month <= 12) {
            return 'ganjil';
        }

        if ($month >= 1 && $month <= 6) {
            return 'genap';
        }

        // Bulan 7 (Juli): jika tahun sama dengan start year maka ganjil, jika akhir maka genap
        if ($month === 7) {
            if ($startYear && $year === $startYear) {
                return 'ganjil';
            }

            return 'genap';
        }

        return 'ganjil';
    }
}
