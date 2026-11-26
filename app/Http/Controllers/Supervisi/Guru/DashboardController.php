<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Enums\SupervisiStatus;
use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;

        $jadwals = SupervisiJadwal::with(['academicYear', 'supervisorTeacher.user', 'supervisorUser', 'schoolClass', 'subject', 'indikatorPmm', 'praObservasi', 'tindakLanjut', 'signatures'])
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('tanggal')
            ->get();

        $selesai = $jadwals->where('status', SupervisiStatus::Selesai->value);
        $avgScore = $selesai->whereNotNull('nilai_akhir')->avg('nilai_akhir');

        $stats = [
            'total' => $jadwals->count(),
            'mendatang' => $jadwals->where('status', '!=', SupervisiStatus::Selesai->value)->where('status', '!=', SupervisiStatus::Batal->value)->count(),
            'selesai' => $selesai->count(),
            'rata_rata' => $avgScore !== null ? round((float) $avgScore, 2) : 0.0,
        ];

        return view('supervisi.guru.dashboard', compact('jadwals', 'stats', 'teacher'));
    }
}
