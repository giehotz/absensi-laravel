<?php

namespace App\Services\TeachingJournal;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
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

class TeachingJournalExcelService
{
    protected array $daysMap = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Unduh template Excel Jurnal Harian yang telah disesuaikan dengan jadwal mengajar guru.
     */
    public function downloadTemplate(Teacher $teacher): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Jurnal KBM');

        $teacherName = $teacher->user?->name ?? 'Guru';

        // 1. Judul Template
        $sheet->setCellValue('A1', 'TEMPLATE JURNAL HARIAN GURU MENGAJAR - '.mb_strtoupper($teacherName));
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->setName('Arial');
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // 2. Petunjuk Pengisian
        $instruction = "Petunjuk Pengisian:\n"
            ."1. Baris jadwal aktif Anda tercantum di bawah. Kolom B s.d. F adalah referensi jadwal (Jangan ubah isi Kolom B).\n"
            ."2. Isi Kolom G (Tanggal KBM format dd/mm/yyyy), H (Pertemuan Ke-), I (Tujuan Pembelajaran), J (Kegiatan KBM), dan K (Permasalahan KBM jika ada).\n"
            .'3. Anda dapat menyalin/menduplikasi baris jadwal yang sama ke baris baru untuk tanggal KBM berikutnya.';

        $sheet->setCellValue('A2', $instruction);
        $sheet->mergeCells('A2:K2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new Color('444444'));
        $sheet->getStyle('A2')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(48);

        // 3. Header Tabel
        $headers = [
            'A3' => 'No',
            'B3' => 'ID Jadwal',
            'C3' => 'Hari',
            'D3' => 'Jam KBM',
            'E3' => 'Kelas',
            'F3' => 'Mata Pelajaran',
            'G3' => 'Tanggal KBM (dd/mm/yyyy)',
            'H3' => 'Pertemuan Ke-',
            'I3' => 'Tujuan Pembelajaran',
            'J3' => 'Kegiatan Belajar Mengajar',
            'K3' => 'Permasalahan KBM (Opsional)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '000000']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'], // Kuning Neobrutalisme
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
        $sheet->getStyle('A3:K3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // 4. Baris Data Awal Berdasarkan Jadwal Guru
        $schedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $row = 4;
        $no = 1;

        if ($schedules->isNotEmpty()) {
            foreach ($schedules as $sch) {
                $dayName = $this->daysMap[$sch->day_of_week] ?? ('Hari '.$sch->day_of_week);
                $timeRange = substr($sch->start_time, 0, 5).' - '.substr($sch->end_time, 0, 5);

                $sheet->setCellValue('A'.$row, $no);
                $sheet->setCellValueExplicit('B'.$row, (string) $sch->id, DataType::TYPE_STRING);
                $sheet->setCellValue('C'.$row, $dayName);
                $sheet->setCellValue('D'.$row, $timeRange);
                $sheet->setCellValue('E'.$row, $sch->schoolClass?->name ?? '-');
                $sheet->setCellValue('F'.$row, $sch->subject?->name ?? '-');
                $sheet->setCellValue('G'.$row, Carbon::today()->format('d/m/Y'));
                $sheet->setCellValue('H'.$row, 1);
                $sheet->setCellValue('I'.$row, 'Peserta didik memahami materi pokok pembelajaran.');
                $sheet->setCellValue('J'.$row, '1. Pembukaan dan apersepsi. 2. Pembahasan materi & diskusi. 3. Evaluasi & penutup.');
                $sheet->setCellValue('K'.$row, '');

                $row++;
                $no++;
            }
        } else {
            // Contoh baris default jika guru belum memiliki jadwal terdaftar
            $sheet->setCellValue('A'.$row, 1);
            $sheet->setCellValue('B'.$row, '');
            $sheet->setCellValue('C'.$row, 'Senin');
            $sheet->setCellValue('D'.$row, '07:30 - 09:00');
            $sheet->setCellValue('E'.$row, 'VII-A');
            $sheet->setCellValue('F'.$row, 'Matematika');
            $sheet->setCellValue('G'.$row, Carbon::today()->format('d/m/Y'));
            $sheet->setCellValue('H'.$row, 1);
            $sheet->setCellValue('I'.$row, 'Peserta didik mampu memahami konsep dasar materi...');
            $sheet->setCellValue('J'.$row, 'Apersepsi, penyampaian materi, tanya jawab, dan latihan soal.');
            $sheet->setCellValue('K'.$row, '');
            $row++;
        }

        // Styling data cells
        $dataStyle = [
            'font' => ['size' => 9],
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
        $sheet->getStyle('A4:K'.($row - 1))->applyFromArray($dataStyle);
        $sheet->getStyle('A4:B'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C4:D'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G4:H'.($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto width
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'template-jurnal-kbm-'.preg_replace('/[^A-Za-z0-9_-]/', '', strtolower($teacherName)).'.xlsx';

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
     * Parse uploaded Excel file into reviewable rows.
     */
    public function parseExcel(UploadedFile $file, Teacher $teacher): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $rows = [];
        $teacherSchedules = Schedule::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get()
            ->keyBy('id');

        $allClasses = SchoolClass::all()->keyBy(fn ($c) => mb_strtolower(trim($c->name)));
        $allSubjects = Subject::all()->keyBy(fn ($s) => mb_strtolower(trim($s->name)));

        for ($r = 4; $r <= $highestRow; $r++) {
            $scheduleIdRaw = trim((string) $sheet->getCell('B'.$r)->getValue());
            $dayRaw = trim((string) $sheet->getCell('C'.$r)->getValue());
            $timeRaw = trim((string) $sheet->getCell('D'.$r)->getValue());
            $classNameRaw = trim((string) $sheet->getCell('E'.$r)->getValue());
            $subjectNameRaw = trim((string) $sheet->getCell('F'.$r)->getValue());
            $dateCell = $sheet->getCell('G'.$r);
            $meetingRaw = trim((string) $sheet->getCell('H'.$r)->getValue());
            $objectiveRaw = trim((string) $sheet->getCell('I'.$r)->getValue());
            $activityRaw = trim((string) $sheet->getCell('J'.$r)->getValue());
            $problemRaw = trim((string) $sheet->getCell('K'.$r)->getValue());

            // Skip completely empty rows
            if (empty($classNameRaw) && empty($subjectNameRaw) && empty($objectiveRaw) && empty($dateCell->getValue())) {
                continue;
            }

            // 1. Resolve Date
            $parsedDate = null;
            $cellVal = $dateCell->getValue();
            if (is_numeric($cellVal) && ExcelDate::isDateTime($dateCell)) {
                $parsedDate = Carbon::instance(ExcelDate::excelToDateTimeObject($cellVal))->format('Y-m-d');
            } elseif (! empty($cellVal)) {
                $cleanStr = trim((string) $cellVal);
                foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'] as $fmt) {
                    try {
                        $parsedDate = Carbon::createFromFormat($fmt, $cleanStr)->format('Y-m-d');
                        break;
                    } catch (\Exception $e) {
                        // ignore and try next
                    }
                }
            }

            // 2. Resolve Schedule / Class / Subject
            $matchedSchedule = null;
            $classId = null;
            $subjectId = null;
            $displayClass = $classNameRaw;
            $displaySubject = $subjectNameRaw;

            if (! empty($scheduleIdRaw) && isset($teacherSchedules[$scheduleIdRaw])) {
                $matchedSchedule = $teacherSchedules[$scheduleIdRaw];
                $classId = $matchedSchedule->school_class_id;
                $subjectId = $matchedSchedule->subject_id;
                $displayClass = $matchedSchedule->schoolClass?->name ?? $classNameRaw;
                $displaySubject = $matchedSchedule->subject?->name ?? $subjectNameRaw;
            } else {
                $classObj = $allClasses->get(mb_strtolower($classNameRaw));
                $subjectObj = $allSubjects->get(mb_strtolower($subjectNameRaw));

                if ($classObj) {
                    $classId = $classObj->id;
                    $displayClass = $classObj->name;
                }
                if ($subjectObj) {
                    $subjectId = $subjectObj->id;
                    $displaySubject = $subjectObj->name;
                }
            }

            // 3. Validation
            $errors = [];
            if (! $parsedDate) {
                $errors[] = 'Format tanggal KBM tidak valid (gunakan dd/mm/yyyy).';
            }
            if (! $classId) {
                $errors[] = 'Kelas "'.$classNameRaw.'" tidak ditemukan di sistem.';
            }
            if (! $subjectId) {
                $errors[] = 'Mata pelajaran "'.$subjectNameRaw.'" tidak ditemukan di sistem.';
            }
            if (empty($objectiveRaw)) {
                $errors[] = 'Tujuan Pembelajaran wajib diisi.';
            }
            if (empty($activityRaw)) {
                $errors[] = 'Kegiatan KBM wajib diisi.';
            }

            // 4. Check Prerequisite Attendance Status
            $hasAttendance = false;
            if ($parsedDate && $classId) {
                $hasAttendance = Attendance::whereDate('date', $parsedDate)
                    ->whereHas('student', fn ($q) => $q->where('school_class_id', $classId))
                    ->exists();
            }

            $meetingNumber = (int) $meetingRaw;
            if ($meetingNumber <= 0) {
                $meetingNumber = 1;
            }

            $rows[] = [
                'row_index' => $r,
                'schedule_id' => $matchedSchedule?->id,
                'school_class_id' => $classId,
                'subject_id' => $subjectId,
                'class_name' => $displayClass,
                'subject_name' => $displaySubject,
                'date' => $parsedDate ?? Carbon::today()->format('Y-m-d'),
                'date_formatted' => $parsedDate ? Carbon::parse($parsedDate)->format('d/m/Y') : (string) $cellVal,
                'day_name' => $parsedDate ? ($this->daysMap[Carbon::parse($parsedDate)->dayOfWeekIso] ?? 'Senin') : $dayRaw,
                'time_range' => $timeRaw,
                'meeting_number' => $meetingNumber,
                'learning_objective' => $objectiveRaw,
                'teaching_activity' => $activityRaw,
                'teaching_problem' => $problemRaw,
                'is_valid' => empty($errors),
                'errors' => $errors,
                'has_attendance' => $hasAttendance,
            ];
        }

        return $rows;
    }
}
