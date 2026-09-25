<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentCardService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    public function __construct(
        protected StudentCardService $studentCardService
    ) {}

    /**
     * Memverifikasi keabsahan kartu tanda pelajar dari scan QR code bagian depan.
     */
    public function verifyStudent(string $identifier): Response|View
    {
        $setting = $this->studentCardService->getSetting();

        // Cari siswa berdasarkan identifier unik QR atau NIS
        $student = Student::with(['user', 'schoolClass'])
            ->where('qr_code_identifier', $identifier)
            ->orWhere('nis', $identifier)
            ->first();

        $verifiedAt = Carbon::now('Asia/Jakarta');

        if (! $student) {
            return response()->view('public.verify-student', [
                'isValid' => false,
                'student' => null,
                'identifier' => $identifier,
                'setting' => $setting,
                'verifiedAt' => $verifiedAt,
            ], 404);
        }

        $student = $this->studentCardService->prepareStudent($student);

        return view('public.verify-student', [
            'isValid' => true,
            'student' => $student,
            'identifier' => $identifier,
            'setting' => $setting,
            'verifiedAt' => $verifiedAt,
        ]);
    }
}
