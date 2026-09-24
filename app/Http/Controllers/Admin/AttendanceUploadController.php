<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceUploadBatch;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Services\Attendance\AttendanceImportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceUploadController extends Controller
{
    public function __construct(
        protected AttendanceImportService $importService
    ) {}

    /**
     * Download template presensi Excel untuk kelas terpilih.
     */
    public function downloadTemplate(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'schedule_id' => 'nullable|exists:schedules,id',
            'date' => 'nullable|date',
        ]);

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $schedule = ! empty($validated['schedule_id']) ? Schedule::find($validated['schedule_id']) : null;
        $date = $validated['date'] ?? Carbon::today()->toDateString();

        return $this->importService->downloadTemplate($schoolClass, $schedule, $date);
    }

    /**
     * Unggah dan proses file presensi Excel/CSV.
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

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);
        $schedule = ! empty($validated['schedule_id']) ? Schedule::find($validated['schedule_id']) : null;
        $date = $validated['date'];

        try {
            $result = $this->importService->importFile(
                $request->file('file'),
                $schoolClass,
                $schedule,
                $date,
                Auth::user()
            );

            $successCount = $result['success_count'];
            $failedCount = $result['failed_count'];
            $className = $schoolClass->name;
            $formattedDate = Carbon::parse($date)->translatedFormat('d M Y');

            if ($successCount > 0 && $failedCount === 0) {
                return redirect()->route('admin.attendances.manual', [
                    'school_class_id' => $schoolClass->id,
                    'date' => $date,
                ])->with('success', "Unggah presensi {$className} tanggal {$formattedDate} berhasil! Seluruh {$successCount} siswa tercatat.");
            }

            if ($successCount > 0 && $failedCount > 0) {
                return redirect()->route('admin.attendances.manual', [
                    'school_class_id' => $schoolClass->id,
                    'date' => $date,
                ])->with('warning', "Unggah presensi {$className} selesai sebagian: {$successCount} siswa berhasil, {$failedCount} baris gagal. Cek riwayat upload untuk rincian error.");
            }

            return redirect()->route('admin.attendances.manual', [
                'school_class_id' => $schoolClass->id,
                'date' => $date,
            ])->with('error', "Gagal mengunggah presensi: Seluruh {$failedCount} baris data tidak valid. Silakan periksa kembali template.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.attendances.manual', [
                'school_class_id' => $schoolClass->id,
                'date' => $date,
            ])->with('error', 'Terjadi kesalahan sistem saat memproses file: '.$e->getMessage());
        }
    }

    /**
     * Mengambil daftar riwayat batch upload untuk ditampilkan di modal.
     */
    public function batches(Request $request): JsonResponse
    {
        $classId = $request->input('school_class_id');

        $query = AttendanceUploadBatch::with(['uploadedByUser', 'schoolClass', 'schedule.subject'])
            ->latest();

        if ($classId) {
            $query->where('school_class_id', $classId);
        }

        $batches = $query->limit(20)->get()->map(function ($batch) {
            return [
                'id' => $batch->id,
                'batch_uuid' => $batch->batch_uuid,
                'class_name' => $batch->schoolClass?->name ?? '-',
                'subject_name' => $batch->schedule?->subject?->name ?? 'Presensi Harian',
                'uploader_name' => $batch->uploadedByUser?->name ?? 'Admin',
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
