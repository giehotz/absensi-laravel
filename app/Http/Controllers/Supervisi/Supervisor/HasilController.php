<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiAspekPenilaian;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use App\Services\Supervisi\SupervisiSignatureService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HasilController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
        private readonly SupervisiSignatureService $signatureService,
    ) {}

    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher?->id;

        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'schoolClass', 'subject', 'signatures'])
            ->where(function ($q) use ($teacherId, $request): void {
                $q->where('supervisor_teacher_id', $teacherId)
                    ->orWhere('supervisor_user_id', $request->user()->id);
            })
            ->where('status', SupervisiStatus::Selesai->value)
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.supervisor.hasil.index', compact('jadwals'));
    }

    public function show(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);

        $jadwal->load([
            'academicYear',
            'teacher.user',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'dokumenAjars',
            'hasils.jenisPenilaian',
            'hasils.detailHasils.aspek',
            'fotoBuktis',
            'tindakLanjut',
            'coachingTirtas',
        ]);

        $signatures = $this->signatureService->getSignaturesByJadwal($jadwal);
        $isFullySigned = $this->signatureService->isFullySigned($jadwal);
        $hasAnySignature = $this->signatureService->hasAnySignature($jadwal);

        $fokusDipilihList = ! empty($jadwal->praObservasi?->fokus_perilaku_dipilih)
            ? SupervisiAspekPenilaian::whereIn('id', $jadwal->praObservasi->fokus_perilaku_dipilih)->orderBy('urutan')->get()
            : collect();

        return view('supervisi.shared.hasil_detail', [
            'jadwal' => $jadwal,
            'signatures' => $signatures,
            'isFullySigned' => $isFullySigned,
            'hasAnySignature' => $hasAnySignature,
            'fokusDipilihList' => $fokusDipilihList,
            'portalPrefix' => 'supervisor',
        ]);
    }
}
