<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     * Dashboard Rekapitulasi Presensi khusus untuk Tenaga Pengajar (Guru).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

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

        // Kelas yang diampu guru (Wali Kelas + Jadwal Mengajar)
        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
        $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $allowedClassIds = $homeroomClassIds->merge($teachingClassIds)->unique();

        $classes = SchoolClass::whereIn('id', $allowedClassIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        // Tentukan ID kelas yang menjadi target query
        $targetClassIds = ($schoolClassId && $schoolClassId !== 'all' && $allowedClassIds->contains((int) $schoolClassId))
            ? collect([(int) $schoolClassId])
            : $allowedClassIds;

        // Query Dasar Absensi
        $attendanceQuery = Attendance::with(['student.user', 'student.schoolClass', 'schedule.subject'])
            ->whereHas('student', function ($q) use ($targetClassIds) {
                $q->whereIn('school_class_id', $targetClassIds);
            })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);

        if ($status && $status !== 'all') {
            $attendanceQuery->where('status', $status);
        }

        // Agregasi Statistik Ringkasan (KPI)
        $totalRecords = (clone $attendanceQuery)->count();
        $totalHadir = (clone $attendanceQuery)->where('status', 'hadir')->count();
        $totalTerlambat = (clone $attendanceQuery)->where('status', 'terlambat')->count();
        $totalIzin = (clone $attendanceQuery)->where('status', 'izin')->count();
        $totalSakit = (clone $attendanceQuery)->where('status', 'sakit')->count();
        $totalAlpa = (clone $attendanceQuery)->where('status', 'alpa')->count();

        $totalPresent = $totalHadir + $totalTerlambat;
        $attendanceRate = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 1) : 0;

        // Data untuk Donut Chart
        $donutChartData = [
            'labels' => ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            'data' => [$totalHadir, $totalTerlambat, $totalIzin, $totalSakit, $totalAlpa],
            'colors' => ['#20C997', '#FFD43B', '#74C0FC', '#A5D8FF', '#FF6B6B'],
        ];

        // Menentukan nama bulan untuk judul grafik tren
        $startCarbon = Carbon::parse($startDate);
        $endCarbon = Carbon::parse($endDate);
        if ($startCarbon->format('Y-m') === $endCarbon->format('Y-m')) {
            $reportMonthName = $startCarbon->translatedFormat('F');
        } else {
            $reportMonthName = $startCarbon->translatedFormat('M').' - '.$endCarbon->translatedFormat('M');
        }

        // Data untuk Bar Chart
        $period = CarbonPeriod::create($startDate, $endDate);
        $dailyLabels = [];
        $dailyFullDates = [];
        $dailyHadir = [];
        $dailyTerlambat = [];
        $dailyIzinSakit = [];
        $dailyAlpa = [];

        $dailyStats = (clone $attendanceQuery)
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
        $studentsQuery = Student::with(['user', 'schoolClass'])
            ->whereIn('school_class_id', $targetClassIds);

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

        $students = $studentsQuery->paginate(15, ['*'], 'student_page')->withQueryString();

        // Data Tab 2: Jurnal Log Riwayat Harian
        $attendanceLogs = (clone $attendanceQuery)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc')
            ->paginate(20, ['*'], 'log_page')
            ->withQueryString();

        return view('guru.reports.attendance', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'schoolClassId' => $schoolClassId,
            'status' => $status,
            'search' => $search,
            'activeTab' => $activeTab,
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
     * Export laporan presensi siswa ke format spreadsheet Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $startDate = $request->filled('start_date')
            ? $request->input('start_date')
            : Carbon::now()->startOfMonth()->toDateString();

        $endDate = $request->filled('end_date')
            ? $request->input('end_date')
            : Carbon::now()->toDateString();

        $schoolClassId = $request->input('school_class_id');
        $status = $request->input('status');
        $search = $request->input('search');

        // Kelas yang diampu guru
        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
        $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');
        $allowedClassIds = $homeroomClassIds->merge($teachingClassIds)->unique();

        $targetClassIds = ($schoolClassId && $schoolClassId !== 'all' && $allowedClassIds->contains((int) $schoolClassId))
            ? collect([(int) $schoolClassId])
            : $allowedClassIds;

        $selectedClass = ($schoolClassId && $schoolClassId !== 'all')
            ? SchoolClass::find($schoolClassId)
            : null;

        $spreadsheet = new Spreadsheet;

        // SHEET 1: REKAPITULASI PER SISWA
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Rekapitulasi Siswa');

        $sheet1->setCellValue('A1', 'LAPORAN REKAPITULASI KEHADIRAN SISWA');
        $sheet1->setCellValue('A2', 'Guru Pengampu: '.($user->name ?? '-').' (NIP: '.($teacher->nip ?? '-').')');
        $sheet1->setCellValue('A3', 'Periode: '.Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y'));
        $sheet1->setCellValue('A4', 'Kelas: '.($selectedClass ? $selectedClass->name : 'Semua Kelas Binaan/Ajar'));

        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet1->getStyle('A2:A4')->getFont()->setSize(10);

        $recapHeaders = [
            'A6' => 'No',
            'B6' => 'NIS',
            'C6' => 'Nama Siswa',
            'D6' => 'Kelas',
            'E6' => 'Hadir',
            'F6' => 'Terlambat',
            'G6' => 'Izin',
            'H6' => 'Sakit',
            'I6' => 'Alpa',
            'J6' => 'Total Presensi',
            'K6' => 'Tingkat Kehadiran (%)',
        ];

        foreach ($recapHeaders as $cell => $val) {
            $sheet1->setCellValue($cell, $val);
        }

        $headerStyle1 = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '20C997']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet1->getStyle('A6:K6')->applyFromArray($headerStyle1);
        $sheet1->getRowDimension(6)->setRowHeight(26);

        $studentsQuery = Student::with(['user', 'schoolClass'])
            ->whereIn('school_class_id', $targetClassIds);

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
        $rowNum1 = 7;
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
            $sheet1->getStyle('A7:K'.($rowNum1 - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getStyle('A7:A'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('B7:B'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('D7:D'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle('E7:K'.($rowNum1 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'K') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // SHEET 2: JURNAL LOG HARIAN
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Log Riwayat');

        $sheet2->setCellValue('A1', 'JURNAL LOG RIWAYAT PRESENSI');
        $sheet2->setCellValue('A2', 'Guru Pengampu: '.($user->name ?? '-'));
        $sheet2->setCellValue('A3', 'Periode: '.Carbon::parse($startDate)->translatedFormat('d F Y').' s/d '.Carbon::parse($endDate)->translatedFormat('d F Y'));

        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet2->getStyle('A2:A3')->getFont()->setSize(10);

        $logHeaders = [
            'A5' => 'No',
            'B5' => 'Tanggal',
            'C5' => 'NIS',
            'D5' => 'Nama Siswa',
            'E5' => 'Kelas',
            'F5' => 'Waktu Masuk',
            'G5' => 'Metode',
            'H5' => 'Status',
            'I5' => 'Catatan',
        ];

        foreach ($logHeaders as $cell => $val) {
            $sheet2->setCellValue($cell, $val);
        }

        $headerStyle2 = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '5294FF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet2->getStyle('A5:I5')->applyFromArray($headerStyle2);
        $sheet2->getRowDimension(5)->setRowHeight(26);

        $logsQuery = Attendance::with(['student.user', 'student.schoolClass'])
            ->whereHas('student', function ($q) use ($targetClassIds) {
                $q->whereIn('school_class_id', $targetClassIds);
            })
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->orderBy('date', 'desc')
            ->orderBy('check_in_time', 'desc');

        if ($status && $status !== 'all') {
            $logsQuery->where('status', $status);
        }

        $logs = $logsQuery->get();
        $rowNum2 = 6;
        $no2 = 1;

        foreach ($logs as $log) {
            $sheet2->setCellValue('A'.$rowNum2, $no2++);
            $sheet2->setCellValue('B'.$rowNum2, Carbon::parse($log->date)->translatedFormat('d/m/Y'));
            $sheet2->setCellValueExplicit('C'.$rowNum2, (string) ($log->student?->nis ?? '-'), DataType::TYPE_STRING);
            $sheet2->setCellValue('D'.$rowNum2, $log->student?->user?->name ?? '-');
            $sheet2->setCellValue('E'.$rowNum2, $log->student?->schoolClass?->name ?? '-');
            $sheet2->setCellValue('F'.$rowNum2, $log->check_in_time ? Carbon::parse($log->check_in_time)->format('H:i') : '-');
            $sheet2->setCellValue('G'.$rowNum2, strtoupper($log->method ?? 'manual'));
            $sheet2->setCellValue('H'.$rowNum2, strtoupper($log->status ?? '-'));
            $sheet2->setCellValue('I'.$rowNum2, $log->notes ?? '-');

            $rowNum2++;
        }

        if ($logs->isNotEmpty()) {
            $sheet2->getStyle('A6:I'.($rowNum2 - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5DD']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet2->getStyle('A6:C'.($rowNum2 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle('E6:H'.($rowNum2 - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'I') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Rekap_Kehadiran_Guru_'.Carbon::now()->format('Ymd_His').'.xlsx';

        return response()->stream(
            function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
