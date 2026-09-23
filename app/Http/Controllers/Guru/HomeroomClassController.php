<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HomeroomClassController extends Controller
{
    /**
     * Tampilan Halaman Utama Kelas Binaan (Wali Kelas).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        // Ambil kelas binaan guru (sebagai wali kelas)
        $homeroomClasses = SchoolClass::with(['academicYear'])
            ->where('homeroom_teacher_id', $teacher->id)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $selectedClassId = $request->input('school_class_id', $homeroomClasses->first()?->id);
        $selectedClass = $homeroomClasses->firstWhere('id', $selectedClassId);

        $students = collect();
        $kpi = [
            'total' => 0,
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpa' => 0,
            'belum_absen' => 0,
            'rate' => 0,
        ];
        $leaveRequests = collect();

        $today = Carbon::today()->toDateString();
        $currentMonthStart = Carbon::today()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::today()->endOfMonth()->toDateString();
        $search = $request->input('search');

        if ($selectedClass) {
            $studentsQuery = Student::with([
                'user',
                'parents.user',
                'attendances' => function ($q) use ($today) {
                    $q->whereDate('date', $today);
                },
            ])
                ->where('school_class_id', $selectedClass->id);

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
                'attendances as month_total' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd);
                },
                'attendances as month_hadir' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'hadir');
                },
                'attendances as month_terlambat' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'terlambat');
                },
                'attendances as month_izin' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'izin');
                },
                'attendances as month_sakit' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'sakit');
                },
                'attendances as month_alpa' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'alpa');
                },
            ]);

            $students = $studentsQuery->get()->sortBy(fn ($s) => $s->user->name ?? '');

            // Hitung KPI Kehadiran Hari Ini
            $totalStudents = $selectedClass->students()->count();
            $todayAttendances = Attendance::whereIn('student_id', $selectedClass->students()->pluck('id'))
                ->whereDate('date', $today)
                ->get();

            $hadirCount = $todayAttendances->where('status', 'hadir')->count();
            $terlambatCount = $todayAttendances->where('status', 'terlambat')->count();
            $izinCount = $todayAttendances->where('status', 'izin')->count();
            $sakitCount = $todayAttendances->where('status', 'sakit')->count();
            $alpaCount = $todayAttendances->where('status', 'alpa')->count();
            $absenCount = $todayAttendances->count();

            $rate = $totalStudents > 0
                ? round((($hadirCount + $terlambatCount) / $totalStudents) * 100, 1)
                : 0;

            $kpi = [
                'total' => $totalStudents,
                'hadir' => $hadirCount,
                'terlambat' => $terlambatCount,
                'izin' => $izinCount,
                'sakit' => $sakitCount,
                'alpa' => $alpaCount,
                'belum_absen' => max(0, $totalStudents - $absenCount),
                'rate' => $rate,
            ];

            // Permohonan izin/sakit siswa kelas binaan
            $leaveRequests = LeaveRequest::with(['student.user'])
                ->whereIn('student_id', $selectedClass->students()->pluck('id'))
                ->latest()
                ->take(30)
                ->get();

            // Catatan khusus siswa kelas binaan
            $studentNotes = StudentNote::with(['student.user', 'teacher.user'])
                ->whereIn('student_id', $selectedClass->students()->pluck('id'))
                ->latest('date')
                ->latest('id')
                ->get();
        } else {
            $studentNotes = collect();
        }

        return view('guru.homeroom.index', compact(
            'teacher',
            'homeroomClasses',
            'selectedClass',
            'selectedClassId',
            'students',
            'kpi',
            'leaveRequests',
            'studentNotes',
            'search',
            'today'
        ));
    }

    /**
     * Simpan Catatan Khusus untuk Siswa Kelas Binaan.
     */
    public function storeNote(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'category' => 'required|in:kedisiplinan,prestasi,kesehatan,pembinaan,umum',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'follow_up' => 'nullable|string|max:255',
        ]);

        // Verifikasi bahwa siswa merupakan anggota kelas binaan guru ini
        $isHomeroomStudent = Student::where('id', $validated['student_id'])
            ->whereHas('schoolClass', function ($q) use ($teacher) {
                $q->where('homeroom_teacher_id', $teacher?->id);
            })
            ->exists();

        if (! $isHomeroomStudent) {
            abort(403, 'Siswa bukan merupakan anggota kelas binaan Anda.');
        }

        StudentNote::create([
            'student_id' => $validated['student_id'],
            'teacher_id' => $teacher?->id,
            'date' => $validated['date'],
            'category' => $validated['category'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'follow_up' => $validated['follow_up'],
        ]);

        return back()->with('success', 'Catatan khusus siswa berhasil disimpan.');
    }

    /**
     * Hapus Catatan Khusus Siswa.
     */
    public function destroyNote(Request $request, StudentNote $studentNote): RedirectResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        // Verifikasi otorisasi
        $isAuthorized = $studentNote->student->schoolClass?->homeroom_teacher_id === $teacher?->id
            || $studentNote->teacher_id === $teacher?->id;

        if (! $isAuthorized) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus catatan ini.');
        }

        $studentNote->delete();

        return back()->with('success', 'Catatan khusus siswa berhasil dihapus.');
    }

    /**
     * Ekspor Direktori Siswa Kelas Binaan & Kontak Orang Tua ke Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        $classId = $request->input('school_class_id');
        $schoolClass = SchoolClass::with('academicYear')
            ->where('homeroom_teacher_id', $teacher?->id)
            ->where('id', $classId)
            ->firstOrFail();

        $today = Carbon::today()->toDateString();
        $currentMonthStart = Carbon::today()->startOfMonth()->toDateString();
        $currentMonthEnd = Carbon::today()->endOfMonth()->toDateString();

        $students = Student::with([
            'user',
            'parents.user',
            'attendances' => function ($q) use ($today) {
                $q->whereDate('date', $today);
            },
        ])
            ->where('school_class_id', $schoolClass->id)
            ->withCount([
                'attendances as month_total' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd);
                },
                'attendances as month_hadir' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'hadir');
                },
                'attendances as month_terlambat' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'terlambat');
                },
                'attendances as month_izin' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'izin');
                },
                'attendances as month_sakit' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'sakit');
                },
                'attendances as month_alpa' => function ($q) use ($currentMonthStart, $currentMonthEnd) {
                    $q->whereDate('date', '>=', $currentMonthStart)->whereDate('date', '<=', $currentMonthEnd)->where('status', 'alpa');
                },
            ])
            ->get()
            ->sortBy(fn ($s) => $s->user->name ?? '');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Direktori Siswa');

        // Judul & Info Kelas
        $sheet->setCellValue('A1', 'DIREKTORI SISWA & KONTAK WALI MURID KELAS BINAAN');
        $sheet->setCellValue('A2', 'Kelas: '.$schoolClass->name.' (Tingkat '.$schoolClass->level.')');
        $sheet->setCellValue('A3', 'Wali Kelas: '.($teacher->user->name ?? '-').' (NIP: '.($teacher->nip ?? '-').')');
        $sheet->setCellValue('A4', 'Tahun Ajaran: '.($schoolClass->academicYear->name ?? '-').' • Tanggal Ekspor: '.Carbon::now()->translatedFormat('d F Y, H:i'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A4')->getFont()->setSize(10);

        // Header Tabel
        $headers = [
            'A6' => 'No',
            'B6' => 'NIS',
            'C6' => 'NISN',
            'D6' => 'Nama Siswa',
            'E6' => 'L/P',
            'F6' => 'Status Hari Ini',
            'G6' => 'No HP Siswa',
            'H6' => 'Nama Orang Tua / Wali',
            'I6' => 'Hubungan',
            'J6' => 'No HP / WhatsApp Ortu',
            'K6' => 'Hadir (Bln Ini)',
            'L6' => 'Terlambat',
            'M6' => 'Izin',
            'N6' => 'Sakit',
            'O6' => 'Alpa',
            'P6' => '% Kehadiran',
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
        $sheet->getStyle('A6:P6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(24);

        $rowNum = 7;
        $no = 1;

        foreach ($students as $student) {
            $todayAtt = $student->attendances->first();
            $statusStr = $todayAtt ? strtoupper($todayAtt->status) : 'BELUM HADIR';

            $firstParent = $student->parents->first();
            $parentName = $firstParent?->user?->name ?? '-';
            $parentRelation = $firstParent?->relation ? ucfirst($firstParent->relation) : '-';
            $parentPhone = $firstParent?->phone ?? '-';

            $totalPresent = $student->month_hadir + $student->month_terlambat;
            $rate = $student->month_total > 0
                ? round(($totalPresent / $student->month_total) * 100, 1)
                : 0;

            $sheet->setCellValue('A'.$rowNum, $no++);
            $sheet->setCellValueExplicit('B'.$rowNum, (string) ($student->nis ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('C'.$rowNum, (string) ($student->nisn ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('D'.$rowNum, $student->user?->name ?? '-');
            $sheet->setCellValue('E'.$rowNum, $student->gender == 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValue('F'.$rowNum, $statusStr);
            $sheet->setCellValueExplicit('G'.$rowNum, (string) ($student->phone ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$rowNum, $parentName);
            $sheet->setCellValue('I'.$rowNum, $parentRelation);
            $sheet->setCellValueExplicit('J'.$rowNum, (string) $parentPhone, DataType::TYPE_STRING);
            $sheet->setCellValue('K'.$rowNum, $student->month_hadir);
            $sheet->setCellValue('L'.$rowNum, $student->month_terlambat);
            $sheet->setCellValue('M'.$rowNum, $student->month_izin);
            $sheet->setCellValue('N'.$rowNum, $student->month_sakit);
            $sheet->setCellValue('O'.$rowNum, $student->month_alpa);
            $sheet->setCellValue('P'.$rowNum, $rate.'%');

            $rowNum++;
        }

        $lastDataRow = max(7, $rowNum - 1);
        $sheet->getStyle('A7:P'.$lastDataRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('000000');
        $sheet->getStyle('A7:A'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7:C'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E7:F'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K7:P'.$lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Daftar-Siswa-Binaan-'.str_replace(' ', '-', $schoolClass->name).'-'.date('Ymd').'.xlsx';

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
     * Update data siswa binaan oleh Wali Kelas.
     */
    public function updateStudent(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeHomeroomStudent($student);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nis' => ['required', 'string', 'max:50', 'unique:students,nis,'.$student->id],
            'nisn' => ['nullable', 'string', 'max:50', 'unique:students,nisn,'.$student->id],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$student->user_id],
            'password' => ['nullable', 'string', 'min:6'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'address' => ['nullable', 'string'],
            'religion' => ['nullable', 'string', 'max:50'],
            'family_status' => ['nullable', 'string', 'max:50'],
            'child_number' => ['nullable', 'string', 'max:10'],
            'previous_school' => ['nullable', 'string', 'max:150'],
            'admission_date' => ['nullable', 'date'],
            'entry_grade' => ['nullable', 'string', 'max:20'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'father_job' => ['nullable', 'string', 'max:100'],
            'mother_job' => ['nullable', 'string', 'max:100'],
            'parent_address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_job' => ['nullable', 'string', 'max:100'],
            'guardian_address' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'gender.required' => 'Pilih jenis kelamin.',
            'photo.image' => 'File foto harus berupa gambar.',
            'photo.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        $photoPath = $student->photo;

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $photoPath = $request->file('photo')->store('students/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $photoPath = null;
        }

        DB::transaction(function () use ($validated, $student, $photoPath) {
            $userUpdate = ['name' => $validated['name']];
            if (! empty($validated['email'])) {
                $userUpdate['email'] = $validated['email'];
            }
            if (! empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }
            $student->user()->update($userUpdate);

            $student->update([
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'photo' => $photoPath,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'family_status' => $validated['family_status'] ?? null,
                'child_number' => $validated['child_number'] ?? null,
                'previous_school' => $validated['previous_school'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'entry_grade' => $validated['entry_grade'] ?? null,
                'father_name' => $validated['father_name'] ?? null,
                'mother_name' => $validated['mother_name'] ?? null,
                'father_job' => $validated['father_job'] ?? null,
                'mother_job' => $validated['mother_job'] ?? null,
                'parent_address' => $validated['parent_address'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_job' => $validated['guardian_job'] ?? null,
                'guardian_address' => $validated['guardian_address'] ?? null,
            ]);
        });

        return back()->with('success', "Data siswa {$student->user->name} berhasil diperbarui.");
    }

    /**
     * Reset password akun siswa oleh Wali Kelas.
     */
    public function resetPassword(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeHomeroomStudent($student);

        $defaultPassword = ! empty($student->nisn) ? (string) $student->nisn : (! empty($student->nis) ? (string) $student->nis : 'password');
        $label = ! empty($student->nisn) ? 'NISN' : (! empty($student->nis) ? 'NIS' : 'default');

        $student->user()->update([
            'password' => Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Kata sandi siswa {$student->user->name} berhasil di-reset ke {$label} ({$defaultPassword}).");
    }

    /**
     * Verifikasi bahwa siswa merupakan anggota kelas binaan guru yang sedang login.
     */
    protected function authorizeHomeroomStudent(Student $student): Teacher
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Akses ditolak: Anda tidak memiliki profil guru.');
        }

        $isHomeroom = SchoolClass::where('id', $student->school_class_id)
            ->where('homeroom_teacher_id', $teacher->id)
            ->exists();

        if (! $isHomeroom) {
            abort(403, 'Akses ditolak: Siswa ini bukan merupakan siswa di kelas binaan Anda.');
        }

        return $teacher;
    }
}
