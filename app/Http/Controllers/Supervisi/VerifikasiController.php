<?php

namespace App\Http\Controllers\Supervisi;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Supervisi\SupervisiDokumenSignature;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function __construct(
        private readonly SupervisiSignatureService $signatureService,
    ) {}

    public function show(string $token): View
    {
        $target = SupervisiDokumenSignature::with([
            'jadwal.academicYear',
            'jadwal.teacher.user',
            'jadwal.supervisorTeacher.user',
            'jadwal.supervisorUser',
            'jadwal.schoolClass',
            'jadwal.subject',
            'jadwal.indikatorPmm',
        ])->where('token_verifikasi', $token)->first();

        $setting = AttendanceSetting::query()->first();

        if (! $target || ! $target->jadwal) {
            return view('supervisi.verifikasi.show', [
                'isValid' => false,
                'token' => $token,
                'targetSignature' => null,
                'jadwal' => null,
                'allSignatures' => [],
                'isFullySigned' => false,
                'setting' => $setting,
            ]);
        }

        $allSignatures = $this->signatureService->getSignaturesByJadwal($target->jadwal, $target->dokumen_type);

        return view('supervisi.verifikasi.show', [
            'isValid' => true,
            'token' => $token,
            'targetSignature' => $target,
            'jadwal' => $target->jadwal,
            'allSignatures' => $allSignatures,
            'isFullySigned' => ! empty($allSignatures['kepala']),
            'setting' => $setting,
        ]);
    }
}
