<?php

namespace App\Services\Ujian;

use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;

class JadwalConflictService
{
    public function normalizeTime(string $time): string
    {
        $trimmed = trim($time);

        return strlen($trimmed) === 5 ? $trimmed.':00' : $trimmed;
    }

    public function isTimeOverlapping(string $startA, string $endA, string $startB, string $endB): bool
    {
        return $this->normalizeTime($startA) < $this->normalizeTime($endB)
            && $this->normalizeTime($endA) > $this->normalizeTime($startB);
    }

    /**
     * Memeriksa bentrok jadwal ujian untuk kelas/tingkat yang sama secara eksak.
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
        $startTime = $this->normalizeTime($jamMulai);
        $endTime = $this->normalizeTime($jamSelesai);

        $candidates = UjianJadwal::with('schoolClass')
            ->where('ujian_periode_id', $periodeId)
            ->whereDate('tanggal_ujian', $tanggal)
            ->where('jam_mulai', '<', $endTime)
            ->where('jam_selesai', '>', $startTime)
            ->when($ignoreJadwalId, fn ($q) => $q->where('id', '!=', $ignoreJadwalId))
            ->get();

        $probe = new UjianJadwal([
            'school_class_id' => $schoolClassId,
            'tingkat' => $tingkat,
        ]);
        $targetLevels = $probe->getTargetLevels();

        foreach ($candidates as $existing) {
            if ($schoolClassId && $existing->school_class_id && (int) $schoolClassId === (int) $existing->school_class_id) {
                return $existing;
            }

            if (! empty(array_intersect($targetLevels, $existing->getTargetLevels()))) {
                return $existing;
            }
        }

        return null;
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

        $startTime = $this->normalizeTime($targetJadwal->jam_mulai);
        $endTime = $this->normalizeTime($targetJadwal->jam_selesai);

        return UjianJadwalPengawas::with(['jadwal', 'ruang'])
            ->whereHas('jadwal', function ($q) use ($targetJadwal, $startTime, $endTime) {
                $q->where('ujian_periode_id', $targetJadwal->ujian_periode_id)
                    ->whereDate('tanggal_ujian', $targetJadwal->tanggal_ujian)
                    ->where('jam_mulai', '<', $endTime)
                    ->where('jam_selesai', '>', $startTime);
            })
            ->where(function ($q) use ($teacherId) {
                $q->where('pengawas_1_id', $teacherId)
                    ->orWhere('pengawas_2_id', $teacherId);
            })
            ->when($ignorePengawasId, fn ($q) => $q->where('id', '!=', $ignorePengawasId))
            ->first();
    }

    /**
     * Memeriksa apakah ruang sudah digunakan oleh jadwal lain pada tanggal dan jam yang beririsan.
     */
    public function checkRoomScheduleConflict(
        int $ruangId,
        int $jadwalId,
        ?int $ignorePengawasId = null
    ): ?UjianJadwalPengawas {
        $targetJadwal = UjianJadwal::find($jadwalId);
        if (! $targetJadwal) {
            return null;
        }

        $startTime = $this->normalizeTime($targetJadwal->jam_mulai);
        $endTime = $this->normalizeTime($targetJadwal->jam_selesai);

        return UjianJadwalPengawas::with(['jadwal.subject', 'ruang'])
            ->where('ujian_ruang_id', $ruangId)
            ->whereHas('jadwal', function ($q) use ($targetJadwal, $startTime, $endTime) {
                $q->where('ujian_periode_id', $targetJadwal->ujian_periode_id)
                    ->whereDate('tanggal_ujian', $targetJadwal->tanggal_ujian)
                    ->where('jam_mulai', '<', $endTime)
                    ->where('jam_selesai', '>', $startTime);
            })
            ->when($ignorePengawasId, fn ($q) => $q->where('id', '!=', $ignorePengawasId))
            ->first();
    }
}
