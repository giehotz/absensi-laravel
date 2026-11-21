<?php

namespace App\Services\Ujian;

use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengawasMapping;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use Illuminate\Support\Facades\DB;

class PengawasAutoPlotterService
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasMappingService $mappingService
    ) {}

    /**
     * Alokasi otomatis pengawas anti-bentrok dengan pemerataan beban mengawas.
     * - Hanya mem-plot ruang yang tingkat kelasnya cocok dengan jadwal (mis. Kelas 1-2 vs 3-6).
     * - Menjamin 0 bentrok guru dan 0 bentrok ruang pada tanggal & jam yang beririsan.
     * - Meratakan jumlah tugas per guru dan meminimalkan tugas ganda di hari/ruang yang sama.
     */
    public function autoPlotSupervisors(
        UjianPeriode $periode,
        bool $twoSupervisorsPerRoom = false,
        bool $resetExisting = true,
        ?array $targetLevels = null
    ): int {
        return DB::transaction(function () use ($periode, $twoSupervisorsPerRoom, $resetExisting, $targetLevels) {
            // 1. Ambil SEMUA panitia aktif periode ini (seluruh panitia dikecualikan dari auto-plotter)
            $excludedPanitiaIds = UjianPanitia::where('ujian_periode_id', $periode->id)
                ->where('is_active', true)
                ->pluck('teacher_id')
                ->all();

            $mappings = UjianPengawasMapping::where('ujian_periode_id', $periode->id)
                ->get()
                ->keyBy('teacher_id');

            $hasAnyMapping = $mappings->isNotEmpty();

            // 2. Ambil guru aktif yang bukan panitia (jika pemetaan ada, wajib memenuhi pemetaan)
            $teachers = Teacher::with('user')
                ->whereHas('user', fn ($q) => $q->where('is_active', true))
                ->whereNotIn('id', $excludedPanitiaIds)
                ->get()
                ->filter(function ($t) use ($mappings, $hasAnyMapping) {
                    if (! $hasAnyMapping) {
                        return true;
                    }

                    return $mappings->has($t->id) && ! empty($mappings[$t->id]->assigned_levels);
                })
                ->values();

            $allJadwals = UjianJadwal::with('schoolClass')
                ->where('ujian_periode_id', $periode->id)
                ->orderBy('tanggal_ujian')
                ->orderBy('jam_mulai')
                ->orderBy('id')
                ->get();

            $jadwals = $allJadwals;
            if (! empty($targetLevels)) {
                $targetLevelsStr = array_map('strval', $targetLevels);
                $jadwals = $allJadwals->filter(function ($j) use ($targetLevelsStr) {
                    return ! empty(array_intersect($j->getTargetLevels(), $targetLevelsStr));
                })->values();
            }

            $ruangs = UjianRuang::with('schoolClass')
                ->orderBy('kode_ruang')
                ->get();

            if ($teachers->isEmpty() || $jadwals->isEmpty() || $ruangs->isEmpty()) {
                return 0;
            }

            if ($resetExisting) {
                // HANYA hapus penugasan pada jadwal kelompok tingkat sasaran!
                UjianJadwalPengawas::whereIn('ujian_jadwal_id', $jadwals->pluck('id'))->delete();
            }

            // 3. Siapkan tracker in-memory (menghindari N+1 query)
            $teacherLevels = [];
            $teacherWorkload = [];
            $teacherDailyCount = [];
            $teacherRoomCount = [];
            $teacherBusySlots = []; // [teacherId => [['date', 'start', 'end'], ...]]
            $roomBusySlots = [];    // [ruangId => [['date', 'start', 'end', 'jadwal_id'], ...]]

            foreach ($teachers as $t) {
                $rawLevels = $mappings->has($t->id) ? $mappings[$t->id]->assigned_levels : ['1', '2', '3', '4', '5', '6'];
                $levels = array_values(array_unique(array_map('strval', $rawLevels)));
                $teacherLevels[$t->id] = $levels;
                $teacherWorkload[$t->id] = 0;
                $teacherDailyCount[$t->id] = [];
                $teacherRoomCount[$t->id] = [];
                $teacherBusySlots[$t->id] = [];
            }

            // Selalu muat penugasan yang masih ada di luar kumpulan jadwal sasaran
            // (misal: guru multi-tugas sudah di-plot di kelompok 1-2, slot sibuknya dihormati saat mem-plot kelompok 3-6)
            $outsideAssignments = UjianJadwalPengawas::with('jadwal')
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $periode->id)->whereNotIn('id', $jadwals->pluck('id')))
                ->get();

            foreach ($outsideAssignments as $asg) {
                if (! $asg->jadwal) {
                    continue;
                }
                $date = $asg->jadwal->tanggal_ujian->format('Y-m-d');
                $start = $this->conflictService->normalizeTime($asg->jadwal->jam_mulai);
                $end = $this->conflictService->normalizeTime($asg->jadwal->jam_selesai);

                $roomBusySlots[$asg->ujian_ruang_id][] = [
                    'date' => $date, 'start' => $start, 'end' => $end, 'jadwal_id' => $asg->ujian_jadwal_id,
                ];

                foreach ([$asg->pengawas_1_id, $asg->pengawas_2_id] as $tid) {
                    if ($tid && isset($teacherWorkload[$tid])) {
                        $this->registerTeacherDuty(
                            $tid, $asg->ujian_ruang_id, $date, $start, $end,
                            $teacherWorkload, $teacherDailyCount, $teacherRoomCount, $teacherBusySlots
                        );
                    }
                }
            }

            // Jika tidak reset, muat juga penugasan yang sudah ada di dalam kelompok sasaran
            if (! $resetExisting) {
                $existingAssignments = UjianJadwalPengawas::with('jadwal')
                    ->whereIn('ujian_jadwal_id', $jadwals->pluck('id'))
                    ->get();

                foreach ($existingAssignments as $asg) {
                    if (! $asg->jadwal) {
                        continue;
                    }
                    $date = $asg->jadwal->tanggal_ujian->format('Y-m-d');
                    $start = $this->conflictService->normalizeTime($asg->jadwal->jam_mulai);
                    $end = $this->conflictService->normalizeTime($asg->jadwal->jam_selesai);

                    $roomBusySlots[$asg->ujian_ruang_id][] = [
                        'date' => $date, 'start' => $start, 'end' => $end, 'jadwal_id' => $asg->ujian_jadwal_id,
                    ];

                    foreach ([$asg->pengawas_1_id, $asg->pengawas_2_id] as $tid) {
                        if ($tid && isset($teacherWorkload[$tid])) {
                            $this->registerTeacherDuty(
                                $tid, $asg->ujian_ruang_id, $date, $start, $end,
                                $teacherWorkload, $teacherDailyCount, $teacherRoomCount, $teacherBusySlots
                            );
                        }
                    }
                }
            }

            // 4. Plot jadwal per sesi & ruang yang cocok tingkat kelasnya
            $totalPlotted = 0;

            foreach ($jadwals as $jadwal) {
                $date = $jadwal->tanggal_ujian->format('Y-m-d');
                $start = $this->conflictService->normalizeTime($jadwal->jam_mulai);
                $end = $this->conflictService->normalizeTime($jadwal->jam_selesai);
                $jadwalLevels = $jadwal->getTargetLevels();

                // FILTER KRUSIAL: hanya ruang yang cocok dengan tingkat/kelas jadwal ini
                $matchedRuangs = $ruangs->filter(fn (UjianRuang $r) => $r->matchesJadwal($jadwal));

                foreach ($matchedRuangs as $ruang) {
                    // Skip jika ruang sudah dipakai jadwal lain pada jam beririsan
                    if ($this->isRoomBusy($roomBusySlots[$ruang->id] ?? [], $date, $start, $end, $jadwal->id)) {
                        continue;
                    }

                    $assignment = UjianJadwalPengawas::firstOrNew([
                        'ujian_jadwal_id' => $jadwal->id,
                        'ujian_ruang_id' => $ruang->id,
                    ]);

                    // Jika mode non-reset dan sudah terisi lengkap, lewati
                    if (! $resetExisting && $assignment->exists && $assignment->pengawas_1_id && (! $twoSupervisorsPerRoom || $assignment->pengawas_2_id)) {
                        continue;
                    }

                    $requiredLevel = $ruang->getLevel();

                    // Pilih Pengawas 1
                    $pengawas1Id = $assignment->pengawas_1_id;
                    if (! $pengawas1Id) {
                        $pengawas1Id = $this->pickBestTeacher(
                            $teachers->pluck('id')->all(),
                            $teacherLevels,
                            $requiredLevel,
                            $jadwalLevels,
                            $date,
                            $start,
                            $end,
                            $ruang->id,
                            null,
                            $teacherWorkload,
                            $teacherDailyCount,
                            $teacherRoomCount,
                            $teacherBusySlots
                        );

                        if ($pengawas1Id) {
                            $this->registerTeacherDuty(
                                $pengawas1Id, $ruang->id, $date, $start, $end,
                                $teacherWorkload, $teacherDailyCount, $teacherRoomCount, $teacherBusySlots
                            );
                        }
                    }

                    // Pilih Pengawas 2 (opsional)
                    $pengawas2Id = $twoSupervisorsPerRoom ? $assignment->pengawas_2_id : null;
                    if ($twoSupervisorsPerRoom && ! $pengawas2Id) {
                        $pengawas2Id = $this->pickBestTeacher(
                            $teachers->pluck('id')->all(),
                            $teacherLevels,
                            $requiredLevel,
                            $jadwalLevels,
                            $date,
                            $start,
                            $end,
                            $ruang->id,
                            $pengawas1Id,
                            $teacherWorkload,
                            $teacherDailyCount,
                            $teacherRoomCount,
                            $teacherBusySlots
                        );

                        if ($pengawas2Id) {
                            $this->registerTeacherDuty(
                                $pengawas2Id, $ruang->id, $date, $start, $end,
                                $teacherWorkload, $teacherDailyCount, $teacherRoomCount, $teacherBusySlots
                            );
                        }
                    }

                    $roomBusySlots[$ruang->id][] = [
                        'date' => $date, 'start' => $start, 'end' => $end, 'jadwal_id' => $jadwal->id,
                    ];

                    $assignment->pengawas_1_id = $pengawas1Id;
                    $assignment->pengawas_2_id = $pengawas2Id;
                    $assignment->status_kehadiran = $assignment->status_kehadiran ?: 'Terjadwal';
                    $assignment->save();

                    $totalPlotted++;
                }
            }

            return $totalPlotted;
        });
    }

    private function pickBestTeacher(
        array $teacherIds,
        array $teacherLevels,
        ?string $ruangLevel,
        array $jadwalLevels,
        string $date,
        string $start,
        string $end,
        int $ruangId,
        ?int $excludeTeacherId,
        array $teacherWorkload,
        array $teacherDailyCount,
        array $teacherRoomCount,
        array $teacherBusySlots
    ): ?int {
        $candidates = [];

        foreach ($teacherIds as $tid) {
            if ($excludeTeacherId !== null && $tid === $excludeTeacherId) {
                continue;
            }

            // 1. Cek kecocokan pemetaan tingkat kelas (utamakan tingkat ruang jika ada)
            $allowed = $teacherLevels[$tid] ?? [];
            $levelMatched = $ruangLevel !== null
                ? in_array($ruangLevel, $allowed, true)
                : ! empty(array_intersect($jadwalLevels, $allowed));

            if (! $levelMatched) {
                continue;
            }

            // 2. Hard constraint: tidak boleh bentrok di tanggal & jam yang beririsan
            if ($this->isTeacherBusy($teacherBusySlots[$tid] ?? [], $date, $start, $end)) {
                continue;
            }

            // 3. Soft constraints (urut dari prioritas tertinggi ke terendah)
            $candidates[] = [
                'id' => $tid,
                'workload' => $teacherWorkload[$tid] ?? 0,
                'daily' => $teacherDailyCount[$tid][$date] ?? 0,
                'room_repeat' => $teacherRoomCount[$tid][$ruangId] ?? 0,
                'flexibility' => count($allowed),
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function (array $a, array $b) {
            return [$a['workload'], $a['daily'], $a['room_repeat'], $a['flexibility'], $a['id']]
                <=> [$b['workload'], $b['daily'], $b['room_repeat'], $b['flexibility'], $b['id']];
        });

        return $candidates[0]['id'];
    }

    private function isTeacherBusy(array $slots, string $date, string $start, string $end): bool
    {
        foreach ($slots as $slot) {
            if ($slot['date'] === $date && $this->conflictService->isTimeOverlapping($start, $end, $slot['start'], $slot['end'])) {
                return true;
            }
        }

        return false;
    }

    private function isRoomBusy(array $slots, string $date, string $start, string $end, int $currentJadwalId): bool
    {
        foreach ($slots as $slot) {
            if ($slot['jadwal_id'] !== $currentJadwalId
                && $slot['date'] === $date
                && $this->conflictService->isTimeOverlapping($start, $end, $slot['start'], $slot['end'])) {
                return true;
            }
        }

        return false;
    }

    private function registerTeacherDuty(
        int $teacherId,
        int $ruangId,
        string $date,
        string $start,
        string $end,
        array &$teacherWorkload,
        array &$teacherDailyCount,
        array &$teacherRoomCount,
        array &$teacherBusySlots
    ): void {
        $teacherWorkload[$teacherId] = ($teacherWorkload[$teacherId] ?? 0) + 1;
        $teacherDailyCount[$teacherId][$date] = ($teacherDailyCount[$teacherId][$date] ?? 0) + 1;
        $teacherRoomCount[$teacherId][$ruangId] = ($teacherRoomCount[$teacherId][$ruangId] ?? 0) + 1;
        $teacherBusySlots[$teacherId][] = ['date' => $date, 'start' => $start, 'end' => $end];
    }
}
