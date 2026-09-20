<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveRequestController extends Controller
{
    /**
     * Halaman Utama Pengelolaan Perizinan Siswa (Guru / Wali Kelas).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $allowedClassIds = $this->getAllowedClassIds($teacher);
        $classes = SchoolClass::whereIn('id', $allowedClassIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $status = $request->input('status', 'pending');
        $type = $request->input('type', 'all');
        $classId = $request->input('school_class_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        // Query dasar seluruh permohonan izin untuk kelas yang diampu
        $baseQuery = LeaveRequest::with(['student.user', 'student.schoolClass', 'requester', 'reviewer'])
            ->whereHas('student', function ($q) use ($allowedClassIds) {
                $q->whereIn('school_class_id', $allowedClassIds);
            });

        // Hitung KPI Statistik
        $counts = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        $query = clone $baseQuery;

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($classId) {
            $query->whereHas('student', fn ($q) => $q->where('school_class_id', $classId));
        }

        if ($startDate) {
            $query->whereDate('date_from', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_to', '<=', $endDate);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $leaveRequests = $query->latest('created_at')->paginate(15)->withQueryString();

        // Daftar siswa di kelas yang diampu (untuk modal input izin manual)
        $students = Student::with(['user', 'schoolClass'])
            ->whereIn('school_class_id', $allowedClassIds)
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '');

        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');

        return view('guru.leave-requests.index', compact(
            'classes',
            'leaveRequests',
            'counts',
            'status',
            'type',
            'classId',
            'startDate',
            'endDate',
            'search',
            'students',
            'teacher',
            'homeroomClassIds'
        ));
    }

    /**
     * Input Permohonan Izin Siswa Manual oleh Guru (Otomatis Disetujui & Rekam Presensi).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $allowedClassIds = $this->getAllowedClassIds($teacher);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:sakit,izin',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $student = Student::findOrFail($validated['student_id']);
        if (! $allowedClassIds->contains($student->school_class_id)) {
            abort(403, 'Anda tidak memiliki wewenang mencatat izin untuk siswa di kelas ini.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'student_id' => $validated['student_id'],
            'requested_by' => Auth::id(),
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Rekam otomatis kehadiran siswa menjadi izin/sakit
        $this->recordAttendanceForLeave($leaveRequest);

        return back()->with('success', 'Perizinan siswa an. '.$student->user->name.' berhasil dicatat dan presensi otomatis diperbarui.');
    }

    /**
     * Setujui permohonan izin/sakit siswa.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeTeacherForStudent($leaveRequest);

        $leaveRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->recordAttendanceForLeave($leaveRequest);

        return back()->with('success', 'Permohonan '.$leaveRequest->type.' atas nama '.$leaveRequest->student->user->name.' berhasil disetujui.');
    }

    /**
     * Tolak permohonan izin/sakit siswa.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorizeTeacherForStudent($leaveRequest);

        $leaveRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permohonan '.$leaveRequest->type.' atas nama '.$leaveRequest->student->user->name.' telah ditolak.');
    }

    /**
     * Ekspor Rekap Data Perizinan Siswa ke Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $allowedClassIds = $this->getAllowedClassIds($teacher);

        $status = $request->input('status', 'all');
        $type = $request->input('type', 'all');
        $classId = $request->input('school_class_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $query = LeaveRequest::with(['student.user', 'student.schoolClass', 'requester', 'reviewer'])
            ->whereHas('student', function ($q) use ($allowedClassIds) {
                $q->whereIn('school_class_id', $allowedClassIds);
            });

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }

        if ($classId) {
            $query->whereHas('student', fn ($q) => $q->where('school_class_id', $classId));
        }

        if ($startDate) {
            $query->whereDate('date_from', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date_to', '<=', $endDate);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->latest('date_from')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Perizinan Siswa');

        // Judul & Metadata
        $sheet->setCellValue('A1', 'REKAPITULASI PERIZINAN SISWA (IZIN & SAKIT)');
        $sheet->setCellValue('A2', 'Guru Pengampu: '.($teacher->user->name ?? '-').' (NIP: '.($teacher->nip ?? '-').')');
        $sheet->setCellValue('A3', 'Filter Status: '.strtoupper($status).' • Tanggal Unduh: '.Carbon::now()->translatedFormat('d F Y, H:i'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setSize(10);

        // Header Kolom
        $headers = [
            'A5' => 'No',
            'B5' => 'Tanggal Pengajuan',
            'C5' => 'NIS',
            'D5' => 'Nama Siswa',
            'E5' => 'Kelas',
            'F5' => 'Jenis',
            'G5' => 'Periode Dari',
            'H5' => 'Periode Sampai',
            'I5' => 'Total Hari',
            'J5' => 'Alasan / Keterangan',
            'K5' => 'Diajukan Oleh',
            'L5' => 'Status',
            'M5' => 'Diverifikasi Oleh',
            'N5' => 'Tanggal Verifikasi',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '20C997']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet->getStyle('A5:N5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $rowNum = 6;
        $no = 1;

        foreach ($records as $lr) {
            $from = Carbon::parse($lr->date_from);
            $to = Carbon::parse($lr->date_to);
            $days = $from->diffInDays($to) + 1;

            $sheet->setCellValue('A'.$rowNum, $no++);
            $sheet->setCellValue('B'.$rowNum, $lr->created_at->translatedFormat('d/m/Y H:i'));
            $sheet->setCellValueExplicit('C'.$rowNum, (string) ($lr->student->nis ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rowNum, $lr->student->user->name ?? '-');
            $sheet->setCellValue('E'.$rowNum, $lr->student->schoolClass->name ?? '-');
            $sheet->setCellValue('F'.$rowNum, strtoupper($lr->type));
            $sheet->setCellValue('G'.$rowNum, $from->translatedFormat('d/m/Y'));
            $sheet->setCellValue('H'.$rowNum, $to->translatedFormat('d/m/Y'));
            $sheet->setCellValue('I'.$rowNum, $days.' hari');
            $sheet->setCellValue('J'.$rowNum, $lr->reason);
            $sheet->setCellValue('K'.$rowNum, $lr->requester->name ?? 'Orang Tua / Wali');
            $sheet->setCellValue('L'.$rowNum, strtoupper($lr->status));
            $sheet->setCellValue('M'.$rowNum, $lr->reviewer->name ?? '-');
            $sheet->setCellValue('N'.$rowNum, $lr->reviewed_at ? Carbon::parse($lr->reviewed_at)->translatedFormat('d/m/Y H:i') : '-');

            $rowNum++;
        }

        $lastRow = max(6, $rowNum - 1);
        $sheet->getStyle('A6:N'.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
        $sheet->getStyle('A6:C'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F6:I'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L6:N'.$lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Rekap-Perizinan-Siswa-'.date('Ymd-His').'.xlsx';

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
     * Rekam otomatis kehadiran siswa sesuai rentang tanggal izin/sakit.
     */
    private function recordAttendanceForLeave(LeaveRequest $leaveRequest): void
    {
        $currentDate = Carbon::parse($leaveRequest->date_from)->copy();
        $endDate = Carbon::parse($leaveRequest->date_to);
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->toDateString();
            $existing = Attendance::where('student_id', $leaveRequest->student_id)
                ->whereDate('date', $dateStr)
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => $leaveRequest->type,
                    'method' => 'manual',
                    'recorded_by' => Auth::id(),
                    'notes' => 'Disetujui dari pengajuan '.$leaveRequest->type.': '.$leaveRequest->reason,
                ]);
            } else {
                Attendance::create([
                    'student_id' => $leaveRequest->student_id,
                    'date' => $dateStr,
                    'status' => $leaveRequest->type,
                    'method' => 'manual',
                    'recorded_by' => Auth::id(),
                    'notes' => 'Disetujui dari pengajuan '.$leaveRequest->type.': '.$leaveRequest->reason,
                ]);
            }
            $currentDate->addDay();
        }
    }

    /**
     * Dapatkan ID kelas yang diampu oleh guru (wali kelas + jadwal mengajar).
     */
    private function getAllowedClassIds(?Teacher $teacher): Collection
    {
        if (! $teacher) {
            return collect();
        }

        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
        $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');

        return $homeroomClassIds->merge($teachingClassIds)->unique();
    }

    /**
     * Validasi hak akses guru terhadap persetujuan izin (Khusus Wali Kelas dari siswa terkait).
     */
    private function authorizeTeacherForStudent(LeaveRequest $leaveRequest): void
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Profil guru tidak ditemukan.');
        }

        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');

        if (! $homeroomClassIds->contains($leaveRequest->student->school_class_id)) {
            abort(403, 'Wewenang persetujuan izin siswa khusus untuk Wali Kelas yang bersangkutan.');
        }
    }
}
