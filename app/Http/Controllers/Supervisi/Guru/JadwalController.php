<?php

namespace App\Http\Controllers\Supervisi\Guru;

use App\Http\Controllers\Controller;
use App\Models\Supervisi\SupervisiJadwal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->teacher->id;

        $jadwals = SupervisiJadwal::with([
            'academicYear',
            'supervisorTeacher.user',
            'supervisorUser',
            'schoolClass',
            'subject',
            'indikatorPmm',
            'praObservasi',
            'dokumenAjars',
            'tindakLanjut',
            'signatures',
        ])
            ->where('teacher_id', $teacherId)
            ->orderByDesc('tanggal')
            ->get();

        return view('supervisi.guru.jadwal', compact('jadwals'));
    }
}
