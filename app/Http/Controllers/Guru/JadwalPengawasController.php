<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Ujian\UjianBeritaAcara;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPengaturanTtd;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianPeserta;
use App\Models\Ujian\UjianRuang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalPengawasController extends Controller
{
    /**
     * Tampilkan jadwal pengawas ujian untuk guru yang sedang login.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $teacher = $user->teacher;

        // Fallback anggun jika akun user guru belum terhubung ke record data guru
        if (! $teacher) {
            return view('guru.pengawas.index', [
                'teacher' => null,
                'activePeriode' => null,
                'periodes' => collect(),
                'groupedDuties' => collect(),
                'duties' => collect(),
                'totalDuties' => 0,
                'todayDutiesCount' => 0,
                'totalMinutes' => 0,
                'completedDutiesCount' => 0,
                'orphanUser' => true,
            ]);
        }

        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::latest()->first());

        $periodes = UjianPeriode::orderBy('created_at', 'desc')->get();

        $duties = collect();
        if ($activePeriode) {
            $duties = UjianJadwalPengawas::with([
                'jadwal.subject',
                'jadwal.schoolClass',
                'ruang.schoolClass',
                'pengawas1.user',
                'pengawas2.user',
                'beritaAcara',
            ])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->where(fn ($q) => $q->where('pengawas_1_id', $teacher->id)->orWhere('pengawas_2_id', $teacher->id))
                ->join('ujian_jadwal', 'ujian_jadwal_pengawas.ujian_jadwal_id', '=', 'ujian_jadwal.id')
                ->orderBy('ujian_jadwal.tanggal_ujian', 'asc')
                ->orderBy('ujian_jadwal.jam_mulai', 'asc')
                ->select('ujian_jadwal_pengawas.*')
                ->get();
        }

        $today = now('Asia/Jakarta')->startOfDay();
        $nowTime = now('Asia/Jakarta')->format('H:i:s');

        // Normalisasi data pendukung & status real-time untuk setiap tugas
        $duties->each(function ($d) use ($teacher, $today, $nowTime) {
            $isP1 = (int) $d->pengawas_1_id === (int) $teacher->id;
            $partner = $isP1 ? $d->pengawas2 : $d->pengawas1;

            $d->is_pengawas_1 = $isP1;
            $d->partner = $partner;
            $d->partner_name = $partner?->user?->name;
            $d->partner_wa_url = $this->buildWhatsAppUrl($partner?->phone);

            // Perhitungan status sesi secara real-time
            $tgl = $d->jadwal?->tanggal_ujian;
            $dutyDate = $tgl ? $tgl->copy()->startOfDay() : null;
            $jamMulai = substr($d->jadwal?->jam_mulai ?? '00:00:00', 0, 8);
            $jamSelesai = substr($d->jadwal?->jam_selesai ?? '23:59:59', 0, 8);

            if (! $dutyDate || $dutyDate->lt($today)) {
                $d->status = 'selesai';
                $d->status_label = 'Selesai';
                $d->status_badge = 'bg-slate-200 text-slate-700 border-slate-400';
            } elseif ($dutyDate->gt($today)) {
                $d->status = 'akan_datang';
                $d->status_label = 'Akan Datang';
                $d->status_badge = 'bg-[#FFF3BF] text-amber-950 border-black';
            } else {
                // Hari ini: evaluasi jam mulai dan jam selesai
                if ($nowTime > $jamSelesai) {
                    $d->status = 'selesai';
                    $d->status_label = 'Selesai';
                    $d->status_badge = 'bg-slate-200 text-slate-700 border-slate-400';
                } elseif ($nowTime >= $jamMulai && $nowTime <= $jamSelesai) {
                    $d->status = 'sedang_berlangsung';
                    $d->status_label = 'Sedang Berlangsung';
                    $d->status_badge = 'bg-[#D3F9D8] text-emerald-950 border-black animate-pulse';
                } else {
                    $d->status = 'hari_ini';
                    $d->status_label = 'Hari Ini';
                    $d->status_badge = 'bg-[#5294FF] text-white border-black';
                }
            }
        });

        $groupedDuties = $duties->groupBy(function ($d) {
            return $d->jadwal?->tanggal_ujian?->format('Y-m-d') ?? 'tidak_terjadwal';
        });

        $totalDuties = $duties->count();
        $todayDutiesCount = $duties->filter(fn ($d) => $d->jadwal?->tanggal_ujian?->isToday())->count();
        $totalMinutes = $duties->sum(fn ($d) => (int) ($d->jadwal?->durasi_menit ?? 0));
        $completedDutiesCount = $duties->filter(fn ($d) => $d->status === 'selesai')->count();

        return view('guru.pengawas.index', compact(
            'teacher',
            'activePeriode',
            'periodes',
            'duties',
            'groupedDuties',
            'totalDuties',
            'todayDutiesCount',
            'totalMinutes',
            'completedDutiesCount',
        ))->with('orphanUser', false);
    }

    /**
     * Ambil daftar peserta siswa di ruangan ujian tertentu (AJAX/JSON).
     * Terlindungi dari celah IDOR: Guru wajib bertugas di ruangan tersebut.
     */
    public function pesertaRuang(UjianRuang $ruang, Request $request): JsonResponse
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            abort(403, 'Akses tidak diizinkan. Data guru tidak ditemukan.');
        }

        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::latest()->first());

        if (! $activePeriode) {
            abort(404, 'Periode ujian tidak ditemukan.');
        }

        // Proteksi Otorisasi IDOR: Pastikan guru login tercatat sebagai pengawas di ruangan ini
        $isAssigned = UjianJadwalPengawas::where('ujian_ruang_id', $ruang->id)
            ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
            ->where(fn ($q) => $q->where('pengawas_1_id', $teacher->id)->orWhere('pengawas_2_id', $teacher->id))
            ->exists();

        if (! $isAssigned) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk melihat daftar peserta di ruangan ini.');
        }

        $pesertas = UjianPeserta::with('schoolClass')
            ->where('ujian_periode_id', $activePeriode->id)
            ->where('ujian_ruang_id', $ruang->id)
            ->orderByRaw('CASE WHEN nomor_kursi IS NULL THEN 1 ELSE 0 END, nomor_kursi ASC, nama_peserta ASC')
            ->get();

        return response()->json([
            'ruang' => [
                'id' => $ruang->id,
                'nama_ruang' => $ruang->nama_ruang,
                'kode_ruang' => $ruang->kode_ruang,
                'kapasitas' => $ruang->kapasitas_maksimal,
            ],
            'total_peserta' => $pesertas->count(),
            'pesertas' => $pesertas->map(fn ($p) => [
                'id' => $p->id,
                'nomor_kursi' => $p->nomor_kursi,
                'nomor_peserta' => $p->nomor_peserta,
                'nama_peserta' => $p->nama_peserta,
                'nisn' => $p->nisn,
                'nama_kelas' => $p->nama_kelas ?: ($p->schoolClass?->name ?? '-'),
            ]),
        ]);
    }

    /**
     * Cetak Surat Tugas Resmi Mandiri untuk guru yang sedang login.
     * Menggunakan ulang view resmi: ujian.pengawas.print-jadwal-guru.
     */
    public function printSuratTugas(Request $request): View|RedirectResponse
    {
        $teacher = $request->user()->teacher;
        if (! $teacher) {
            abort(404, 'Data profil guru tidak ditemukan.');
        }

        $periodeId = $request->query('periode_id');
        $activePeriode = $periodeId
            ? UjianPeriode::find($periodeId)
            : (UjianPeriode::firstWhere('is_active', true) ?? UjianPeriode::latest()->first());

        $duties = collect();
        if ($activePeriode) {
            $duties = UjianJadwalPengawas::with(['jadwal.subject', 'ruang', 'pengawas1.user', 'pengawas2.user'])
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $activePeriode->id))
                ->where(fn ($q) => $q->where('pengawas_1_id', $teacher->id)->orWhere('pengawas_2_id', $teacher->id))
                ->join('ujian_jadwal', 'ujian_jadwal_pengawas.ujian_jadwal_id', '=', 'ujian_jadwal.id')
                ->orderBy('ujian_jadwal.tanggal_ujian', 'asc')
                ->orderBy('ujian_jadwal.jam_mulai', 'asc')
                ->select('ujian_jadwal_pengawas.*')
                ->get();
        }

        // Jika jadwal kosong, redirect kembali dengan notifikasi ramah
        if ($duties->isEmpty()) {
            return redirect()
                ->route('guru.pengawas.index', ['periode_id' => $activePeriode?->id])
                ->with('warning', 'Anda belum memiliki jadwal mengawas pada periode ini untuk dicetak.');
        }

        $teacherSchedules = [
            [
                'teacher' => $teacher,
                'duties' => $duties,
            ],
        ];

        $setting = AttendanceSetting::first();
        $ttdSetting = $activePeriode ? UjianPengaturanTtd::where('ujian_periode_id', $activePeriode->id)->first() : null;

        return view('ujian.pengawas.print-jadwal-guru', compact('teacherSchedules', 'activePeriode', 'setting', 'ttdSetting'));
    }

    /**
     * Tampilkan form pengisian Berita Acara Pelaksanaan Ujian Ruang.
     */
    public function beritaAcara(UjianJadwalPengawas $jadwalPengawas, Request $request): View
    {
        $teacher = $request->user()->teacher;
        if (! $teacher || ((int) $jadwalPengawas->pengawas_1_id !== (int) $teacher->id && (int) $jadwalPengawas->pengawas_2_id !== (int) $teacher->id)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk mengisi Berita Acara ruangan ini.');
        }

        $jadwalPengawas->load(['jadwal.subject', 'jadwal.schoolClass', 'jadwal.periode', 'ruang.schoolClass', 'pengawas1.user', 'pengawas2.user']);

        $beritaAcara = UjianBeritaAcara::where('ujian_jadwal_id', $jadwalPengawas->ujian_jadwal_id)
            ->where('ujian_ruang_id', $jadwalPengawas->ujian_ruang_id)
            ->first();

        // Ambil daftar peserta siswa di ruangan ini
        $pesertaQuery = UjianPeserta::with('schoolClass')
            ->where('ujian_periode_id', $jadwalPengawas->jadwal->ujian_periode_id)
            ->where('ujian_ruang_id', $jadwalPengawas->ujian_ruang_id);

        if ($jadwalPengawas->jadwal->school_class_id) {
            $pesertaQuery->where('school_class_id', $jadwalPengawas->jadwal->school_class_id);
        } elseif ($jadwalPengawas->jadwal->tingkat && $jadwalPengawas->jadwal->tingkat !== 'Semua Tingkat') {
            $tingkats = array_values(array_filter(array_map('trim', explode(',', $jadwalPengawas->jadwal->tingkat))));
            if (count($tingkats) < 6) {
                $pesertaQuery->where(function ($q) use ($tingkats) {
                    foreach ($tingkats as $t) {
                        $q->orWhere('nama_kelas', 'like', "Kelas {$t}%")
                            ->orWhereHas('schoolClass', fn ($sq) => $sq->where('name', 'like', "Kelas {$t}%"));
                    }
                });
            }
        }

        $pesertas = $pesertaQuery->orderByRaw('CASE WHEN nomor_kursi IS NULL THEN 1 ELSE 0 END, nomor_kursi ASC, nama_peserta ASC')->get();

        return view('guru.pengawas.berita-acara', compact('jadwalPengawas', 'beritaAcara', 'pesertas', 'teacher'));
    }

    /**
     * Simpan / update Laporan Berita Acara Pelaksanaan Ujian Ruang.
     */
    public function storeBeritaAcara(UjianJadwalPengawas $jadwalPengawas, Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        if (! $teacher || ((int) $jadwalPengawas->pengawas_1_id !== (int) $teacher->id && (int) $jadwalPengawas->pengawas_2_id !== (int) $teacher->id)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk mengirim Berita Acara ruangan ini.');
        }

        $jadwal = $jadwalPengawas->jadwal;
        $periode = $jadwal?->periode;
        $allowEarly = (bool) ($periode?->izinkan_pengisian_awal ?? false);

        if (! $allowEarly && $jadwal?->tanggal_ujian?->isFuture()) {
            return back()->with('error', 'Berita Acara hanya dapat diisi pada hari pelaksanaan ujian atau setelahnya.');
        }

        $beritaAcara = UjianBeritaAcara::where('ujian_jadwal_id', $jadwalPengawas->ujian_jadwal_id)
            ->where('ujian_ruang_id', $jadwalPengawas->ujian_ruang_id)
            ->first();

        // Kunci pasca-verifikasi: tolak edit jika sudah verified
        if ($beritaAcara && $beritaAcara->isVerified()) {
            abort(403, 'Laporan Berita Acara ini telah diverifikasi oleh Panitia dan terkunci.');
        }

        $validated = $request->validate([
            'naskah_soal_diterima' => 'required|integer|min:0',
            'naskah_soal_digunakan' => 'required|integer|min:0',
            'naskah_soal_sisa' => 'required|integer|min:0',
            'naskah_soal_rusak' => 'nullable|integer|min:0',
            'catatan_kejadian' => 'nullable|string|max:2000',
            'absen_siswa' => 'nullable|array',
            'absen_siswa.*.student_id' => 'required|integer',
            'absen_siswa.*.nomor_peserta' => 'nullable|string',
            'absen_siswa.*.nama_peserta' => 'required|string',
            'absen_siswa.*.nama_kelas' => 'nullable|string',
            'absen_siswa.*.alasan' => 'required|in:S,I,A',
            'absen_siswa.*.catatan' => 'nullable|string|max:255',
        ]);

        $diterima = (int) $validated['naskah_soal_diterima'];
        $digunakan = (int) $validated['naskah_soal_digunakan'];
        $sisa = (int) $validated['naskah_soal_sisa'];
        $rusak = (int) ($validated['naskah_soal_rusak'] ?? 0);

        if ($digunakan + $sisa + $rusak > $diterima) {
            return back()->withInput()->with('error', 'Jumlah naskah digunakan + sisa + rusak tidak boleh melebihi naskah yang diterima.');
        }

        // Hitung total peserta seharusnya langsung di server
        $pesertaQuery = UjianPeserta::where('ujian_periode_id', $jadwal->ujian_periode_id)
            ->where('ujian_ruang_id', $jadwalPengawas->ujian_ruang_id);

        if ($jadwal->school_class_id) {
            $pesertaQuery->where('school_class_id', $jadwal->school_class_id);
        } elseif ($jadwal->tingkat && $jadwal->tingkat !== 'Semua Tingkat') {
            $tingkats = array_values(array_filter(array_map('trim', explode(',', $jadwal->tingkat))));
            if (count($tingkats) < 6) {
                $pesertaQuery->where(function ($q) use ($tingkats) {
                    foreach ($tingkats as $t) {
                        $q->orWhere('nama_kelas', 'like', "Kelas {$t}%")
                            ->orWhereHas('schoolClass', fn ($sq) => $sq->where('name', 'like', "Kelas {$t}%"));
                    }
                });
            }
        }
        $pesertaSeharusnya = $pesertaQuery->count();

        // Hitung peserta tidak hadir dari daftar absen unik
        $daftarAbsen = array_values($validated['absen_siswa'] ?? []);
        $pesertaTidakHadir = count($daftarAbsen);
        $pesertaHadir = max(0, $pesertaSeharusnya - $pesertaTidakHadir);

        UjianBeritaAcara::updateOrCreate(
            [
                'ujian_jadwal_id' => $jadwalPengawas->ujian_jadwal_id,
                'ujian_ruang_id' => $jadwalPengawas->ujian_ruang_id,
            ],
            [
                'ujian_periode_id' => $jadwal->ujian_periode_id,
                'pengawas_pengisi_id' => $teacher->id,
                'peserta_seharusnya' => $pesertaSeharusnya,
                'peserta_hadir' => $pesertaHadir,
                'peserta_tidak_hadir' => $pesertaTidakHadir,
                'naskah_soal_diterima' => $diterima,
                'naskah_soal_digunakan' => $digunakan,
                'naskah_soal_sisa' => $sisa,
                'naskah_soal_rusak' => $rusak,
                'catatan_kejadian' => $validated['catatan_kejadian'] ?? null,
                'daftar_absen_siswa' => $daftarAbsen,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        // Otomatis tandai guru pengawas yang men-submit sebagai Hadir
        if ((int) $jadwalPengawas->pengawas_1_id === (int) $teacher->id) {
            $jadwalPengawas->status_p1 = 'Hadir';
            $jadwalPengawas->check_in_p1_at = $jadwalPengawas->check_in_p1_at ?: now();
        }
        if ((int) $jadwalPengawas->pengawas_2_id === (int) $teacher->id) {
            $jadwalPengawas->status_p2 = 'Hadir';
            $jadwalPengawas->check_in_p2_at = $jadwalPengawas->check_in_p2_at ?: now();
        }
        $jadwalPengawas->syncLegacyStatusKehadiran();
        $jadwalPengawas->save();

        return redirect()->route('guru.pengawas.index', ['periode_id' => $jadwal->ujian_periode_id])
            ->with('success', 'Laporan Berita Acara Ruangan berhasil disimpan dan dikirim ke Panitia Ujian.');
    }

    /**
     * Presensi Check-In mandiri pengawas ujian.
     */
    public function checkIn(UjianJadwalPengawas $jadwalPengawas, Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        if (! $teacher || ((int) $jadwalPengawas->pengawas_1_id !== (int) $teacher->id && (int) $jadwalPengawas->pengawas_2_id !== (int) $teacher->id)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk presensi pada jadwal ini.');
        }

        $jadwal = $jadwalPengawas->jadwal;
        $periode = $jadwal?->periode;
        $allowEarly = (bool) ($periode?->izinkan_pengisian_awal ?? false);

        if (! $allowEarly && $jadwal?->tanggal_ujian?->isFuture()) {
            return back()->with('error', 'Presensi kehadiran pengawas hanya dapat dilakukan pada hari pelaksanaan ujian.');
        }

        if ((int) $jadwalPengawas->pengawas_1_id === (int) $teacher->id) {
            $jadwalPengawas->status_p1 = 'Hadir';
            $jadwalPengawas->check_in_p1_at = now();
        }
        if ((int) $jadwalPengawas->pengawas_2_id === (int) $teacher->id) {
            $jadwalPengawas->status_p2 = 'Hadir';
            $jadwalPengawas->check_in_p2_at = now();
        }
        $jadwalPengawas->syncLegacyStatusKehadiran();
        $jadwalPengawas->save();

        return back()->with('success', 'Presensi kehadiran mengawas berhasil dicatat.');
    }

    /**
     * Normalisasi nomor telepon lokal menjadi format link WhatsApp internasional.
     */
    private function buildWhatsAppUrl(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $cleaned = preg_replace('/\D/', '', $phone);
        if (empty($cleaned)) {
            return null;
        }

        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62'.substr($cleaned, 1);
        }

        return 'https://wa.me/'.$cleaned;
    }
}
