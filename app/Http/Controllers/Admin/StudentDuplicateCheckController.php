<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDuplicateConflict;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentDuplicateCheckController extends Controller
{
    /**
     * Menampilkan daftar konflik duplikasi NIS siswa beserta saran tindakan manual untuk admin.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = StudentDuplicateConflict::with([
            'student.user',
            'student.schoolClass',
            'conflictWith.user',
            'conflictWith.schoolClass',
            'resolvedBy',
        ])->latest();

        if (in_array($status, ['pending', 'resolved'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('duplicate_nis', 'like', "%{$search}%")
                    ->orWhere('assigned_temp_nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('student.user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('conflictWith.user', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $conflicts = $query->paginate(20)->withQueryString();

        // Mengelompokkan data berdasarkan duplicate_nis untuk tampilan perbandingan kelompok konflik
        $groupedConflicts = $conflicts->getCollection()->groupBy('duplicate_nis');

        // Statistik ringkasan
        $stats = [
            'total_cases' => StudentDuplicateConflict::distinct('duplicate_nis')->count('duplicate_nis'),
            'total_students' => StudentDuplicateConflict::distinct('student_id')->count('student_id'),
            'pending' => StudentDuplicateConflict::where('status', 'pending')->count(),
            'resolved' => StudentDuplicateConflict::where('status', 'resolved')->count(),
        ];

        $totalStudentsCount = Student::count();

        return view('admin.students.cek_duplikat', compact(
            'conflicts',
            'groupedConflicts',
            'stats',
            'status',
            'search',
            'totalStudentsCount'
        ));
    }

    /**
     * Memverifikasi dan memperbarui NIS siswa yang berkonflik secara manual.
     */
    public function resolve(Request $request, StudentDuplicateConflict $conflict): RedirectResponse
    {
        $student = $conflict->student;

        if (! $student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $validated = $request->validate([
            'nis' => [
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'nis')->ignore($student->id),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'nis.required' => 'Nomor NIS baru wajib diisi.',
            'nis.unique' => 'Nomor NIS ini sudah digunakan oleh siswa lain di database.',
        ]);

        $oldNis = $student->nis;
        $newNis = trim($validated['nis']);
        $notes = ! empty($validated['notes'])
            ? trim($validated['notes'])
            : "NIS diperbarui manual oleh admin dari '{$oldNis}' menjadi '{$newNis}' setelah verifikasi NISN ({$student->nisn}).";

        DB::transaction(function () use ($conflict, $student, $newNis, $notes) {
            // Update NIS resmi siswa
            $student->update([
                'nis' => $newNis,
            ]);

            // Update status konflik menjadi resolved
            $conflict->update([
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_notes' => $notes,
            ]);

            // Cek apakah ada konflik lain dalam kelompok NIS yang sama yang kini bisa diselesaikan otomatis
            $duplicateNis = $conflict->duplicate_nis;
            $studentsStillUsingDuplicateNis = Student::where('nis', $duplicateNis)->count();

            if ($studentsStillUsingDuplicateNis <= 1) {
                // Siswa lain di grup yang memang berhak atas NIS asli ditandai terselesaikan
                $remainingConflicts = StudentDuplicateConflict::where('duplicate_nis', $duplicateNis)
                    ->where('status', 'pending')
                    ->get();

                foreach ($remainingConflicts as $otherConflict) {
                    if ($otherConflict->student && $otherConflict->student->nis === $duplicateNis) {
                        $otherConflict->update([
                            'status' => 'resolved',
                            'resolved_by' => auth()->id(),
                            'resolved_at' => now(),
                            'resolution_notes' => "Konflik selesai otomatis: siswa {$student->user->name} telah dipindahkan ke NIS '{$newNis}'. Siswa ini sah menggunakan NIS '{$duplicateNis}'.",
                        ]);
                    }
                }
            }
        });

        return back()->with('success', "NIS siswa {$student->user->name} berhasil diperbarui menjadi {$newNis} dan status konflik telah diselesaikan!");
    }

    /**
     * Memindai ulang database dan mendeteksi inkonsistensi nomor NIS.
     */
    public function scan(): RedirectResponse
    {
        // 1. Deteksi siswa yang menggunakan NIS sementara (misal: format sama dengan NISN atau terdapat pembeda sufiks)
        $potentialTempStudents = Student::whereNotNull('nisn')
            ->whereColumn('nis', 'nisn')
            ->with(['user', 'schoolClass'])
            ->get();

        $foundCount = 0;

        foreach ($potentialTempStudents as $student) {
            // Cek apakah ada siswa lain di kelas manapun yang memiliki nomor NIS yang semestinya sama di SIS-EMIS
            $conflict = StudentDuplicateConflict::where('student_id', $student->id)->first();
            if (! $conflict) {
                StudentDuplicateConflict::create([
                    'duplicate_nis' => $student->nisn,
                    'student_id' => $student->id,
                    'assigned_temp_nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nik' => $student->nik,
                    'source' => 'system_scan',
                    'status' => 'pending',
                    'resolution_notes' => 'Terdeteksi siswa menggunakan nomor NISN sebagai nomor NIS lokal. Disarankan untuk memverifikasi nomor NIS resmi madrasah.',
                ]);
                $foundCount++;
            }
        }

        // 2. Evaluasi konflik yang sudah tidak bentrok lagi di tabel students
        $pendingConflicts = StudentDuplicateConflict::where('status', 'pending')->with('student')->get();
        $autoResolvedCount = 0;

        foreach ($pendingConflicts as $conf) {
            if (! $conf->student) {
                continue;
            }

            // Jika siswa sudah memiliki NIS yang berbeda dari duplicate_nis dan assigned_temp_nis serta unik
            if ($conf->student->nis !== $conf->duplicate_nis && $conf->student->nis !== $conf->assigned_temp_nis) {
                $conf->update([
                    'status' => 'resolved',
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'resolution_notes' => "Terselesaikan otomatis melalui pemindaian sistem (NIS siswa saat ini: {$conf->student->nis}).",
                ]);
                $autoResolvedCount++;
            }
        }

        $message = "Pemindaian selesai. {$foundCount} catatan baru dicatat, {$autoResolvedCount} konflik otomatis diselesaikan.";

        return back()->with('success', $message);
    }

    /**
     * Menghapus catatan riwayat konflik duplikasi.
     */
    public function destroy(StudentDuplicateConflict $conflict): RedirectResponse
    {
        $conflict->delete();

        return back()->with('success', 'Catatan log konflik berhasil dihapus.');
    }
}
