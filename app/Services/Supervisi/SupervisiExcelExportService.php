<?php

namespace App\Services\Supervisi;

use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupervisiExcelExportService
{
    /**
     * Ekspor rekap jadwal & hasil supervisi (1 sheet).
     *
     * @param  Collection<int, SupervisiJadwal>  $jadwals
     */
    public function exportRekap(Collection $jadwals, string $filterLabel, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Supervisi');

        $schoolName = AttendanceSetting::query()->value('school_name') ?? 'Madrasah';

        $sheet->setCellValue('A1', 'LAPORAN REKAP HASIL SUPERVISI GURU');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', $schoolName.' — '.$filterLabel);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headerRow = 4;
        $headers = ['No', 'Tahun Ajaran', 'Nama Guru', 'NIP', 'Supervisor', 'Kelas / Mapel', 'Tanggal', 'Nilai Akhir', 'Predikat'];
        foreach ($headers as $idx => $label) {
            $col = chr(65 + $idx);
            $sheet->setCellValue($col.$headerRow, $label);
        }
        $this->styleHeaderRow($sheet, "A{$headerRow}:I{$headerRow}");

        $row = $headerRow + 1;
        foreach ($jadwals as $i => $jadwal) {
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", ($jadwal->academicYear?->name ?? '-').' ('.ucfirst($jadwal->academicYear?->semester ?? '').')');
            $sheet->setCellValue("C{$row}", $jadwal->teacher?->user?->name ?? '-');
            $sheet->setCellValue("D{$row}", $jadwal->teacher?->nip ?? '-');
            $sheet->setCellValue("E{$row}", $jadwal->supervisor_display_name);
            $sheet->setCellValue("F{$row}", ($jadwal->schoolClass?->name ?? '-').' / '.($jadwal->subject?->name ?? '-'));
            $sheet->setCellValue("G{$row}", $jadwal->tanggal?->format('d/m/Y') ?? '-');
            $sheet->setCellValue("H{$row}", $jadwal->nilai_akhir !== null ? number_format((float) $jadwal->nilai_akhir, 2) : '-');
            $sheet->setCellValue("I{$row}", $jadwal->ketercapaian ?? ucfirst((string) $jadwal->status));
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    /**
     * Ekspor detail penilaian 1 jadwal supervisi.
     */
    public function exportDetailJadwal(SupervisiJadwal $jadwal): StreamedResponse
    {
        $jadwal->loadMissing(['teacher.user', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'hasils.jenisPenilaian', 'hasils.detailHasils.aspek']);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Supervisi');

        $sheet->setCellValue('A1', 'DETAIL HASIL SUPERVISI GURU');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Nama Guru')->setCellValue('B3', $jadwal->teacher?->user?->name ?? '-');
        $sheet->setCellValue('A4', 'NIP')->setCellValue('B4', $jadwal->teacher?->nip ?? '-');
        $sheet->setCellValue('A5', 'Kelas / Mapel')->setCellValue('B5', ($jadwal->schoolClass?->name ?? '-').' / '.($jadwal->subject?->name ?? '-'));
        $sheet->setCellValue('A6', 'Tanggal')->setCellValue('B6', $jadwal->tanggal?->format('d/m/Y') ?? '-');
        $sheet->setCellValue('A7', 'Nilai Akhir')->setCellValue('B7', ($jadwal->nilai_akhir ?? 0).' ('.($jadwal->ketercapaian ?? '-').')');
        $sheet->getStyle('A3:A7')->getFont()->setBold(true);

        $row = 9;
        foreach ($jadwal->hasils as $hasil) {
            $sheet->setCellValue("A{$row}", ($hasil->jenisPenilaian?->nama ?? 'Komponen').' — Nilai: '.number_format((float) $hasil->nilai_akhir, 2).'%');
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            $sheet->setCellValue("A{$row}", 'No')->setCellValue("B{$row}", 'Aspek')->setCellValue("C{$row}", 'Skor')->setCellValue("D{$row}", 'Catatan');
            $this->styleHeaderRow($sheet, "A{$row}:D{$row}");
            $row++;

            foreach ($hasil->detailHasils as $idx => $detail) {
                $sheet->setCellValue("A{$row}", $idx + 1);
                $sheet->setCellValue("B{$row}", $detail->aspek?->nama_aspek ?? '-');
                $sheet->setCellValue("C{$row}", $detail->skor);
                $sheet->setCellValue("D{$row}", $detail->catatan ?? '-');
                $row++;
            }
            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '-', $jadwal->teacher?->user?->name ?? 'guru');

        return $this->streamSpreadsheet($spreadsheet, "detail-supervisi-{$safeName}-".now()->format('Y-m-d').'.xlsx');
    }

    /**
     * Ekspor multi-sheet: 1 worksheet per guru (porting BatchExcelController::exportAll).
     *
     * @param  Collection<int, SupervisiJadwal>  $jadwals
     */
    public function exportBatchMultiSheetPerGuru(Collection $jadwals, string $filename): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $grouped = $jadwals->groupBy('teacher_id');
        if ($grouped->isEmpty()) {
            $sheet = new Worksheet($spreadsheet, 'Kosong');
            $spreadsheet->addSheet($sheet);
            $sheet->setCellValue('A1', 'Belum ada data supervisi yang selesai.');

            return $this->streamSpreadsheet($spreadsheet, $filename);
        }

        foreach ($grouped as $teacherId => $items) {
            $first = $items->first();
            $teacherName = $first->teacher?->user?->name ?? "Guru {$teacherId}";
            $cleanTitle = trim(str_replace(['*', '?', '[', ']', ':', '\\', '/'], '', $teacherName));
            $sheetTitle = mb_substr($cleanTitle ?: "Guru_{$teacherId}", 0, 31);

            $sheet = new Worksheet($spreadsheet, $sheetTitle);
            $spreadsheet->addSheet($sheet);

            $sheet->setCellValue('A1', 'LAPORAN SUPERVISI: '.mb_strtoupper($teacherName));
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

            $row = 3;
            $sheet->setCellValue("A{$row}", 'No')
                ->setCellValue("B{$row}", 'Tanggal')
                ->setCellValue("C{$row}", 'Kelas / Mapel')
                ->setCellValue("D{$row}", 'Supervisor')
                ->setCellValue("E{$row}", 'Nilai Akhir')
                ->setCellValue("F{$row}", 'Predikat')
                ->setCellValue("G{$row}", 'Materi');
            $this->styleHeaderRow($sheet, "A{$row}:G{$row}");
            $row++;

            foreach ($items as $idx => $jadwal) {
                $sheet->setCellValue("A{$row}", $idx + 1);
                $sheet->setCellValue("B{$row}", $jadwal->tanggal?->format('d/m/Y') ?? '-');
                $sheet->setCellValue("C{$row}", ($jadwal->schoolClass?->name ?? '-').' / '.($jadwal->subject?->name ?? '-'));
                $sheet->setCellValue("D{$row}", $jadwal->supervisor_display_name);
                $sheet->setCellValue("E{$row}", $jadwal->nilai_akhir !== null ? number_format((float) $jadwal->nilai_akhir, 2) : '-');
                $sheet->setCellValue("F{$row}", $jadwal->ketercapaian ?? '-');
                $sheet->setCellValue("G{$row}", $jadwal->materi ?? '-');
                $row++;
            }

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $this->streamSpreadsheet($spreadsheet, $filename);
    }

    private function styleHeaderRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0F172A');
        $sheet->getStyle($range)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function streamSpreadsheet(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
