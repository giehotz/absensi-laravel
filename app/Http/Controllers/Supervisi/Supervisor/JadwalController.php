<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
use App\Services\Supervisi\SupervisiCycleGuardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function __construct(
        private readonly SupervisiCycleGuardService $guard,
    ) {}

    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher?->id;

        $jadwals = SupervisiJadwal::with(['academicYear', 'teacher.user', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'tindakLanjut', 'signatures'])
            ->where(function ($q) use ($teacherId, $request): void {
                $q->where('supervisor_teacher_id', $teacherId)
                    ->orWhere('supervisor_user_id', $request->user()->id);
            })
            ->orderBy('tanggal')
            ->get();

        return view('supervisi.supervisor.jadwal.index', compact('jadwals'));
    }

    public function show(Request $request, SupervisiJadwal $jadwal): View
    {
        $this->guard->ensureSupervisorOwnsJadwal($request->user(), $jadwal);
        $jadwal->load(['academicYear', 'teacher.user', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'dokumenAjars', 'hasils.jenisPenilaian', 'fotoBuktis', 'tindakLanjut', 'coachingTirtas', 'signatures']);

        return view('supervisi.supervisor.jadwal.show', compact('jadwal'));
    }
}
