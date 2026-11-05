<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuangUjianController extends Controller
{
    public function index(): View
    {
        $ruangs = UjianRuang::with('schoolClass')
            ->withCount(['pesertas', 'jadwalPengawas'])
            ->orderBy('kode_ruang')
            ->get();

        $totalKelas = SchoolClass::count();
        $totalKelasNon6 = SchoolClass::where('level', '!=', '6')->count();

        return view('ujian.ruang.index', compact('ruangs', 'totalKelas', 'totalKelasNon6'));
    }

    public function syncFromClasses(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:all,exclude_grade_6'],
        ]);

        $query = SchoolClass::withCount('students')->orderBy('level')->orderBy('name');

        if ($validated['mode'] === 'exclude_grade_6') {
            $query->where('level', '!=', '6');
        }

        $classes = $query->get();
        $count = 0;

        foreach ($classes as $class) {
            $kodeRuang = 'RK-'.strtoupper(str_replace([' ', '.'], '', $class->name));
            $kapasitas = $class->students_count > 0 ? $class->students_count : 24;

            $ruang = UjianRuang::updateOrCreate(
                ['kode_ruang' => $kodeRuang],
                [
                    'nama_ruang' => 'Ruang '.$class->name,
                    'kapasitas_maksimal' => $kapasitas,
                    'tipe_ruang' => 'kelas',
                    'school_class_id' => $class->id,
                    'lokasi_gedung' => 'Gedung Kelas '.$class->level,
                    'keterangan' => 'Ruang Kelas Rombel '.$class->name,
                ]
            );

            // Sinkronkan peserta ujian yang berada pada rombel kelas ini ke ruang yang baru dibuat
            UjianPeserta::where('school_class_id', $class->id)
                ->update(['ujian_ruang_id' => $ruang->id]);

            $count++;
        }

        $keteranganMode = $validated['mode'] === 'exclude_grade_6'
            ? 'Kecuali Kelas 6 (Khusus persiapan UAM)'
            : 'Semua Rombel (Kelas 1 s/d 6)';

        return back()->with('success', "Sinkronisasi berhasil! Sebanyak {$count} ruang kelas telah disinkronkan ({$keteranganMode}).");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_ruang' => ['required', 'string', 'max:30', 'unique:ujian_ruang,kode_ruang'],
            'nama_ruang' => ['required', 'string', 'max:100'],
            'kapasitas_maksimal' => ['required', 'integer', 'min:1', 'max:100'],
            'tipe_ruang' => ['nullable', 'in:kelas,manual'],
            'lokasi_gedung' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['tipe_ruang'] = $validated['tipe_ruang'] ?? 'manual';
        $validated['school_class_id'] = null;

        UjianRuang::create($validated);

        return back()->with('success', 'Ruang Ujian baru berhasil ditambahkan.');
    }

    public function update(Request $request, UjianRuang $ruang): RedirectResponse
    {
        $validated = $request->validate([
            'kode_ruang' => ['required', 'string', 'max:30', 'unique:ujian_ruang,kode_ruang,'.$ruang->id],
            'nama_ruang' => ['required', 'string', 'max:100'],
            'kapasitas_maksimal' => ['required', 'integer', 'min:1', 'max:100'],
            'lokasi_gedung' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $ruang->update($validated);

        return back()->with('success', 'Data Ruang Ujian berhasil diperbarui.');
    }

    public function destroy(UjianRuang $ruang): RedirectResponse
    {
        $ruang->delete();

        return back()->with('success', 'Ruang Ujian berhasil dihapus.');
    }
}
