<?php

namespace App\Services\Ujian;

use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;

class PengawasAutoPlotterService
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasMappingService $mappingService
    ) {}

    /**
     * Alokasi otomatis guru aktif sebagai pengawas ruang pada periode tertentu.
     * Menggunakan round-robin berputar bebas bentrok, wajib mematuhi pemetaan kelompok kelas
     * dan mengecualikan guru yang bertugas sebagai Panitia Ujian.
     */
    public function autoPlotSupervisors(UjianPeriode $periode, bool $twoSupervisorsPerRoom = false): int
    {
        // Ambil guru yang aktif dan BUKAN panitia ujian aktif pada periode ini
        $teachers = Teacher::with(['user', 'pengawasMappings'])
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->get()
            ->reject(fn ($t) => $t->isPanitiaUjian($periode->id))
            ->values();

        if ($teachers->isEmpty()) {
            return 0;
        }

        $jadwals = UjianJadwal::where('ujian_periode_id', $periode->id)
            ->orderBy('tanggal_ujian')
            ->orderBy('jam_mulai')
            ->get();

        $ruangs = UjianRuang::orderBy('kode_ruang')->get();
        if ($ruangs->isEmpty() || $jadwals->isEmpty()) {
            return 0;
        }

        $teacherCount = $teachers->count();
        $teacherIndex = 0;
        $totalPlotted = 0;

        foreach ($jadwals as $jadwal) {
            foreach ($ruangs as $ruang) {
                $assignment = UjianJadwalPengawas::firstOrNew([
                    'ujian_jadwal_id' => $jadwal->id,
                    'ujian_ruang_id' => $ruang->id,
                ]);

                // Cari pengawas 1 yang memenuhi syarat pemetaan kelas dan tidak bentrok
                $pengawas1Id = null;
                $attempts = 0;
                while ($attempts < $teacherCount) {
                    $candidate = $teachers[$teacherIndex % $teacherCount];
                    $teacherIndex++;
                    $attempts++;

                    // 1. Cek pemetaan kelas
                    $eligibility = $this->mappingService->isTeacherEligibleForJadwal($candidate->id, $jadwal);
                    if (! $eligibility['eligible']) {
                        continue;
                    }

                    // 2. Cek bentrok jam
                    $conflict = $this->conflictService->checkTeacherSupervisorConflict(
                        $candidate->id,
                        $jadwal->id,
                        $assignment->id
                    );

                    if (! $conflict) {
                        $pengawas1Id = $candidate->id;
                        break;
                    }
                }

                // Cari pengawas 2 jika mode 2 pengawas diaktifkan
                $pengawas2Id = null;
                if ($twoSupervisorsPerRoom) {
                    $attempts = 0;
                    while ($attempts < $teacherCount) {
                        $candidate = $teachers[$teacherIndex % $teacherCount];
                        $teacherIndex++;
                        $attempts++;

                        if ($candidate->id === $pengawas1Id) {
                            continue;
                        }

                        // 1. Cek pemetaan kelas
                        $eligibility = $this->mappingService->isTeacherEligibleForJadwal($candidate->id, $jadwal);
                        if (! $eligibility['eligible']) {
                            continue;
                        }

                        // 2. Cek bentrok jam
                        $conflict = $this->conflictService->checkTeacherSupervisorConflict(
                            $candidate->id,
                            $jadwal->id,
                            $assignment->id
                        );

                        if (! $conflict) {
                            $pengawas2Id = $candidate->id;
                            break;
                        }
                    }
                }

                $assignment->pengawas_1_id = $pengawas1Id;
                $assignment->pengawas_2_id = $pengawas2Id;
                $assignment->status_kehadiran = 'Terjadwal';
                $assignment->save();

                $totalPlotted++;
            }
        }

        return $totalPlotted;
    }
}
