<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardUjianController extends Controller
{
    public function index(Request $request): View
    {
        $periodes = UjianPeriode::with('jenis')->orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $totalPeserta = 0;
        $totalRuang = UjianRuang::count();
        $totalJadwal = 0;
        $totalPengawasTerplot = 0;
        $panitias = collect();

        if ($activePeriode) {
            $totalPeserta = UjianPeserta::where('ujian_periode_id', $activePeriode->id)->count();
            $totalJadwal = UjianJadwal::where('ujian_periode_id', $activePeriode->id)->count();
            $totalPengawasTerplot = UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->whereNotNull('pengawas_1_id')
                ->count();

            $panitias = UjianPanitia::with('teacher.user')
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderByRaw("CASE jabatan WHEN 'ketua' THEN 1 WHEN 'sekretaris' THEN 2 WHEN 'bendahara' THEN 3 ELSE 4 END")
                ->get();
        }

        $teachers = Teacher::with('user')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn ($t) => $t->user?->name ?? '')
            ->values();

        return view('ujian.dashboard', compact(
            'periodes',
            'activePeriode',
            'totalPeserta',
            'totalRuang',
            'totalJadwal',
            'totalPengawasTerplot',
            'panitias',
            'teachers'
        ));
    }
}
