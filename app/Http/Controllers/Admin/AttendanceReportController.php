<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceReportController extends Controller
{
    /**
     * Menampilkan dashboard laporan presensi siswa lengkap dengan statistik KPI dan grafik Neobrutalism.
     */
    public function index(Request $request): View
    {
        $startDate = $request->filled('start_date')
            ? $request->input('start_date')
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->input('end_date')
            : Carbon::now()->toDateString();

        $schoolClassId = $request->input('school_class_id');
        $status = $request->input('status');
        $search = $request->input('search');
        $activeTab = $request->input('tab', 'summary');

        // Master data kelas untuk dropdown filter dengan jumlah siswa
        $classes = SchoolClass::withCount('students')->orderBy('level')->orderBy('name')->get();
        $selectedClass = $schoolClassId && $schoolClassId !== 'all'
            ? $classes->firstWhere('id', (int) $schoolClassId)
            : null;

        // Query dasar absensi dengan filter rentang tanggal
        $baseQuery = Attendance::with(['student.user', 'student.schoolClass', 'schedule.subject'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($schoolClassId && $schoolClassId !== 'all') {
            $baseQuery->whereHas('student', function ($q) use ($schoolClassId) {
                $q->where('school_class_id', $schoolClassId);
            });
        }

        // Query khusus riwayat logs (menerapkan filter status)
        $attendanceQuery = clone $baseQuery;
        if ($status && $status !== 'all') {
            if ($status === 'izin_sakit') {
                $attendanceQuery->whereIn('status', ['izin', 'sakit']);
            } else {
                $attendanceQuery->where('status', $status);
            }
        }

        // Agregasi Statistik Ringkasan (KPI)
        $totalRecords = (clone $baseQuery)->count();
        $totalHadir = (clone $baseQuery)->where('status', 'hadir')->count();
        $totalTerlambat = (clone $baseQuery)->where('status', 'terlambat')->count();
        $totalIzin = (clone $baseQuery)->where('status', 'izin')->count();
        $totalSakit = (clone $baseQuery)->where('status', 'sakit')->count();
        $totalAlpa = (clone $baseQuery)->where('status', 'alpa')->count();

        $totalPresent = $totalHadir + $totalTerlambat;
        $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // Data untuk Neobrutalism Donut Chart (Distribusi Status Kehadiran)
        $donutChartData = [
            'labels' => ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            'data' => [$totalHadir, $totalTerlambat, $totalIzin, $totalSakit, $totalAlpa],
            'colors' => ['#20C997', '#FFD43B', '#5294FF', '#845EF7', '#FF6B6B'],
        ];

        // Menentukan nama bulan untuk judul grafik tren
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        if ($startCarbon->format('Y-m') === $endCarbon->format('Y-m')) {
            $reportMonthName = $startCarbon->translatedFormat('F');
        } else {
            $reportMonthName = $startCarbon->translatedFormat('M').' - '.$endCarbon->translatedFormat('M');
        }

        // Data untuk Neobrutalism Bar Chart (Tren Kehadiran Harian)
        $period = CarbonPeriod::create($startDate, $endDate);
        $dailyLabels = [];
        $dailyFullDates = [];
        $dailyHadir = [];
        $dailyTerlambat = [];
        $dailyIzinSakit = [];
        $dailyAlpa = [];

        $dailyStats = (clone $baseQuery)
            ->selectRaw('DATE(date) as log_date, status, COUNT(*) as count')
            ->groupBy('log_date', 'status')
            ->get()
            ->groupBy('log_date');

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $dailyLabels[] = (string) $date->format('j');
            $dailyFullDates[] = 'Tanggal '.$date->format('j').' '.$date->translatedFormat('F');

            $statusCounts = $dailyStats->get($dateStr, collect())->pluck('count', 'status')->all();

            $dailyHadir[] = $statusCounts['hadir'] ?? 0;
            $dailyTerlambat[] = $statusCounts['terlambat'] ?? 0;
            $dailyIzinSakit[] = ($statusCounts['izin'] ?? 0) + ($statusCounts['sakit'] ?? 0);
            $dailyAlpa[] = $statusCounts['alpa'] ?? 0;
        }

        $barChartData = [
            'labels' => $dailyLabels,
            'full_dates' => $dailyFullDates,
            'hadir' => $dailyHadir,
            'terlambat' => $dailyTerlambat,
            'izin_sakit' => $dailyIzinSakit,
            'alpa' => $dailyAlpa,
        ];

        // Data Tab 1: Rekapitulasi per Siswa
        $studentsQuery = Student::with(['user', 'schoolClass']);

        if ($schoolClassId && $schoolClassId !== 'all') {
            $studentsQuery->where('school_class_id', $schoolClassId);
        }

        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $studentsQuery->withCount([
            'attendances as count_total' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
            },
            'attendances as count_hadir' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'hadir');
            },
            'attendances as count_terlambat' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'terlambat');
            },
            'attendances as count_izin' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'izin');
            },
            'attendances as count_sakit' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'sakit');
            },
            'attendances as count_alpa' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'alpa');
            },
        ]);

        // Pagination limit untuk Rekapitulasi per Siswa (25, 50, 100, semua)
        $perPage = $request->input('per_page', '25');
        if ($perPage === 'all' || $perPage === 'semua') {
            $totalStudents = Student::count();
            $perPageNum = max($totalStudents, 1);
        } else {
            $perPageNum = in_array((int) $perPage, [25, 50, 100], true) ? (int) $perPage : 25;
            $perPage = (string) $perPageNum;
        }

        $students = $studentsQuery->paginate($perPageNum, ['*'], 'student_page')->withQueryString();

        // Data Tab 2: Jurnal Log Riwayat Harian
        $attendanceLogs = (clone $attendanceQuery)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20, ['*'], 'log_page')
            ->withQueryString();

        return view('admin.reports.attendance', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'schoolClassId' => $schoolClassId,
            'selectedClass' => $selectedClass,
            'status' => $status,
            'search' => $search,
            'activeTab' => $activeTab,
            'perPage' => $perPage,
            'classes' => $classes,
            'totalRecords' => $totalRecords,
            'totalHadir' => $totalHadir,
            'totalTerlambat' => $totalTerlambat,
            'totalIzin' => $totalIzin,
            'totalSakit' => $totalSakit,
            'totalAlpa' => $totalAlpa,
            'attendanceRate' => $attendanceRate,
            'donutChartData' => $donutChartData,
            'barChartData' => $barChartData,
            'reportMonthName' => $reportMonthName,
            'students' => $students,
            'attendanceLogs' => $attendanceLogs,
        ]);
    }

    /**
     * Export laporan presensi siswa ke format spreadsheet Excel (.xlsx) multi-sheet.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->filled('start_date')
            ? $request->input('start_date')
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->input('end_date')
            : Carbon::now()->toDateString();

        $schoolClassId = $request->input('school_class_id');
        $status = $request->input('status');
        $search = $request->input('search');

        $selectedClass = $schoolClassId && $schoolClassId !== 'all'
            ? SchoolClass::find($schoolClassId)
            : null;

        $spreadsheet = new Spreadsheet;

        // ==========================================
        // SHEET 1: REKAPITULASI PER SISWA
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Rekapitulasi Siswa');

        // Header Dokumen
        $sheet1->setCellValue('A1', 'LAPORAN REKAPITULASI KEHADIRAN SISWA');
        $sheet1->setCellValue('A2', 'Periode: '.Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y'));
        $sheet1->setCellValue('A3', 'Kelas: '.($selectedClass ? $selectedClass->name : 'Semua Kelas'));

        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A2:A3')->getFont()->setSize(10);

        // Header Kolom Tabel
        $recapHeaders = [
            'A5' => 'No',
            'B5' => 'NIS',
            'C5' => 'Nama Siswa',
            'D5' => 'Kelas',
            'E5' => 'Hadir',
            'F5' => 'Terlambat',
            'G5' => 'Izin',
            'H5' => 'Sakit',
            'I5' => 'Alpa',
            'J5' => 'Total Presensi',
            'K5' => 'Tingkat Kehadiran (%)',
        ];

        foreach ($recapHeaders as $cell => $val) {
            $sheet1->setCellValue($cell, $val);
        }

        // Style Header Tabel Sheet 1 (Neobrutalism Yellow)
        $headerStyle1 = [
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
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet1->getStyle('A5:K5')->applyFromArray($headerStyle1);
        $sheet1->getRowDimension(5)->setRowHeight(26);

        // Ambil Data Siswa untuk Rekap
        $studentsQuery = Student::with(['user', 'schoolClass']);
        if ($schoolClassId && $schoolClassId !== 'all') {
            $studentsQuery->where('school_class_id', $schoolClassId);
        }
        if ($search) {
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $studentsQuery->withCount([
            'attendances as count_total' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
            },
            'attendances as count_hadir' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'hadir');
            },
            'attendances as count_terlambat' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'terlambat');
            },
            'attendances as count_izin' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'izin');
            },
            'attendances as count_sakit' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'sakit');
            },
            'attendances as count_alpa' => function ($q) use ($startDate, $endDate) {
                $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)->where('status', 'alpa');
            },
        ]);

        $students = $studentsQuery->get();
        $rowNum1 = 6;
        $no1 = 1;

        foreach ($students as $student) {
            $totalPresent = $student->count_hadir + $student->count_terlambat;
            $rate = $student->count_total > 0
                ? round(($totalPresent / $student->count_total) * 100, 1)
                : 0;

            $sheet1->setCellValue('A'.$rowNum1, $no1++);
            $sheet1->setCellValueExplicit('B'.$rowNum1, (string) ($student->nis ?? '-'), DataType::TYPE_STRING);
            $sheet1->setCellValue('C'.$rowNum1, $student->user?->name ?? '-');
            $sheet1->setCellValue('D'.$rowNum1, $student->schoolClass?->name ?? '-');
            $sheet1->setCellValue('E'.$rowNum1, $student->count_hadir);
            $sheet1->setCellValue('F'.$rowNum1, $student->count_terlambat);
            $sheet1->setCellValue('G'.$rowNum1, $student->count_izin);
            $sheet1->setCellValue('H'.$rowNum1, $student->count_sakit);
            $sheet1->setCellValue('I'.$rowNum1, $student->count_alpa);
            $sheet1->setCellValue('J'.$rowNum1, $student->count_total);
            $sheet1->setCellValue('K'.$rowNum1, $rate.'%');

            $rowNum1++;
        }

        if ($students->isNotEmpty()) {
            $dataStyle1 = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D5DD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet1->getStyle('A6:K'.($rowNum1 - 1))->applyFromArray($dataStyle1);
            $sheet1->getStyle('A6:A'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B6:B'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('D6:D'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('E6:K'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'K') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ==========================================
        // SHEET 2: JURNAL LOG HARIAN
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Log Harian');

        // Header Dokumen Sheet 2
        $sheet2->setCellValue('A1', 'JURNAL LOG RIWAYAT PRESENSI SISWA');
        $sheet2->setCellValue('A2', 'Periode: '.Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y'));
        $sheet2->setCellValue('A3', 'Kelas: '.($selectedClass ? $selectedClass->name : 'Semua Kelas'));

        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A2:A3')->getFont()->setSize(10);

        // Header Kolom Tabel Sheet 2
        $logHeaders = [
            'A5' => 'No',
            'B5' => 'Tanggal',
            'C5' => 'NIS',
            'D5' => 'Nama Siswa',
            'E5' => 'Kelas',
            'F5' => 'Jam Masuk',
            'G5' => 'Jam Pulang',
            'H5' => 'Status',
            'I5' => 'Metode',
            'J5' => 'Catatan',
        ];

        foreach ($logHeaders as $cell => $val) {
            $sheet2->setCellValue($cell, $val);
        }

        // Style Header Tabel Sheet 2 (Neobrutalism Cyan / Main Blue)
        $headerStyle2 = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D0EBFF'],
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
        $sheet2->getStyle('A5:J5')->applyFromArray($headerStyle2);
        $sheet2->getRowDimension(5)->setRowHeight(26);

        // Ambil Data Log Absensi
        $attendanceLogsQuery = Attendance::with(['student.user', 'student.schoolClass', 'schedule.subject'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($schoolClassId && $schoolClassId !== 'all') {
            $attendanceLogsQuery->whereHas('student', function ($q) use ($schoolClassId) {
                $q->where('school_class_id', $schoolClassId);
            });
        }

        if ($status && $status !== 'all') {
            if ($status === 'izin_sakit') {
                $attendanceLogsQuery->whereIn('status', ['izin', 'sakit']);
            } else {
                $attendanceLogsQuery->where('status', $status);
            }
        }

        $logs = $attendanceLogsQuery->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->get();

        $rowNum2 = 6;
        $no2 = 1;

        foreach ($logs as $log) {
            $sheet2->setCellValue('A'.$rowNum2, $no2++);
            $sheet2->setCellValue('B'.$rowNum2, $log->date?->format('d/m/Y') ?? '-');
            $sheet2->setCellValueExplicit('C'.$rowNum2, (string) ($log->student?->nis ?? '-'), DataType::TYPE_STRING);
            $sheet2->setCellValue('D'.$rowNum2, $log->student?->user?->name ?? '-');
            $sheet2->setCellValue('E'.$rowNum2, $log->student?->schoolClass?->name ?? '-');
            $sheet2->setCellValue('F'.$rowNum2, $log->check_in_time ? $log->check_in_time->format('H:i') : '-');
            $sheet2->setCellValue('G'.$rowNum2, $log->check_out_time ? $log->check_out_time->format('H:i') : '-');
            $sheet2->setCellValue('H'.$rowNum2, strtoupper($log->status));
            $sheet2->setCellValue('I'.$rowNum2, strtoupper($log->method));
            $sheet2->setCellValue('J'.$rowNum2, $log->notes ?? '-');

            $rowNum2++;
        }

        if ($logs->isNotEmpty()) {
            $dataStyle2 = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D5DD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet2->getStyle('A6:J'.($rowNum2 - 1))->applyFromArray($dataStyle2);
            $sheet2->getStyle('A6:C'.($rowNum2 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('E6:I'.($rowNum2 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'J') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet ke sheet 1 saat dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_kehadiran_siswa_'.str_replace('-', '', $startDate).'_'.str_replace('-', '', $endDate).'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
