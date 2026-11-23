<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
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
