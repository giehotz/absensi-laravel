<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\Student;

class StudentCardService
{
    public function __construct(
        protected QrCodeService $qrCodeService
    ) {}

    /**
     * Mengambil pengaturan kartu aktif dengan fallback nilai default.
     */
    public function getSetting(): AttendanceSetting
    {
        return AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'MIN 2 TANGGAMUS',
            'card_school_name' => 'MIN 2 TANGGAMUS',
            'card_title' => 'KARTU SISWA',
            'card_validity_text' => 'BERLAKU SELAMA MENJADI SISWA',
            'card_back_instructions' => 'Arahkan layar ponsel ini ke kamera scanner sekolah. Pastikan kecerahan layar maksimal.',
            'card_width_cm' => 8.70,
            'card_height_cm' => 5.40,
            'card_theme_color' => '#20C997',
            'card_show_back_token' => true,
            'card_show_signature' => false,
        ]);
    }

    /**
     * Menghasilkan URL publik untuk memverifikasi keabsahan kartu siswa.
     */
    public function getVerificationUrl(Student $student): string
    {
        return route('public.verify.student', ['identifier' => $student->qr_code_identifier]);
    }

    /**
     * Menghasilkan Data URI SVG QR Code Bagian Depan (URL Verifikasi Publik).
     */
    public function getFrontQrDataUri(Student $student, int $size = 180): string
    {
        $url = $this->getVerificationUrl($student);

        return $this->qrCodeService->generateDataUri($url, $size, 1);
    }

    /**
     * Menghasilkan Data URI SVG QR Code Bagian Belakang (Identifier Scan Presensi Masuk).
     */
    public function getBackQrDataUri(Student $student, int $size = 240): string
    {
        return $this->qrCodeService->generateDataUri($student->qr_code_identifier, $size, 2);
    }

    /**
     * Mempersiapkan data siswa agar siap dirender pada komponen kartu tanda pelajar.
     */
    public function prepareStudent(Student $student): Student
    {
        if (! $student->relationLoaded('user') || ! $student->relationLoaded('schoolClass')) {
            $student->load(['user', 'schoolClass']);
        }

        $student->verification_url = $this->getVerificationUrl($student);
        $student->front_qr_data_uri = $this->getFrontQrDataUri($student);
        $student->back_qr_data_uri = $this->getBackQrDataUri($student);

        return $student;
    }
}
