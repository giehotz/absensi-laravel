<?php

namespace App\Services;

use App\Models\MbgDistribution;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MbgReportService
{
    /**
     * Tentukan rentang tanggal dan label periode berdasarkan parameter.
     * Mendukung preset, rentang bulan bebas (from_month, to_month, year), atau tanggal kustom.
     *
     * @return array{start_date: Carbon, end_date: Carbon, period: string, period_label: string, next_period_label: string}
     */
    public function resolveDateRange(
        string $period = 'this_month',
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $fromMonth = null,
        ?int $toMonth = null,
        ?int $year = null
    ): array {
        $now = Carbon::now();
        $targetYear = $year ?: $now->year;

        // Jika diberikan parameter bulan awal dan bulan akhir (rentang bulan bebas)
        if ($fromMonth !== null && $toMonth !== null) {
            $from = max(1, min(12, $fromMonth));
            $to = max(1, min(12, $toMonth));

            if ($from > $to) {
                [$from, $to] = [$to, $from];
            }

            $start = Carbon::create($targetYear, $from, 1)->startOfDay();
            $end = Carbon::create($targetYear, $to, 1)->endOfMonth()->endOfDay();

            // Deteksi label dan RTL periode berikutnya
            if ($from === 1 && $to === 3) {
                $label = "Triwulan I (Januari – Maret {$targetYear})";
                $nextLabel = "Triwulan II (April – Juni {$targetYear})";
            } elseif ($from === 4 && $to === 6) {
                $label = "Triwulan II (April – Juni {$targetYear})";
                $nextLabel = "Triwulan III (Juli – September {$targetYear})";
            } elseif ($from === 7 && $to === 9) {
                $label = "Triwulan III (Juli – September {$targetYear})";
                $nextLabel = "Triwulan IV (Oktober – Desember {$targetYear})";
            } elseif ($from === 10 && $to === 12) {
                $label = "Triwulan IV (Oktober – Desember {$targetYear})";
                $nextLabel = 'Triwulan I (Januari – Maret '.($targetYear + 1).')';
            } elseif ($from === 1 && $to === 6) {
                $label = "Semester I (Januari – Juni {$targetYear})";
                $nextLabel = "Semester II (Juli – Desember {$targetYear})";
            } elseif ($from === 7 && $to === 12) {
                $label = "Semester II (Juli – Desember {$targetYear})";
                $nextLabel = 'Semester I (Januari – Juni '.($targetYear + 1).')';
            } elseif ($from === $to) {
                $label = 'Bulan '.$start->translatedFormat('F Y');
                $nextLabel = 'Bulan '.$start->copy()->addMonth()->translatedFormat('F Y');
            } else {
                $label = $start->translatedFormat('F').' – '.$end->translatedFormat('F Y');
                $nextStart = $end->copy()->addDay();
                $nextLabel = 'Bulan '.$nextStart->translatedFormat('F Y');
            }

            return [
                'start_date' => $start,
                'end_date' => $end,
                'period' => 'month_range',
                'period_label' => $label,
                'next_period_label' => $nextLabel,
            ];
        }

        return match ($period) {
            'last_month' => [
                'start_date' => $now->copy()->subMonth()->startOfMonth(),
                'end_date' => $now->copy()->subMonth()->endOfMonth(),
                'period' => 'last_month',
                'period_label' => 'Bulan Lalu ('.$now->copy()->subMonth()->translatedFormat('F Y').')',
                'next_period_label' => 'Bulan Ini ('.$now->translatedFormat('F Y').')',
            ],
            'triwulan_1' => [
                'start_date' => Carbon::create($now->year, 1, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 3, 31)->endOfDay(),
                'period' => 'triwulan_1',
                'period_label' => 'Triwulan I (Januari – Maret '.$now->year.')',
                'next_period_label' => 'Triwulan II (April – Juni '.$now->year.')',
            ],
            'triwulan_2' => [
                'start_date' => Carbon::create($now->year, 4, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 6, 30)->endOfDay(),
                'period' => 'triwulan_2',
                'period_label' => 'Triwulan II (April – Juni '.$now->year.')',
                'next_period_label' => 'Triwulan III (Juli – September '.$now->year.')',
            ],
            'triwulan_3' => [
                'start_date' => Carbon::create($now->year, 7, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 9, 30)->endOfDay(),
                'period' => 'triwulan_3',
                'period_label' => 'Triwulan III (Juli – September '.$now->year.')',
                'next_period_label' => 'Triwulan IV (Oktober – Desember '.$now->year.')',
            ],
            'triwulan_4' => [
                'start_date' => Carbon::create($now->year, 10, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 12, 31)->endOfDay(),
                'period' => 'triwulan_4',
                'period_label' => 'Triwulan IV (Oktober – Desember '.$now->year.')',
                'next_period_label' => 'Triwulan I (Januari – Maret '.($now->year + 1).')',
            ],
            'semester_1' => [
                'start_date' => Carbon::create($now->year, 7, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 12, 31)->endOfDay(),
                'period' => 'semester_1',
                'period_label' => 'Semester Ganjil (Juli – Desember '.$now->year.')',
                'next_period_label' => 'Semester Genap (Januari – Juni '.($now->year + 1).')',
            ],
            'semester_2' => [
                'start_date' => Carbon::create($now->year, 1, 1)->startOfDay(),
                'end_date' => Carbon::create($now->year, 6, 30)->endOfDay(),
                'period' => 'semester_2',
                'period_label' => 'Semester Genap (Januari – Juni '.$now->year.')',
                'next_period_label' => 'Semester Ganjil (Juli – Desember '.$now->year.')',
            ],
            'custom' => [
                'start_date' => $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                'end_date' => $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfMonth(),
                'period' => 'custom',
                'period_label' => 'Rentang Kustom ('.($startDate ? Carbon::parse($startDate)->format('d/m/Y') : '').' – '.($endDate ? Carbon::parse($endDate)->format('d/m/Y') : '').')',
                'next_period_label' => 'Periode Lanjutan',
            ],
            default => [
                'start_date' => $now->copy()->startOfMonth(),
                'end_date' => $now->copy()->endOfMonth(),
                'period' => 'this_month',
                'period_label' => 'Bulan Ini ('.$now->translatedFormat('F Y').')',
                'next_period_label' => 'Bulan Depan ('.$now->copy()->addMonth()->translatedFormat('F Y').')',
            ],
        };
    }

    /**
     * Kalkulasi statistik ringkasan dalam rentang tanggal.
     *
     * @return array{
     *     total_days: int,
     *     target_students: int,
     *     distributed_students: int,
     *     target_teachers: int,
     *     distributed_teachers: int,
     *     total_target: int,
     *     total_distributed: int,
     *     total_leftovers: int,
     *     avg_daily_distributed: float,
     *     success_rate: float
     * }
     */
    public function getSummaryStats(Carbon $from, Carbon $to): array
    {
        $distributions = MbgDistribution::whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])->get();

        $totalDays = $distributions->count();
        $targetStudents = (int) $distributions->sum('target_students');
        $distStudents = (int) $distributions->sum('distributed_students');
        $targetTeachers = (int) $distributions->sum('target_teachers');
        $distTeachers = (int) $distributions->sum('distributed_teachers');
        $totalTarget = $targetStudents + $targetTeachers;
        $totalDistributed = $distStudents + $distTeachers;
        $totalLeftovers = (int) $distributions->sum('leftover_portions');

        $avgDaily = $totalDays > 0 ? round($totalDistributed / $totalDays, 1) : 0.0;
        $successRate = $totalTarget > 0 ? round(($totalDistributed / $totalTarget) * 100, 1) : 0.0;

        return [
            'total_days' => $totalDays,
            'target_students' => $targetStudents,
            'distributed_students' => $distStudents,
            'target_teachers' => $targetTeachers,
            'distributed_teachers' => $distTeachers,
            'total_target' => $totalTarget,
            'total_distributed' => $totalDistributed,
            'total_leftovers' => $totalLeftovers,
            'avg_daily_distributed' => $avgDaily,
            'success_rate' => $successRate,
        ];
    }

    /**
     * Rincian agregat bulanan dalam rentang tanggal untuk laporan detail.
     * Memastikan setiap bulan dalam rentang tanggal ditampilkan secara urut dan lengkap.
     *
     * @return array<int, array{
     *     month_name: string,
     *     days_count: int,
     *     target_packets: int,
     *     received_packets: int,
     *     distributed_students: int,
     *     distributed_teachers: int,
     *     distributed_packets: int,
     *     success_rate: float,
     *     quality_status: string
     * }>
     */
    public function getMonthlyBreakdown(Carbon $from, Carbon $to): array
    {
        $distributions = MbgDistribution::whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $grouped = $distributions->groupBy(fn ($d) => Carbon::parse($d->date)->format('Y-m'));
        $months = [];

        $cursor = $from->copy()->startOfMonth();
        $endCursor = $to->copy()->startOfMonth();

        while ($cursor->lte($endCursor)) {
            $yearMonth = $cursor->format('Y-m');
            $items = $grouped->get($yearMonth, collect());

            $daysCount = $items->count();
            $targetPackets = (int) $items->sum('total_target');
            $distStudents = (int) $items->sum('distributed_students');
            $distTeachers = (int) $items->sum('distributed_teachers');
            $distPackets = (int) $items->sum('total_distributed');

            $rate = $targetPackets > 0
                ? round(($distPackets / $targetPackets) * 100, 1)
                : 100.0;

            $months[] = [
                'month_name' => $cursor->translatedFormat('F Y'),
                'days_count' => $daysCount,
                'target_packets' => $targetPackets,
                'received_packets' => $targetPackets,
                'distributed_students' => $distStudents,
                'distributed_teachers' => $distTeachers,
                'distributed_packets' => $distPackets,
                'success_rate' => $rate,
                'quality_status' => 'Sangat Baik / Layak',
            ];

            $cursor->addMonth();
        }

        return $months;
    }

    /**
     * Ekspor rekapitulasi data MBG ke file Excel (.xlsx).
     */
    public function exportExcel(Collection $distributions, array $stats, string $periodLabel, string $schoolName): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekapitulasi MBG');

        // Header Dokumen
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI MAKAN BERGIZI GRATIS (MBG)');
        $sheet->setCellValue('A2', strtoupper($schoolName));
        $sheet->setCellValue('A3', 'Periode: '.$periodLabel);

        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Tabel Kolom
        $row = 5;
        $sheet->setCellValue('A'.$row, 'NO');
        $sheet->setCellValue('B'.$row, 'TANGGAL');
        $sheet->setCellValue('C'.$row, 'MENU MAKANAN');
        $sheet->setCellValue('D'.$row, 'TARGET SISWA');
        $sheet->setCellValue('E'.$row, 'REALISASI SISWA');
        $sheet->setCellValue('F'.$row, 'TARGET GURU');
        $sheet->setCellValue('G'.$row, 'REALISASI GURU');
        $sheet->setCellValue('H'.$row, 'TOTAL DISTRIBUSI');
        $sheet->setCellValue('I'.$row, 'SISA');
        $sheet->setCellValue('J'.$row, 'CAPAIAN (%)');
        $sheet->setCellValue('K'.$row, 'KETERANGAN');

        $headerRange = 'A5:K5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Isi Data
        $row = 6;
        $no = 1;

        foreach ($distributions as $item) {
            $sheet->setCellValueExplicit('A'.$row, $no++, DataType::TYPE_NUMERIC);
            $sheet->setCellValue('B'.$row, $item->date ? $item->date->format('d/m/Y') : '-');
            $sheet->setCellValue('C'.$row, $item->menu_name);
            $sheet->setCellValueExplicit('D'.$row, $item->target_students, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('E'.$row, $item->distributed_students, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('F'.$row, $item->target_teachers, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('G'.$row, $item->distributed_teachers, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('H'.$row, $item->total_distributed, DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('I'.$row, $item->leftover_portions, DataType::TYPE_NUMERIC);
            $sheet->setCellValue('J'.$row, $item->success_rate.'%');
            $sheet->setCellValue('K'.$row, $item->notes ?? '-');

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$row.':J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Baris Total / Summary
        $sheet->setCellValue('A'.$row, 'TOTAL KESELURUHAN');
        $sheet->mergeCells('A'.$row.':C'.$row);
        $sheet->setCellValueExplicit('D'.$row, $stats['target_students'], DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('E'.$row, $stats['distributed_students'], DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('F'.$row, $stats['target_teachers'], DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('G'.$row, $stats['distributed_teachers'], DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('H'.$row, $stats['total_distributed'], DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('I'.$row, $stats['total_leftovers'], DataType::TYPE_NUMERIC);
        $sheet->setCellValue('J'.$row, $stats['success_rate'].'%');
        $sheet->setCellValue('K'.$row, 'Rata-rata: '.$stats['avg_daily_distributed'].' porsi/hari');

        $totalRange = 'A'.$row.':K'.$row;
        $sheet->getStyle($totalRange)->getFont()->setBold(true);
        $sheet->getStyle($totalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$row.':J'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Styling Border
        $tableRange = 'A5:K'.$row;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto width kolom
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'rekap-mbg-'.date('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
