<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeriodeKelompok;
use App\Models\Ujian\UjianSesi;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JadwalExcelTemplateService
{
    public function __construct(
        protected UjianKalenderService $kalenderService
    ) {}

    /**
     * Membangun template legacy 5 kolom untuk kompatibilitas ke belakang.
     */
    public function buildLegacy(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Ujian');

        $headers = [
            'A1' => 'No',
            'B1' => 'Matapelajaran',
            'C1' => 'tanggal(dd-mm-yyyy)',
            'D1' => 'sesi',
            'E1' => 'Kelas',
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
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $subjects = Subject::orderBy('name')->pluck('name')->toArray();
        $sesis = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get();

        $sampleDate = now()->addDays(7)->format('d-m-Y');
        $sampleRows = [
            [1, $subjects[0] ?? "Al-Qur'an Hadis", $sampleDate, 1, '123'],
            [2, $subjects[1] ?? 'Akidah Akhlak', $sampleDate, 2, '456'],
            [3, $subjects[2] ?? 'Bahasa Indonesia', now()->addDays(8)->format('d-m-Y'), 1, '123456'],
        ];

        $rowIdx = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue("A{$rowIdx}", $row[0]);
            $sheet->setCellValue("B{$rowIdx}", $row[1]);
            $sheet->setCellValue("C{$rowIdx}", $row[2]);
            $sheet->setCellValue("D{$rowIdx}", $row[3]);
            $sheet->setCellValue("E{$rowIdx}", $row[4]);
            $rowIdx++;
        }

        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle("A2:E{$rowIdx}")->applyFromArray($dataStyle);
        $sheet->getStyle("A2:A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C2:E{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setCellValue('A1', 'Daftar Mata Pelajaran');
        $refSheet->setCellValue('B1', 'Daftar Sesi Ujian');
        $refSheet->setCellValue('C1', 'Petunjuk Format Kelas / Tingkat');
        $refSheet->getStyle('A1:C1')->getFont()->setBold(true);

        $refRow = 2;
        foreach ($subjects as $sName) {
            $refSheet->setCellValue("A{$refRow}", $sName);
            $refRow++;
        }
        $maxSubjectRow = max(2, $refRow - 1);

        $sesiRow = 2;
        foreach ($sesis as $s) {
            $refSheet->setCellValue("B{$sesiRow}", "Sesi {$s->sesi_ke} (".substr($s->jam_mulai, 0, 5).' - '.substr($s->jam_selesai, 0, 5).')');
            $sesiRow++;
        }

        $petunjuk = [
            '123 : Tingkat 1, 2, 3 (Kelas Rendah)',
            '456 : Tingkat 4, 5, 6 (Kelas Atas)',
            '123456 : Semua Tingkat (Kelas 1 s/d 6)',
            '1, 2, 3 : Format koma juga didukung',
            '6A, 6B : Nama rombel spesifik',
        ];
        foreach ($petunjuk as $pIdx => $pText) {
            $refSheet->setCellValue('C'.($pIdx + 2), $pText);
        }

        foreach (range('A', 'C') as $col) {
            $refSheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (! empty($subjects)) {
            for ($r = 2; $r <= 100; $r++) {
                $validation = $sheet->getCell("B{$r}")->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Pilihan Mata Pelajaran');
                $validation->setError('Pilih mata pelajaran dari daftar atau ketik nama mapel.');
                $validation->setFormula1("Referensi!\$A\$2:\$A\${$maxSubjectRow}");

                $validationSesi = $sheet->getCell("D{$r}")->getDataValidation();
                $validationSesi->setType(DataValidation::TYPE_LIST);
                $validationSesi->setErrorStyle(DataValidation::STYLE_STOP);
                $validationSesi->setShowDropDown(true);
                $validationSesi->setFormula1('"1, 2, 3, 4"');

                $validationKelas = $sheet->getCell("E{$r}")->getDataValidation();
                $validationKelas->setType(DataValidation::TYPE_LIST);
                $validationKelas->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validationKelas->setShowDropDown(true);
                $validationKelas->setFormula1('"123, 456, 123456, 1, 2, 3, 4, 5, 6"');
            }
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Membangun template Excel v2 berdasarkan kelompok (bawah atau atas).
     */
    public function build(
        UjianPeriode $periode,
        string $kelompok,
        ?UjianPeriodeKelompok $config = null
    ): Spreadsheet {
        $configSource = 'saved';

        if (! $config) {
            $configSource = 'periode_fallback';
            $tMulai = $periode->tanggal_mulai ?: now()->addDays(7);
            $tSelesai = $periode->tanggal_selesai ?: now()->addDays(13);

            $config = new UjianPeriodeKelompok([
                'ujian_periode_id' => $periode->id,
                'kelompok' => $kelompok,
                'tingkat_list' => $kelompok === 'bawah' ? ['1', '2'] : ['3', '4', '5', '6'],
                'mode_jadwal' => $kelompok === 'bawah' ? 'per_rombel' : 'per_tingkat',
                'tanggal_mulai' => $tMulai,
                'tanggal_selesai' => $tSelesai,
                'kecualikan_minggu' => true,
                'tanggal_libur' => [],
                'jumlah_sesi_per_hari' => 2,
                'sesi_hari_terakhir' => 1,
            ]);
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal Ujian');

        $slots = $this->kalenderService->buildSlots($config);
        $subjects = Subject::orderBy('name')->pluck('name')->toArray();
        $sesis = UjianSesi::where('is_active', true)->orderBy('sesi_ke')->get();

        $allClasses = SchoolClass::ordered()->get();
        $kelasBawah = $allClasses->filter(fn ($c) => in_array(ClassLabelResolver::levelFromName($c->name), ['1', '2']))->values();

        // 1. Setup Sheet Referensi
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setCellValue('A1', 'Daftar Mata Pelajaran');
        $refSheet->setCellValue('B1', 'Daftar Sesi Ujian');
        $refSheet->setCellValue('C1', 'Petunjuk Pengisian');
        $refSheet->setCellValue('D1', 'Daftar Rombel');
        $refSheet->setCellValue('E1', 'Jenis Soal');
        $refSheet->getStyle('A1:E1')->getFont()->setBold(true);

        $refRow = 2;
        foreach ($subjects as $sName) {
            $refSheet->setCellValue("A{$refRow}", $sName);
            $refRow++;
        }
        $maxSubjectRow = max(2, $refRow - 1);

        $sesiRow = 2;
        foreach ($sesis as $s) {
            $refSheet->setCellValue("B{$sesiRow}", "Sesi {$s->sesi_ke} (".substr($s->jam_mulai, 0, 5).' - '.substr($s->jam_selesai, 0, 5).')');
            $sesiRow++;
        }

        $petunjuk = [
            '1. Kolom abu-abu (No, Hari, Tanggal, Sesi, Jam) terisi otomatis dari kalender.',
            '2. Pilih mata pelajaran dari dropdown pilihan atau ketik nama mapel.',
            '3. Format tanggal adalah YYYY-MM-DD.',
            '4. Jangan mengubah sheet _Meta yang tersembunyi agar file dikenali saat import.',
        ];
        foreach ($petunjuk as $pIdx => $pText) {
            $refSheet->setCellValue('C'.($pIdx + 2), $pText);
        }

        $rombelRow = 2;
        foreach ($allClasses as $c) {
            $refSheet->setCellValue("D{$rombelRow}", ClassLabelResolver::displayLabel($c->name)." ({$c->name})");
            $rombelRow++;
        }

        $jenisSoalList = ['Tertulis', 'Praktik', 'Lisan'];
        foreach ($jenisSoalList as $jIdx => $jVal) {
            $refSheet->setCellValue('E'.($jIdx + 2), $jVal);
        }

        foreach (range('A', 'E') as $cCol) {
            $refSheet->getColumnDimension($cCol)->setAutoSize(true);
        }

        // 2. Setup Data di Sheet Utama (Jadwal Ujian)
        if ($kelompok === 'bawah') {
            // Layout Bawah: Per Rombel
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Hari');
            $sheet->setCellValue('C1', 'Tanggal');
            $sheet->setCellValue('D1', 'Sesi');
            $sheet->setCellValue('E1', 'Jam');

            $colIndex = 6; // Mulai dari kolom F
            $rombelColumns = [];
            foreach ($kelasBawah as $kelas) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $label = ClassLabelResolver::displayLabel($kelas->name);
                $sheet->setCellValue("{$colLetter}1", $label);
                $rombelColumns[$colLetter] = $kelas;
                $colIndex++;
            }
            $lastColLetter = Coordinate::stringFromColumnIndex(max(5, $colIndex - 1));

            // Isi Data Baris Slot
            $rowNum = 2;
            foreach ($slots as $idx => $slot) {
                $sheet->setCellValue("A{$rowNum}", $idx + 1);
                $sheet->setCellValue("B{$rowNum}", $slot['nama_hari']);
                $sheet->setCellValue("C{$rowNum}", $slot['tanggal']);
                $sheet->setCellValue("D{$rowNum}", $slot['sesi_ke']);
                $sheet->setCellValue("E{$rowNum}", "{$slot['jam_mulai']} - {$slot['jam_selesai']}");

                // Dropdown Mapel di setiap kolom rombel
                foreach (array_keys($rombelColumns) as $colLetter) {
                    if (! empty($subjects)) {
                        $validation = $sheet->getCell("{$colLetter}{$rowNum}")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setErrorTitle('Pilihan Mata Pelajaran');
                        $validation->setError('Pilih mata pelajaran dari daftar atau ketik nama mapel.');
                        $validation->setFormula1("Referensi!\$A\$2:\$A\${$maxSubjectRow}");
                    }
                }
                $rowNum++;
            }
            $lastRow = max(2, $rowNum - 1);

            // Styling
            $this->applyHeaderStyle($sheet, "A1:{$lastColLetter}1");
            $this->applyDataBorders($sheet, "A2:{$lastColLetter}{$lastRow}");

            // Kolom A-E latar abu-abu muda
            $sheet->getStyle("A2:E{$lastRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E9ECEF');
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Kolom F+ latar putih
            if ($colIndex > 6) {
                $firstRombelCol = 'F';
                $sheet->getStyle("{$firstRombelCol}2:{$lastColLetter}{$lastRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFFFF');
            }

            for ($c = 1; $c <= $colIndex - 1; $c++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }
        } else {
            // Layout Atas: Seragam Tingkat
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Hari');
            $sheet->setCellValue('C1', 'Tanggal');
            $sheet->setCellValue('D1', 'Sesi');
            $sheet->setCellValue('E1', 'Jam');
            $sheet->setCellValue('F1', 'Mata Pelajaran');
            $sheet->setCellValue('G1', 'Target Tingkat');
            $sheet->setCellValue('H1', 'Jenis Soal');
            $lastColLetter = 'H';

            $rowNum = 2;
            foreach ($slots as $idx => $slot) {
                $sheet->setCellValue("A{$rowNum}", $idx + 1);
                $sheet->setCellValue("B{$rowNum}", $slot['nama_hari']);
                $sheet->setCellValue("C{$rowNum}", $slot['tanggal']);
                $sheet->setCellValue("D{$rowNum}", $slot['sesi_ke']);
                $sheet->setCellValue("E{$rowNum}", "{$slot['jam_mulai']} - {$slot['jam_selesai']}");
                $sheet->setCellValue("G{$rowNum}", '3, 4, 5, 6');
                $sheet->setCellValue("H{$rowNum}", 'Tertulis');

                // Dropdown Mapel di Kolom F
                if (! empty($subjects)) {
                    $valMapel = $sheet->getCell("F{$rowNum}")->getDataValidation();
                    $valMapel->setType(DataValidation::TYPE_LIST);
                    $valMapel->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $valMapel->setAllowBlank(true);
                    $valMapel->setShowDropDown(true);
                    $valMapel->setErrorTitle('Pilihan Mata Pelajaran');
                    $valMapel->setError('Pilih mata pelajaran dari daftar.');
                    $valMapel->setFormula1("Referensi!\$A\$2:\$A\${$maxSubjectRow}");
                }

                // Dropdown Jenis Soal di Kolom H
                $valJenis = $sheet->getCell("H{$rowNum}")->getDataValidation();
                $valJenis->setType(DataValidation::TYPE_LIST);
                $valJenis->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $valJenis->setShowDropDown(true);
                $valJenis->setFormula1('Referensi!$E$2:$E$4');

                $rowNum++;
            }
            $lastRow = max(2, $rowNum - 1);

            $this->applyHeaderStyle($sheet, "A1:{$lastColLetter}1");
            $this->applyDataBorders($sheet, "A2:{$lastColLetter}{$lastRow}");

            // Kolom A-E abu-abu, F-H putih
            $sheet->getStyle("A2:E{$lastRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E9ECEF');
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C2:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle("F2:H{$lastRow}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFFFFF');

            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        $sheet->freezePane('A2');

        // 3. Sheet _Meta Tersembunyi
        $metaSheet = $spreadsheet->createSheet();
        $metaSheet->setTitle('_Meta');
        $metaSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $metaData = [
            ['template_version', 'v2'],
            ['kelompok', $kelompok],
            ['mode_jadwal', $config->mode_jadwal ?? ($kelompok === 'bawah' ? 'per_rombel' : 'per_tingkat')],
            ['ujian_periode_id', (string) $periode->id],
            ['ujian_periode_nama', $periode->nama_periode],
            ['tingkat_list', json_encode($config->tingkat_list ?? ($kelompok === 'bawah' ? ['1', '2'] : ['3', '4', '5', '6']))],
            ['generated_at', now()->toIso8601String()],
            ['config_source', $configSource],
        ];

        foreach ($metaData as $mIdx => $mRow) {
            $metaSheet->setCellValue('A'.($mIdx + 1), $mRow[0]);
            $metaSheet->setCellValue('B'.($mIdx + 1), $mRow[1]);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function filename(UjianPeriode $periode, string $kelompok): string
    {
        return "template_jadwal_ujian_{$kelompok}_{$periode->id}.xlsx";
    }

    protected function applyHeaderStyle(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD43B'], // Kuning Neo-Brutalist
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
    }

    protected function applyDataBorders(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }
}
