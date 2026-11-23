<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengawasAbsensiAdminController extends Controller
{
    /**
     * Tampilkan monitoring presensi sesi pengawas & rekapitulasi kehadiran pengawas.
     */
    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId) ?? $periodes->first();

        $duties = collect();
        $rekapTeachers = collect();

        if ($activePeriode) {
            $query = UjianJadwalPengawas::with([
                'jadwal.subject',
                'jadwal.schoolClass',
                'ruang.schoolClass',
                'pengawas1.user',
                'pengawas2.user',
                'beritaAcara.pengawasPengisi.user',
            ])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->join('ujian_jadwal', 'ujian_jadwal_pengawas.ujian_jadwal_id', '=', 'ujian_jadwal.id')
                ->orderBy('ujian_jadwal.tanggal_ujian', 'asc')
                ->orderBy('ujian_jadwal.jam_mulai', 'asc')
                ->select('ujian_jadwal_pengawas.*');

            if ($request->filled('tanggal')) {
                $query->whereDate('ujian_jadwal.tanggal_ujian', $request->query('tanggal'));
            }

            if ($request->filled('ruang_id')) {
                $query->where('ujian_jadwal_pengawas.ujian_ruang_id', $request->query('ruang_id'));
            }

            $duties = $query->get();

            // Hitung rekap kehadiran per guru pada periode ini
            $allDutiesInPeriod = UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))->get();

            $teacherIds = $allDutiesInPeriod->pluck('pengawas_1_id')
                ->merge($allDutiesInPeriod->pluck('pengawas_2_id'))
                ->filter()
                ->unique();

            $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get()->sortBy(fn ($t) => $t->user?->name);

            $rekapTeachers = $teachers->map(function ($teacher) use ($allDutiesInPeriod) {
                $tDuties = $allDutiesInPeriod->filter(fn ($d) => (int) $d->pengawas_1_id === (int) $teacher->id || (int) $d->pengawas_2_id === (int) $teacher->id);

                $totalSesi = $tDuties->count();
                $hadir = 0;
                $izin = 0;
                $sakit = 0;
                $alpa = 0;
                $digantikan = 0;

                foreach ($tDuties as $duty) {
                    $status = ((int) $duty->pengawas_1_id === (int) $teacher->id) ? ($duty->status_p1 ?: $duty->status_kehadiran) : ($duty->status_p2 ?: $duty->status_kehadiran);
                    match ($status) {
                        'Hadir' => $hadir++,
                        'Izin' => $izin++,
                        'Sakit' => $sakit++,
                        'Alpa' => $alpa++,
                        'Digantikan' => $digantikan++,
                        default => null,
                    };
                }

                return [
                    'teacher' => $teacher,
                    'total_sesi' => $totalSesi,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'digantikan' => $digantikan,
                    'persen_hadir' => $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 0,
                ];
            });
        }

        $ruangs = UjianRuang::orderBy('nama_ruang')->get();
        $dates = UjianJadwal::where('ujian_periode_id', $activePeriode?->id)
            ->distinct()
            ->orderBy('tanggal_ujian')
            ->pluck('tanggal_ujian')
            ->filter();

        $totalSlots = $duties->count() * 2;
        $totalHadir = $duties->sum(fn ($d) => ($d->status_p1 === 'Hadir' ? 1 : 0) + ($d->status_p2 === 'Hadir' ? 1 : 0));
        $totalAbsen = $duties->sum(fn ($d) => (in_array($d->status_p1, ['Izin', 'Sakit', 'Alpa', 'Digantikan'], true) ? 1 : 0) + (in_array($d->status_p2, ['Izin', 'Sakit', 'Alpa', 'Digantikan'], true) ? 1 : 0));
        $totalBeritaAcara = $duties->filter(fn ($d) => $d->beritaAcara !== null)->count();

        return view('ujian.pengawas.presensi', compact(
            'periodes',
            'activePeriode',
            'duties',
            'rekapTeachers',
            'ruangs',
            'dates',
            'totalSlots',
            'totalHadir',
            'totalAbsen',
            'totalBeritaAcara'
        ));
    }

    /**
     * Update status kehadiran pengawas ruang dan penunjukan pengawas pengganti.
     */
    public function update(UjianJadwalPengawas $jadwalPengawas, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status_p1' => 'nullable|in:Terjadwal,Hadir,Izin,Sakit,Alpa,Digantikan',
            'pengawas_pengganti_1' => 'nullable|string|max:150',
            'status_p2' => 'nullable|in:Terjadwal,Hadir,Izin,Sakit,Alpa,Digantikan',
            'pengawas_pengganti_2' => 'nullable|string|max:150',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($request->has('status_p1')) {
            $jadwalPengawas->status_p1 = $validated['status_p1'];
            if ($validated['status_p1'] === 'Hadir' && ! $jadwalPengawas->check_in_p1_at) {
                $jadwalPengawas->check_in_p1_at = now();
            }
            $jadwalPengawas->pengawas_pengganti_1 = $validated['pengawas_pengganti_1'] ?? null;
        }

        if ($request->has('status_p2')) {
            $jadwalPengawas->status_p2 = $validated['status_p2'];
            if ($validated['status_p2'] === 'Hadir' && ! $jadwalPengawas->check_in_p2_at) {
                $jadwalPengawas->check_in_p2_at = now();
            }
            $jadwalPengawas->pengawas_pengganti_2 = $validated['pengawas_pengganti_2'] ?? null;
        }

        if ($request->has('catatan')) {
            $jadwalPengawas->catatan = $validated['catatan'];
        }

        $jadwalPengawas->syncLegacyStatusKehadiran();
        $jadwalPengawas->save();

        return back()->with('success', 'Status kehadiran pengawas berhasil diperbarui.');
    }

    /**
     * Cetak Dokumen Rekapitulasi Presensi Pengawas Ujian (PDF Resmi dengan Kop Surat).
     */
    public function printRekap(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::first());

        $rekapTeachers = collect();
        if ($activePeriode) {
            $allDutiesInPeriod = UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))->get();

            $teacherIds = $allDutiesInPeriod->pluck('pengawas_1_id')
                ->merge($allDutiesInPeriod->pluck('pengawas_2_id'))
                ->filter()
                ->unique();

            $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get()->sortBy(fn ($t) => $t->user?->name);

            $rekapTeachers = $teachers->map(function ($teacher) use ($allDutiesInPeriod) {
                $tDuties = $allDutiesInPeriod->filter(fn ($d) => (int) $d->pengawas_1_id === (int) $teacher->id || (int) $d->pengawas_2_id === (int) $teacher->id);

                $totalSesi = $tDuties->count();
                $hadir = 0;
                $izin = 0;
                $sakit = 0;
                $alpa = 0;
                $digantikan = 0;

                foreach ($tDuties as $duty) {
                    $status = ((int) $duty->pengawas_1_id === (int) $teacher->id) ? ($duty->status_p1 ?: $duty->status_kehadiran) : ($duty->status_p2 ?: $duty->status_kehadiran);
                    match ($status) {
                        'Hadir' => $hadir++,
                        'Izin' => $izin++,
                        'Sakit' => $sakit++,
                        'Alpa' => $alpa++,
                        'Digantikan' => $digantikan++,
                        default => null,
                    };
                }

                return [
                    'teacher' => $teacher,
                    'total_sesi' => $totalSesi,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpa' => $alpa,
                    'digantikan' => $digantikan,
                    'persen_hadir' => $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 0,
                ];
            });
        }

        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        return view('ujian.pengawas.print-rekap-presensi', compact('rekapTeachers', 'activePeriode', 'setting', 'ttdSetting'));
    }

    /**
     * Ekspor Rekapitulasi Presensi Pengawas ke format Spreadsheet CSV / Excel untuk SPJ Panitia.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::first());

        $allDutiesInPeriod = $activePeriode
            ? UjianJadwalPengawas::whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))->get()
            : collect();

        $teacherIds = $allDutiesInPeriod->pluck('pengawas_1_id')
            ->merge($allDutiesInPeriod->pluck('pengawas_2_id'))
            ->filter()
            ->unique();

        $teachers = Teacher::with('user')->whereIn('id', $teacherIds)->get()->sortBy(fn ($t) => $t->user?->name);

        $filename = 'rekap_presensi_pengawas_'.($activePeriode ? str_replace(' ', '_', strtolower($activePeriode->nama_periode)) : 'ujian').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($teachers, $allDutiesInPeriod) {
            $handle = fopen('php://output', 'w');
            // Menambahkan UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['No', 'Nama Guru', 'NIP', 'Total Sesi', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Digantikan', 'Persentase Kehadiran (%)']);

            $no = 1;
            foreach ($teachers as $teacher) {
                $tDuties = $allDutiesInPeriod->filter(fn ($d) => (int) $d->pengawas_1_id === (int) $teacher->id || (int) $d->pengawas_2_id === (int) $teacher->id);

                $totalSesi = $tDuties->count();
                $hadir = 0;
                $izin = 0;
                $sakit = 0;
                $alpa = 0;
                $digantikan = 0;

                foreach ($tDuties as $duty) {
                    $status = ((int) $duty->pengawas_1_id === (int) $teacher->id) ? ($duty->status_p1 ?: $duty->status_kehadiran) : ($duty->status_p2 ?: $duty->status_kehadiran);
                    match ($status) {
                        'Hadir' => $hadir++,
                        'Izin' => $izin++,
                        'Sakit' => $sakit++,
                        'Alpa' => $alpa++,
                        'Digantikan' => $digantikan++,
                        default => null,
                    };
                }

                $persen = $totalSesi > 0 ? round(($hadir / $totalSesi) * 100, 1) : 0;

                fputcsv($handle, [
                    $no++,
                    $teacher->user?->name ?? '-',
                    $teacher->nip ? "'".$teacher->nip : '-',
                    $totalSesi,
                    $hadir,
                    $izin,
                    $sakit,
                    $alpa,
                    $digantikan,
                    $persen.'%',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Toggle izin pengisian presensi dan berita acara sebelum hari H ujian.
     */
    public function togglePengisianAwal(Request $request): RedirectResponse
    {
        $periodeId = $request->input('periode_id') ?: $request->query('periode_id');

        $periode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::latest()->first());

        if (! $periode) {
            return redirect()->route('ujian.pengawas.presensi.index')->with('error', 'Periode ujian tidak ditemukan.');
        }

        $periode->izinkan_pengisian_awal = ! $periode->izinkan_pengisian_awal;
        $periode->save();

        $statusText = $periode->izinkan_pengisian_awal ? 'diaktifkan (Pengawas sekarang dapat mengisi presensi & berita acara lebih awal)' : 'dinonaktifkan (Pengawas hanya dapat mengisi pada hari H ujian)';

        return redirect()->route('ujian.pengawas.presensi.index', ['periode_id' => $periode->id])
            ->with('success', "Mode pengisian sebelum hari H berhasil {$statusText}.");
    }
}
