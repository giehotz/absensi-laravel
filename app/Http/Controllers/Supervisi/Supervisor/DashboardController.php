<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher?->id;

        $jadwals = SupervisiJadwal::with(['teacher.user', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'tindakLanjut', 'signatures'])
            ->where(function ($q) use ($teacherId, $request): void {
                $q->where('supervisor_teacher_id', $teacherId)
                    ->orWhere('supervisor_user_id', $request->user()->id);
            })
            ->orderByDesc('tanggal')
            ->get();

        $stats = [
            'total' => $jadwals->count(),
            'menunggu_pra' => $jadwals->whereIn('status', [SupervisiStatus::Terjadwal->value, SupervisiStatus::PraObservasi->value])->count(),
            'siap_observasi' => $jadwals->where('status', SupervisiStatus::Observasi->value)->count(),
            'tindak_lanjut' => $jadwals->where('status', SupervisiStatus::PascaObservasi->value)->count(),
            'selesai' => $jadwals->where('status', SupervisiStatus::Selesai->value)->count(),
        ];

        return view('supervisi.supervisor.dashboard', compact('jadwals', 'stats'));
    }
}
