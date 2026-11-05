<?php

namespace App\Services\Ujian;

use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;

class JadwalConflictService
{
    /**
     * Memeriksa apakah ada bentrok jadwal ujian untuk kelas/tingkat yang sama.
     */
    public function checkClassScheduleConflict(
        int $periodeId,
        ?int $schoolClassId,
        ?string $tingkat,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $ignoreJadwalId = null
    ): ?UjianJadwal {
        $startTime = strlen($jamMulai) === 5 ? $jamMulai.':00' : $jamMulai;
        $endTime = strlen($jamSelesai) === 5 ? $jamSelesai.':00' : $jamSelesai;

        $query = UjianJadwal::where('ujian_periode_id', $periodeId)
            ->whereDate('tanggal_ujian', $tanggal)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('jam_mulai', '<', $endTime)
                    ->where('jam_selesai', '>', $startTime);
            });

        if ($ignoreJadwalId) {
            $query->where('id', '!=', $ignoreJadwalId);
        }

        $levels = $tingkat ? array_filter(array_map('trim', explode(',', $tingkat))) : [];

        if ($schoolClassId && ! empty($levels)) {
            $query->where(function ($q) use ($schoolClassId, $levels) {
                $q->where('school_class_id', $schoolClassId);
                foreach ($levels as $lvl) {
                    $q->orWhere('tingkat', 'LIKE', "%{$lvl}%");
                }
            });
        } elseif ($schoolClassId) {
            $query->where('school_class_id', $schoolClassId);
        } elseif (! empty($levels)) {
            $query->where(function ($q) use ($levels) {
                foreach ($levels as $lvl) {
                    $q->orWhere('tingkat', 'LIKE', "%{$lvl}%");
                }
            });
        }

        return $query->first();
    }

    /**
     * Memeriksa apakah guru sudah ditugaskan mengawas pada jadwal/jam lain yang beririsan.
     */
    public function checkTeacherSupervisorConflict(
        int $teacherId,
        int $jadwalId,
        ?int $ignorePengawasId = null
    ): ?UjianJadwalPengawas {
        $targetJadwal = UjianJadwal::find($jadwalId);
        if (! $targetJadwal) {
            return null;
        }

        $query = UjianJadwalPengawas::whereHas('jadwal', function ($q) use ($targetJadwal) {
            $q->where('ujian_periode_id', $targetJadwal->ujian_periode_id)
                ->whereDate('tanggal_ujian', $targetJadwal->tanggal_ujian)
                ->where('jam_mulai', '<', $targetJadwal->jam_selesai)
                ->where('jam_selesai', '>', $targetJadwal->jam_mulai);
        })
            ->where(function ($q) use ($teacherId) {
                $q->where('pengawas_1_id', $teacherId)
                    ->orWhere('pengawas_2_id', $teacherId);
            });

        if ($ignorePengawasId) {
            $query->where('id', '!=', $ignorePengawasId);
        }

        return $query->first();
    }
}
