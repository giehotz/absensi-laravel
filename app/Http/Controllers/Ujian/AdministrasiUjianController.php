<?php

namespace App\Http\Controllers\Ujian;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdministrasiUjianController extends Controller
{
    /**
     * Hub Navigasi Cetak Berkas Administrasi Ruang Ujian.
     */
    public function index(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jadwals = collect();
        $ruangs = collect();
        $gurus = collect();

        if ($activePeriode) {
            $jadwals = UjianJadwal::with(['subject', 'schoolClass'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->get();

            $ruangs = UjianRuang::orderBy('nama_ruang')->get();

            $gurus = Teacher::with('user')
                ->whereHas('user', fn ($q) => $q->where('is_active', true))
                ->get()
                ->sortBy(fn ($t) => $t->user?->name);
        }

        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        return view('ujian.administrasi.index', compact('periodes', 'activePeriode', 'jadwals', 'ruangs', 'gurus', 'ttdSetting'));
    }

    /**
     * Cetak Daftar Hadir Peserta Ujian per Ruang & Mapel.
     */
    public function printDaftarHadirPeserta(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $isBlangko = $request->boolean('blangko');
        $jadwalId = $request->query('jadwal_id');
        $ruangId = $request->query('ruang_id');

        $jadwal = $jadwalId ? UjianJadwal::with(['subject', 'schoolClass'])->find($jadwalId) : null;
        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        // Siapkan kumpulan bundle halaman cetak per ruang
        $sheets = collect();

        if ($activePeriode) {
            $targetRuangs = collect();

            if ($ruangId && $ruangId !== 'all') {
                $targetRuangs = UjianRuang::where('id', $ruangId)->get();
            } else {
                $targetRuangs = UjianRuang::orderBy('nama_ruang')->get();
            }

            foreach ($targetRuangs as $r) {
                $pesertaQuery = UjianPeserta::with('schoolClass')
                    ->where('ujian_periode_id', $activePeriode->id)
                    ->where('ujian_ruang_id', $r->id)
                    ->orderBy('nomor_kursi')
                    ->orderBy('nomor_peserta');

                // Jika terikat jadwal dan jadwal memiliki target tingkat/kelas, sesuaikan
                if ($jadwal && ! $isBlangko) {
                    if ($jadwal->school_class_id) {
                        $pesertaQuery->where('school_class_id', $jadwal->school_class_id);
                    } elseif ($jadwal->tingkat) {
                        $tingkats = array_map('trim', explode(',', $jadwal->tingkat));
                        $pesertaQuery->whereHas('schoolClass', function ($q) use ($tingkats) {
                            $q->whereIn('level', $tingkats);
                        });
                    }
                }

                $pesertas = $pesertaQuery->get();

                // Dapatkan pengawas jika ada jadwal
                $pengawas = null;
                if ($jadwal) {
                    $pengawas = UjianJadwalPengawas::with(['pengawas1.user', 'pengawas2.user'])
                        ->where('ujian_jadwal_id', $jadwal->id)
                        ->where('ujian_ruang_id', $r->id)
                        ->first();
                }

                if ($pesertas->isNotEmpty() || $isBlangko || ($ruangId && $ruangId !== 'all')) {
                    $sheets->push([
                        'ruang' => $r,
                        'jadwal' => $jadwal,
                        'pesertas' => $pesertas,
                        'pengawas' => $pengawas,
                    ]);
                }
            }
        }

        return view('ujian.administrasi.daftar-hadir-peserta', compact(
            'activePeriode',
            'jadwal',
            'sheets',
            'isBlangko',
            'setting',
            'ttdSetting'
        ));
    }

    /**
     * Cetak Berita Acara Pelaksanaan (BAP) Ujian.
     */
    public function printBeritaAcara(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $isBlangko = $request->boolean('blangko');
        $jadwalId = $request->query('jadwal_id');
        $ruangId = $request->query('ruang_id');

        $jadwal = $jadwalId ? UjianJadwal::with(['subject', 'schoolClass'])->find($jadwalId) : null;
        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        $sheets = collect();

        if ($activePeriode) {
            $targetRuangs = collect();
            if ($ruangId && $ruangId !== 'all') {
                $targetRuangs = UjianRuang::where('id', $ruangId)->get();
            } else {
                $targetRuangs = UjianRuang::orderBy('nama_ruang')->get();
            }

            foreach ($targetRuangs as $r) {
                $pesertaCount = UjianPeserta::where('ujian_periode_id', $activePeriode->id)
                    ->where('ujian_ruang_id', $r->id)
                    ->count();

                $pengawas = null;
                if ($jadwal) {
                    $pengawas = UjianJadwalPengawas::with(['pengawas1.user', 'pengawas2.user'])
                        ->where('ujian_jadwal_id', $jadwal->id)
                        ->where('ujian_ruang_id', $r->id)
                        ->first();
                }

                $sheets->push([
                    'ruang' => $r,
                    'jadwal' => $jadwal,
                    'peserta_count' => $pesertaCount,
                    'pengawas' => $pengawas,
                ]);
            }
        }

        return view('ujian.administrasi.berita-acara', compact(
            'activePeriode',
            'jadwal',
            'sheets',
            'isBlangko',
            'setting',
            'ttdSetting'
        ));
    }

    /**
     * Cetak Cover / Label Amplop Naskah Soal & LJK.
     */
    public function printLabelAmplop(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $jadwalId = $request->query('jadwal_id');
        $ruangId = $request->query('ruang_id');

        $jadwal = $jadwalId ? UjianJadwal::with(['subject', 'schoolClass'])->find($jadwalId) : null;
        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        $sheets = collect();
        if ($activePeriode) {
            $targetRuangs = collect();
            if ($ruangId && $ruangId !== 'all') {
                $targetRuangs = UjianRuang::where('id', $ruangId)->get();
            } else {
                $targetRuangs = UjianRuang::orderBy('nama_ruang')->get();
            }

            foreach ($targetRuangs as $r) {
                $pesertaCount = UjianPeserta::where('ujian_periode_id', $activePeriode->id)
                    ->where('ujian_ruang_id', $r->id)
                    ->count();

                $pengawas = null;
                if ($jadwal) {
                    $pengawas = UjianJadwalPengawas::with(['pengawas1.user', 'pengawas2.user'])
                        ->where('ujian_jadwal_id', $jadwal->id)
                        ->where('ujian_ruang_id', $r->id)
                        ->first();
                }

                $sheets->push([
                    'ruang' => $r,
                    'jadwal' => $jadwal,
                    'peserta_count' => $pesertaCount,
                    'pengawas' => $pengawas,
                ]);
            }
        }

        return view('ujian.administrasi.label-amplop', compact(
            'activePeriode',
            'jadwal',
            'sheets',
            'setting',
            'ttdSetting'
        ));
    }

    /**
     * Cetak Lembar Tata Tertib Peserta & Pengawas Ujian.
     */
    public function printTataTertib(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        return view('ujian.administrasi.tata-tertib', compact('activePeriode', 'setting', 'ttdSetting'));
    }

    /**
     * Cetak Surat Keputusan (SK) Panitia Ujian Resmi.
     */
    public function printSkPanitia(Request $request): View
    {
        $periodes = UjianPeriode::orderByDesc('id')->get();
        $selectedPeriodeId = $request->query('periode_id', $periodes->firstWhere('is_active', true)?->id ?? $periodes->first()?->id);
        $activePeriode = $periodes->firstWhere('id', $selectedPeriodeId);

        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        $panitias = collect();
        if ($activePeriode) {
            $panitias = UjianPanitia::with(['teacher.user'])
                ->where('ujian_periode_id', $activePeriode->id)
                ->where('is_active', true)
                ->get();
        }

        return view('ujian.administrasi.sk-panitia', compact('activePeriode', 'setting', 'ttdSetting', 'panitias'));
    }

    /**
     * Simpan pembaruan konten WYSIWYG Tata Tertib Peserta & Pengawas.
     */
    public function updateTataTertib(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'periode_id' => ['required', 'exists:ujian_periode,id'],
            'konten_tata_tertib' => ['nullable', 'string'],
        ]);

        $ttdSetting = UjianPengaturanTtd::firstOrCreate(
            ['ujian_periode_id' => $validated['periode_id']]
        );

        $ttdSetting->update([
            'konten_tata_tertib' => $validated['konten_tata_tertib'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Isi Tata Tertib Peserta & Pengawas berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Isi Tata Tertib Peserta & Pengawas berhasil diperbarui.');
    }

    /**
     * Reset konten Tata Tertib ke standar bawaan.
     */
    public function resetTataTertib(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'periode_id' => ['required', 'exists:ujian_periode,id'],
        ]);

        $ttdSetting = UjianPengaturanTtd::where('ujian_periode_id', $validated['periode_id'])->first();
        if ($ttdSetting) {
            $ttdSetting->update(['konten_tata_tertib' => null]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Isi Tata Tertib berhasil dikembalikan ke standar bawaan.',
            ]);
        }

        return back()->with('success', 'Isi Tata Tertib berhasil dikembalikan ke standar bawaan.');
    }

