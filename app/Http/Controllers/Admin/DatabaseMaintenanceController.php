<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\AttendanceArchive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseMaintenanceController extends Controller
{
    /**
     * Mengambil pratinjau data presensi tahun ajaran untuk ditampilkan di modal edukasi.
     */
    public function preview(AcademicYear $academicYear): JsonResponse
    {
        $activeCount = Attendance::whereDate('date', '>=', $academicYear->start_date)
            ->whereDate('date', '<=', $academicYear->end_date)
            ->count();

        $archivedCount = AttendanceArchive::where('academic_year_id', $academicYear->id)
            ->orWhere(function ($q) use ($academicYear) {
                $q->whereNull('academic_year_id')
                    ->whereDate('date', '>=', $academicYear->start_date)
                    ->whereDate('date', '<=', $academicYear->end_date);
            })
            ->count();

        $classesCount = $academicYear->schoolClasses()->count();

        return response()->json([
            'id' => $academicYear->id,
            'name' => $academicYear->name,
            'semester' => $academicYear->semester,
            'start_date' => $academicYear->start_date?->translatedFormat('d M Y') ?? '-',
            'end_date' => $academicYear->end_date?->translatedFormat('d M Y') ?? '-',
            'is_active' => (bool) $academicYear->is_active,
            'active_count' => $activeCount,
            'archived_count' => $archivedCount,
            'classes_count' => $classesCount,
            'can_archive' => ! $academicYear->is_active && $activeCount > 0,
            'can_restore' => $archivedCount > 0,
        ]);
    }

    /**
     * Memindahkan data presensi dari tabel aktif (attendances) ke tabel arsip (attendances_archive).
     */
    public function archive(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ], [
            'academic_year_id.required' => 'Tahun Ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun Ajaran tidak ditemukan.',
        ]);

        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        if ($academicYear->is_active) {
            return redirect()->back()->with('error', 'Tidak dapat mengarsipkan Tahun Ajaran yang sedang Aktif! Harap nonaktifkan atau ganti tahun ajaran aktif terlebih dahulu.');
        }

        $attendances = Attendance::whereDate('date', '>=', $academicYear->start_date)
            ->whereDate('date', '<=', $academicYear->end_date)
            ->get();

        if ($attendances->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada data presensi aktif pada rentang tanggal Tahun Ajaran {$academicYear->name} ({$academicYear->semester}).");
        }

        $totalCount = $attendances->count();

        DB::transaction(function () use ($attendances, $academicYear) {
            $archiveData = $attendances->map(function ($att) use ($academicYear) {
                return [
                    'student_id' => $att->student_id,
                    'schedule_id' => $att->schedule_id,
                    'academic_year_id' => $academicYear->id,
                    'date' => $att->date?->toDateString(),
                    'check_in_time' => $att->check_in_time?->toDateTimeString(),
                    'check_out_time' => $att->check_out_time?->toDateTimeString(),
                    'status' => $att->status,
                    'method' => $att->method,
                    'recorded_by' => $att->recorded_by,
                    'notes' => $att->notes,
                    'archived_at' => now(),
                    'created_at' => $att->created_at,
                    'updated_at' => $att->updated_at,
                ];
            })->all();

            foreach (array_chunk($archiveData, 500) as $chunk) {
                AttendanceArchive::insert($chunk);
            }

            Attendance::whereIn('id', $attendances->pluck('id'))->delete();
        });

        return redirect()->back()->with('success', "Sukses! Sebanyak {$totalCount} catatan presensi Tahun Ajaran {$academicYear->name} berhasil dipindahkan ke tabel arsip. Beban tabel aktif berhasil diringankan!");
    }

    /**
     * Memulihkan data presensi dari tabel arsip (attendances_archive) kembali ke tabel aktif (attendances).
     */
    public function restore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
        ], [
            'academic_year_id.required' => 'Tahun Ajaran wajib dipilih.',
            'academic_year_id.exists' => 'Tahun Ajaran tidak ditemukan.',
        ]);

        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        $archives = AttendanceArchive::where('academic_year_id', $academicYear->id)
            ->orWhere(function ($q) use ($academicYear) {
                $q->whereNull('academic_year_id')
                    ->whereDate('date', '>=', $academicYear->start_date)
                    ->whereDate('date', '<=', $academicYear->end_date);
            })
            ->get();

        if ($archives->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ditemukan data arsip untuk Tahun Ajaran {$academicYear->name} ({$academicYear->semester}).");
        }

        $totalCount = $archives->count();

        DB::transaction(function () use ($archives) {
            $restoreData = $archives->map(function ($arch) {
                return [
                    'student_id' => $arch->student_id,
                    'schedule_id' => $arch->schedule_id,
                    'date' => $arch->date?->toDateString(),
                    'check_in_time' => $arch->check_in_time?->toDateTimeString(),
                    'check_out_time' => $arch->check_out_time?->toDateTimeString(),
                    'status' => $arch->status,
                    'method' => $arch->method,
                    'recorded_by' => $arch->recorded_by,
                    'notes' => $arch->notes,
                    'created_at' => $arch->created_at,
                    'updated_at' => $arch->updated_at,
                ];
            })->all();

            foreach (array_chunk($restoreData, 500) as $chunk) {
                Attendance::insert($chunk);
            }

            AttendanceArchive::whereIn('id', $archives->pluck('id'))->delete();
        });

        return redirect()->back()->with('success', "Sukses! Sebanyak {$totalCount} catatan presensi Tahun Ajaran {$academicYear->name} berhasil dipulihkan kembali ke tabel utama!");
    }

    /**
     * Menjalankan proses defragmentasi dan optimalisasi indeks penyimpanan tabel database.
     */
    public function optimize(Request $request): RedirectResponse
    {
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('OPTIMIZE TABLE attendances');
                DB::statement('OPTIMIZE TABLE attendances_archive');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA optimize;');
            } elseif ($driver === 'pgsql') {
                DB::statement('VACUUM ANALYZE attendances');
                DB::statement('VACUUM ANALYZE attendances_archive');
            }

            return redirect()->back()->with('success', 'Optimalisasi database selesai! Indeks penyimpanan telah ditata ulang dan fragmentasi disk storage berhasil dibersihkan.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal melakukan optimalisasi tabel: '.$th->getMessage());
        }
    }
}
