<?php

namespace App\Http\Controllers\Supervisi\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuruBinaanController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher?->id;

        $jadwals = SupervisiJadwal::with([
            'academicYear',
            'teacher.user',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'tindakLanjut',
            'signatures',
        ])
            ->where(function ($q) use ($teacherId, $request): void {
                $q->where('supervisor_teacher_id', $teacherId)
                    ->orWhere('supervisor_user_id', $request->user()->id);
            })
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.supervisor.guru_binaan', compact('jadwals'));
    }
}