    /**
     * Simpan pembaruan konten WYSIWYG SK Panitia Ujian.
     */
    public function updateSkPanitia(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'periode_id' => ['required', 'exists:ujian_periode,id'],
            'konten_sk_panitia' => ['nullable', 'string'],
        ]);

        $ttdSetting = UjianPengaturanTtd::firstOrCreate(
            ['ujian_periode_id' => $validated['periode_id']]
        );

        $ttdSetting->update([
            'konten_sk_panitia' => $validated['konten_sk_panitia'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Isi Surat Keputusan (SK) Panitia Ujian berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Isi Surat Keputusan (SK) Panitia Ujian berhasil diperbarui.');
    }

    /**
     * Reset konten SK Panitia ke standar bawaan.
     */
    public function resetSkPanitia(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'periode_id' => ['required', 'exists:ujian_periode,id'],
        ]);

        $ttdSetting = UjianPengaturanTtd::where('ujian_periode_id', $validated['periode_id'])->first();
        if ($ttdSetting) {
            $ttdSetting->update(['konten_sk_panitia' => null]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Isi SK Panitia berhasil dikembalikan ke standar bawaan.',
            ]);
        }

        return back()->with('success', 'Isi SK Panitia berhasil dikembalikan ke standar bawaan.');
    }
}
