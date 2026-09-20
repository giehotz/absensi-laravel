<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StudentResource;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentTransferController extends Controller
{
    /**
     * Mengambil daftar siswa dengan pagination dan berbagai filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::with([
            'user',
            'schoolClass.academicYear',
            'schoolClass.homeroomTeacher.user',
        ]);

        $query = $this->applyFilters($query, $request);

        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);
        $students = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil diambil.',
            'meta' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
                'has_more_pages' => $students->hasMorePages(),
            ],
            'data' => StudentResource::collection($students),
        ])->header('X-Records-Count', (string) $students->count());
    }

    /**
     * Mengambil detail 1 siswa berdasarkan NIS atau NISN.
     */
    public function show(string $identifier): JsonResponse
    {
        $student = Student::with([
            'user',
            'schoolClass.academicYear',
            'schoolClass.homeroomTeacher.user',
        ])
            ->where('nis', $identifier)
            ->orWhere('nisn', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => "Siswa dengan identifier '{$identifier}' tidak ditemukan.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data siswa ditemukan.',
            'data' => new StudentResource($student),
        ])->header('X-Records-Count', '1');
    }

    /**
     * Endpoint khusus bulk export / transfer data siswa untuk sinkronisasi massal.
     */
    public function exportTransfer(Request $request): JsonResponse
    {
        $query = Student::with([
            'user',
            'schoolClass.academicYear',
            'schoolClass.homeroomTeacher.user',
        ]);

        $query = $this->applyFilters($query, $request);

        $limit = min(max((int) $request->query('limit', 500), 1), 2000);
        $students = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Data transfer siswa berhasil diekspor.',
            'meta' => [
                'total_exported' => $students->count(),
                'limit_applied' => $limit,
            ],
            'data' => StudentResource::collection($students),
        ])->header('X-Records-Count', (string) $students->count());
    }

    /**
     * Menerapkan filter query request ke builder.
     *
     * @param  Builder<Student>  $query
     * @return Builder<Student>
     */
    protected function applyFilters($query, Request $request)
    {
        // Filter tahun ajaran
        if ($request->filled('academic_year_id')) {
            $query->whereHas('schoolClass', function ($q) use ($request) {
                $q->where('academic_year_id', $request->query('academic_year_id'));
            });
        }

        // Filter ID kelas
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->query('class_id'));
        }

        // Filter nama kelas
        if ($request->filled('class_name')) {
            $query->whereHas('schoolClass', function ($q) use ($request) {
                $q->where('name', $request->query('class_name'));
            });
        }

        // Filter jenis kelamin
        if ($request->filled('gender')) {
            $query->where('gender', strtoupper((string) $request->query('gender')));
        }

        // Filter pencarian nama atau NIS/NISN
        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter sinkronisasi inkremental (hanya data yang diupdate setelah waktu tertentu)
        if ($request->filled('updated_since')) {
            $query->where('updated_at', '>=', $request->query('updated_since'));
        }

        return $query->latest('students.id');
    }
}
