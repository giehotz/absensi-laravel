<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceUploadBatch;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\Attendance\AttendanceImportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceUploadController extends Controller
{
    public function __construct(
        protected AttendanceImportService $importService
    ) {}

    /**
     * Helper untuk mengambil kelas yang diizinkan untuk guru ini.
     */
    protected function getAllowedClassIds(Teacher $teacher): Collection
    {
        $homeroomClassIds = SchoolClass::where('homeroom_teacher_id', $teacher->id)->pluck('id');
        $teachingClassIds = Schedule::where('teacher_id', $teacher->id)->pluck('school_class_id');

        return $homeroomClassIds->merge($teachingClassIds)->unique();
    }

    /**
     * Download template presensi Excel untuk kelas terpilih (Guru).
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'date' => 'nullable|date',
        ]);

        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $allowedClassIds = $this->getAllowedClassIds($teacher);
        if (! $allowedClassIds->contains((int) $validated['school_class_id'])) {
            abort(403, 'Anda tidak memiliki hak akses mengunduh template untuk kelas ini.');
        }

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $schedule = ! empty($validated['schedule_id']) ? Schedule::find($validated['schedule_id']) : null;
        $date = $validated['date'] ?? Carbon::today()->toDateString();

        return $this->importService->downloadTemplate($schoolClass, $schedule, $date);
    }

    /**
     * Unggah dan proses file presensi Excel/CSV (Guru).
     */
    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'school_class_id' => 'required|exists:school_classes,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'date' => 'required|date',
        ], [
            'file.required' => 'File Excel/CSV presensi wajib diunggah.',
            'file.mimes' => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal adalah 5MB.',
            'school_class_id.required' => 'Pilih kelas tujuan presensi.',
            'date.required' => 'Pilih tanggal presensi.',
        ]);

        $user = Auth::user();
        $teacher = $user->teacher ?? Teacher::firstOrCreate(
            ['user_id' => $user->id],
            ['nip' => 'GURU-DEMO', 'phone' => '081234567800']
        );

        $allowedClassIds = $this->getAllowedClassIds($teacher);
        if (! $allowedClassIds->contains((int) $validated['school_class_id'])) {
            abort(403, 'Anda tidak memiliki hak akses mengunggah presensi untuk kelas ini.');
        }

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $schedule = ! empty($validated['schedule_id']) ? Schedule::find($validated['schedule_id']) : null;
        $date = $validated['date'];

        try {
            $result = $this->importService->importFile(
                $request->file('file'),
                $schoolClass,
                $schedule,
                $date,
                $user
            );

            $successCount = $result['success_count'];
            $failedCount = $result['failed_count'];
            $className = $schoolClass->name;
            $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

            if ($successCount > 0 && $failedCount === 0) {
                return redirect()->route('guru.attendance.manual', [
                    'school_class_id' => $schoolClass->id,
                    'date' => $date,
                ])->with('success', "Unggah presensi {$className} tanggal {$formattedDate} berhasil! Seluruh {$successCount} siswa tercatat.");
            }

            if ($successCount > 0 && $failedCount > 0) {
                return redirect()->route('guru.attendance.manual', [
                    'school_class_id' => $schoolClass->id,
                    'date' => $date,
                ])->with('warning', "Unggah presensi {$className} selesai sebagian: {$successCount} siswa berhasil, {$failedCount} baris gagal. Cek riwayat upload untuk rincian error.");
            }

            return redirect()->route('guru.attendance.manual', [
                'school_class_id' => $schoolClass->id,
                'date' => $date,
            ])->with('error', "Gagal mengunggah presensi: Seluruh {$failedCount} baris data tidak valid. Silakan periksa kembali template.");
        } catch (\Throwable $e) {
            return redirect()->route('guru.attendance.manual', [
                'school_class_id' => $schoolClass->id,
                'date' => $date,
            ])->with('error', 'Terjadi kesalahan sistem saat memproses file: '.$e->getMessage());
        }
    }

    /**
     * Mengambil daftar riwayat batch upload milik guru.
     */
    public function batches(Request $request): JsonResponse
    {
        $user = Auth::user();
        $classId = $request->input('school_class_id');

        $query = AttendanceUploadBatch::with(['schoolClass', 'schedule.subject'])
            ->where('uploaded_by', $user->id)
            ->latest();

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        $batches = $query->limit(20)->get()->map(function ($batch) use ($user) {
            return [
                'id' => $batch->id,
                'batch_uuid' => $batch->batch_uuid,
                'class_name' => $batch->schoolClass?->name ?? '-',
                'subject_name' => $batch->schedule?->subject?->name ?? 'Presensi Harian',
                'uploader_name' => $user->name,
                'date' => $batch->date?->translatedFormat('d M Y') ?? '-',
                'original_filename' => $batch->original_filename,
                'total_rows' => $batch->total_rows,
                'success_rows' => $batch->success_rows,
                'failed_rows' => $batch->failed_rows,
                'status' => $batch->status,
                'error_log' => $batch->error_log ?? [],
                'created_at' => $batch->created_at?->diffForHumans() ?? '-',
            ];
        });

        return response()->json(['batches' => $batches]);
    }
}
