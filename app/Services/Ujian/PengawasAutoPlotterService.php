<?php

namespace App\Services\Ujian;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJadwalPengawas;
use App\Models\Ujian\UjianPanitia;
use App\Models\Ujian\UjianPengawasMapping;
use App\Models\Ujian\UjianPeriode;
use App\Models\Ujian\UjianRuang;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PengawasAutoPlotterService
{
    public function __construct(
        protected JadwalConflictService $conflictService,
        protected PengawasMappingService $mappingService,
        protected PengawasTeachingResolverService $teachingResolver
    ) {}

    /**
     * Alokasi otomatis pengawas anti-bentrok (2-Pass Allocation):
     * - Pass 1: Memprioritaskan guru pengampu mapel pada kelas yang diajar (jika diaktifkan).
     * - Pass 2: Mengisi slot sisa menggunakan pemerataan beban dan rotasi.
     */
    public function autoPlotSupervisors(
        UjianPeriode $periode,
        bool $twoSupervisorsPerRoom = false,
        bool $resetExisting = true,
        ?array $targetLevels = null,
        bool $prioritizeSubjectTeachers = true,
        bool $preserveLocked = true
    ): int {
        return DB::transaction(function () use (
            $periode,
            $twoSupervisorsPerRoom,
            $resetExisting,
            $targetLevels,
            $prioritizeSubjectTeachers,
            $preserveLocked
        ) {
            $excludedPanitiaIds = UjianPanitia::where('ujian_periode_id', $periode->id)
                ->where('is_active', true)
                ->pluck('teacher_id')
                ->all();

            $mappings = UjianPengawasMapping::where('ujian_periode_id', $periode->id)
                ->get()
                ->keyBy('teacher_id');

            $hasAnyMapping = $mappings->isNotEmpty();

            $teachers = Teacher::with('user')
                ->whereHas('user', fn ($q) => $q->where('is_active', true))
                ->whereNotIn('id', $excludedPanitiaIds)
                ->get()
                ->filter(function (Teacher $t) use ($mappings, $hasAnyMapping) {
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
                $jadwals = $allJadwals->filter(function (UjianJadwal $j) use ($targetLevelsStr) {
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
                UjianJadwalPengawas::whereIn('ujian_jadwal_id', $jadwals->pluck('id'))->delete();
            }

            $teacherLevels = [];
            $teacherWorkload = [];
            $teacherDailyCount = [];
            $teacherRoomCount = [];
            $teacherBusySlots = [];
            $roomBusySlots = [];
            $eligibleTeacherLookup = [];

            foreach ($teachers as $t) {
                $rawLevels = $mappings->has($t->id) ? $mappings[$t->id]->assigned_levels : ['1', '2', '3', '4', '5', '6'];
                $teacherLevels[$t->id] = array_values(array_unique(array_map('strval', $rawLevels)));
                $teacherWorkload[$t->id] = 0;
                $teacherDailyCount[$t->id] = [];
                $teacherRoomCount[$t->id] = [];
                $teacherBusySlots[$t->id] = [];
                $eligibleTeacherLookup[$t->id] = true;
            }

            // Muat penugasan di luar jadwal target agar guru multi-kelompok tidak bentrok
            $outsideAssignments = UjianJadwalPengawas::with('jadwal')
                ->whereHas('jadwal', fn ($q) => $q->where('ujian_periode_id', $periode->id)->whereNotIn('id', $jadwals->pluck('id')))
                ->get();

            $this->registerExistingAssignments(
                $outsideAssignments,
                $roomBusySlots,
                $teacherWorkload,
                $teacherDailyCount,
                $teacherRoomCount,
                $teacherBusySlots
            );

            if (! $resetExisting) {
                $existingAssignments = UjianJadwalPengawas::with('jadwal')
                    ->whereIn('ujian_jadwal_id', $jadwals->pluck('id'))
                    ->get();

                $this->registerExistingAssignments(
                    $existingAssignments,
                    $roomBusySlots,
                    $teacherWorkload,
                    $teacherDailyCount,
                    $teacherRoomCount,
                    $teacherBusySlots
                );
            }

            $teachingMap = $prioritizeSubjectTeachers
                ? $this->teachingResolver->buildTeachingMap($periode->academic_year_id)
                : [];
            $homeroomMap = SchoolClass::pluck('homeroom_teacher_id', 'id')->all();
            $subjectsByNormalizedName = Subject::all()->mapWithKeys(
                fn (Subject $s) => [$this->teachingResolver->normalizeSubjectName($s->name) => (int) $s->id]
            );

            // Susun slot target yang perlu dialokasikan
            $pendingSlots = [];

            foreach ($jadwals as $jadwal) {
                $date = $jadwal->tanggal_ujian->format('Y-m-d');
                $start = $this->conflictService->normalizeTime($jadwal->jam_mulai);
                $end = $this->conflictService->normalizeTime($jadwal->jam_selesai);
                $jadwalLevels = $jadwal->getTargetLevels();
                $subjectId = $this->teachingResolver->resolveSubjectId($jadwal, $subjectsByNormalizedName);

                $matchedRuangs = $ruangs->filter(fn (UjianRuang $r) => $r->matchesJadwal($jadwal));

                foreach ($matchedRuangs as $ruang) {
                    if ($this->isRoomBusy($roomBusySlots[$ruang->id] ?? [], $date, $start, $end, $jadwal->id)) {
                        continue;
                    }

                    $assignment = UjianJadwalPengawas::firstOrNew([
                        'ujian_jadwal_id' => $jadwal->id,
                        'ujian_ruang_id' => $ruang->id,
                    ]);

                    $slotLocked = ! $resetExisting && $preserveLocked && $assignment->exists && $assignment->is_locked;
                    $p1Complete = (bool) $assignment->pengawas_1_id;
                    $p2Complete = ! $twoSupervisorsPerRoom || (bool) $assignment->pengawas_2_id;

                    if (! $resetExisting && $assignment->exists && $p1Complete && $p2Complete) {
                        continue;
                    }

                    if ($slotLocked && $p2Complete) {
                        continue;
                    }

                    $classId = $ruang->school_class_id ? (int) $ruang->school_class_id : null;
                    $homeroomTeacherId = ($classId && isset($homeroomMap[$classId])) ? (int) $homeroomMap[$classId] : null;
                    $teachingCandidates = ($classId && $subjectId)
                        ? ($teachingMap["{$classId}:{$subjectId}"] ?? [])
                        : [];

                    $hasHomeroomCandidate = $homeroomTeacherId !== null && in_array($homeroomTeacherId, $teachingCandidates, true);

                    $pendingSlots[] = [
                        'jadwal' => $jadwal,
                        'ruang' => $ruang,
                        'assignment' => $assignment,
                        'date' => $date,
                        'start' => $start,
                        'end' => $end,
                        'jadwal_levels' => $jadwalLevels,
                        'ruang_level' => $ruang->getLevel(),
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'homeroom_teacher_id' => $homeroomTeacherId,
                        'teaching_candidates' => $teachingCandidates,
                        'has_homeroom_candidate' => $hasHomeroomCandidate,
                        'slot_locked' => $slotLocked,
                        'p1_source' => $assignment->assignment_source,
                    ];

                    $roomBusySlots[$ruang->id][] = [
                        'date' => $date,
                        'start' => $start,
                        'end' => $end,
                        'jadwal_id' => $jadwal->id,
                    ];
                }
            }

            if (empty($pendingSlots)) {
                return 0;
            }

            // PASS 1: Kunci pengawas pengampu mapel (dahulukan rombel tempat ia menjadi wali kelas)
            if ($prioritizeSubjectTeachers) {
                $pass1Indices = array_keys($pendingSlots);
                usort($pass1Indices, function (int $idxA, int $idxB) use ($pendingSlots) {
                    $a = $pendingSlots[$idxA];
                    $b = $pendingSlots[$idxB];

                    return [
                        $a['has_homeroom_candidate'] ? 0 : 1,
                        $a['date'],
                        $a['start'],
                        $a['ruang']->kode_ruang,
                        $a['jadwal']->id,
                    ] <=> [
                        $b['has_homeroom_candidate'] ? 0 : 1,
                        $b['date'],
                        $b['start'],
                        $b['ruang']->kode_ruang,
                        $b['jadwal']->id,
                    ];
                });

                foreach ($pass1Indices as $idx) {
                    $slot = &$pendingSlots[$idx];
                    $assignment = $slot['assignment'];

                    if ($slot['slot_locked'] || $assignment->pengawas_1_id) {
                        continue;
                    }

                    $pengawas1Id = $this->pickSubjectTeacherCandidate(
                        $slot['teaching_candidates'],
                        $eligibleTeacherLookup,
                        $mappings,
                        $hasAnyMapping,
                        $teacherLevels,
                        $slot['ruang_level'],
                        $slot['subject_id'],
                        $slot['class_id'],
                        $slot['homeroom_teacher_id'],
                        $slot['date'],
                        $slot['start'],
                        $slot['end'],
                        $teacherWorkload,
                        $teacherBusySlots
                    );

                    if ($pengawas1Id) {
                        $assignment->pengawas_1_id = $pengawas1Id;
                        $slot['p1_source'] = 'subject_teacher';

                        $this->registerTeacherDuty(
                            $pengawas1Id,
                            $slot['ruang']->id,
                            $slot['date'],
                            $slot['start'],
                            $slot['end'],
                            $teacherWorkload,
                            $teacherDailyCount,
                            $teacherRoomCount,
                            $teacherBusySlots
                        );
                    }
                }
                unset($slot);
            }

            // PASS 2: Isi sisa slot secara rotasi merata
            $totalPlotted = 0;
            $allTeacherIds = $teachers->pluck('id')->all();

            foreach ($pendingSlots as $slot) {
                /** @var UjianJadwalPengawas $assignment */
                $assignment = $slot['assignment'];
                $ruangId = $slot['ruang']->id;

                $pengawas1Id = $assignment->pengawas_1_id;
                $source = $slot['p1_source'];

                if (! $pengawas1Id && ! $slot['slot_locked']) {
                    $pengawas1Id = $this->pickBestTeacher(
                        $allTeacherIds,
                        $mappings,
                        $teacherLevels,
                        $slot['ruang_level'],
                        $slot['jadwal_levels'],
                        $slot['date'],
                        $slot['start'],
                        $slot['end'],
                        $ruangId,
                        null,
                        $teacherWorkload,
                        $teacherDailyCount,
                        $teacherRoomCount,
                        $teacherBusySlots
                    );

                    if ($pengawas1Id) {
                        $source = 'auto_rotation';
                        $this->registerTeacherDuty(
                            $pengawas1Id,
                            $ruangId,
                            $slot['date'],
                            $slot['start'],
                            $slot['end'],
                            $teacherWorkload,
                            $teacherDailyCount,
                            $teacherRoomCount,
                            $teacherBusySlots
                        );
                    }
                }

                $pengawas2Id = $twoSupervisorsPerRoom ? $assignment->pengawas_2_id : $assignment->pengawas_2_id;
                if ($twoSupervisorsPerRoom && ! $pengawas2Id) {
                    $pengawas2Id = $this->pickBestTeacher(
                        $allTeacherIds,
                        $mappings,
                        $teacherLevels,
                        $slot['ruang_level'],
                        $slot['jadwal_levels'],
                        $slot['date'],
                        $slot['start'],
                        $slot['end'],
                        $ruangId,
                        $pengawas1Id,
                        $teacherWorkload,
                        $teacherDailyCount,
                        $teacherRoomCount,
                        $teacherBusySlots
                    );

                    if ($pengawas2Id) {
                        $this->registerTeacherDuty(
                            $pengawas2Id,
                            $ruangId,
                            $slot['date'],
                            $slot['start'],
                            $slot['end'],
                            $teacherWorkload,
                            $teacherDailyCount,
                            $teacherRoomCount,
                            $teacherBusySlots
                        );
                    }
                }

                $assignment->pengawas_1_id = $pengawas1Id;
                $assignment->pengawas_2_id = $twoSupervisorsPerRoom ? $pengawas2Id : $assignment->pengawas_2_id;
                $assignment->assignment_source = $source ?: 'auto_rotation';
                $assignment->is_locked = $assignment->exists ? (bool) $assignment->is_locked : false;
                $assignment->status_kehadiran = $assignment->status_kehadiran ?: 'Terjadwal';
                $assignment->save();

                $totalPlotted++;
            }

            return $totalPlotted;
        });
    }

    /**
     * Memilih kandidat guru pengampu mapel terbaik pada Pass 1 (mengutamakan wali kelas rombel).
     *
     * @param  array<int, int>  $teachingCandidates
     * @param  array<int, bool>  $eligibleTeacherLookup
     * @param  Collection<int, UjianPengawasMapping>  $mappings
     * @param  array<int, array<int, string>>  $teacherLevels
     */
    private function pickSubjectTeacherCandidate(
        array $teachingCandidates,
        array $eligibleTeacherLookup,
        Collection $mappings,
        bool $hasAnyMapping,
        array $teacherLevels,
        ?string $ruangLevel,
        ?int $subjectId,
        ?int $classId,
        ?int $homeroomTeacherId,
        string $date,
        string $start,
        string $end,
        array $teacherWorkload,
        array $teacherBusySlots
    ): ?int {
        $candidates = [];

        foreach ($teachingCandidates as $tid) {
            if (! isset($eligibleTeacherLookup[$tid])) {
                continue;
            }

            if ($hasAnyMapping) {
                $mapping = $mappings->get($tid);
                if (! $mapping || ! $mapping->isPengampuMapelMode()) {
                    continue;
                }

                $allowed = $teacherLevels[$tid] ?? [];
                if ($ruangLevel !== null && ! in_array($ruangLevel, $allowed, true)) {
                    continue;
                }

                if (! $mapping->allowsSubject($subjectId) || ! $mapping->allowsClass($classId)) {
                    continue;
                }
            }

            if ($this->isTeacherBusy($teacherBusySlots[$tid] ?? [], $date, $start, $end)) {
                continue;
            }

            $candidates[] = [
                'id' => $tid,
                'homeroom_rank' => ($homeroomTeacherId !== null && $tid === $homeroomTeacherId) ? 0 : 1,
                'workload' => $teacherWorkload[$tid] ?? 0,
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function (array $a, array $b) {
            return [$a['homeroom_rank'], $a['workload'], $a['id']]
                <=> [$b['homeroom_rank'], $b['workload'], $b['id']];
        });

        return $candidates[0]['id'];
    }

    private function pickBestTeacher(
        array $teacherIds,
        Collection $mappings,
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

            $allowed = $teacherLevels[$tid] ?? [];
            $levelMatched = $ruangLevel !== null
                ? in_array($ruangLevel, $allowed, true)
                : ! empty(array_intersect($jadwalLevels, $allowed));

            if (! $levelMatched) {
                continue;
            }

            if ($this->isTeacherBusy($teacherBusySlots[$tid] ?? [], $date, $start, $end)) {
                continue;
            }

            $isPengampuMode = $mappings->get($tid)?->isPengampuMapelMode() ? 1 : 0;

            $candidates[] = [
                'id' => $tid,
                'workload' => $teacherWorkload[$tid] ?? 0,
                'mode_penalty' => $isPengampuMode,
                'daily' => $teacherDailyCount[$tid][$date] ?? 0,
                'room_repeat' => $teacherRoomCount[$tid][$ruangId] ?? 0,
                'flexibility' => count($allowed),
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function (array $a, array $b) {
            return [$a['workload'], $a['mode_penalty'], $a['daily'], $a['room_repeat'], $a['flexibility'], $a['id']]
                <=> [$b['workload'], $b['mode_penalty'], $b['daily'], $b['room_repeat'], $b['flexibility'], $b['id']];
        });

        return $candidates[0]['id'];
    }

    private function registerExistingAssignments(
        Collection $assignments,
        array &$roomBusySlots,
        array &$teacherWorkload,
        array &$teacherDailyCount,
        array &$teacherRoomCount,
        array &$teacherBusySlots
    ): void {
        foreach ($assignments as $asg) {
            if (! $asg->jadwal) {
                continue;
            }

            $date = $asg->jadwal->tanggal_ujian->format('Y-m-d');
            $start = $this->conflictService->normalizeTime($asg->jadwal->jam_mulai);
            $end = $this->conflictService->normalizeTime($asg->jadwal->jam_selesai);

            $roomBusySlots[$asg->ujian_ruang_id][] = [
                'date' => $date,
                'start' => $start,
                'end' => $end,
                'jadwal_id' => $asg->ujian_jadwal_id,
            ];

            foreach ([$asg->pengawas_1_id, $asg->pengawas_2_id] as $tid) {
                if ($tid && isset($teacherWorkload[$tid])) {
                    $this->registerTeacherDuty(
                        $tid,
                        $asg->ujian_ruang_id,
                        $date,
                        $start,
                        $end,
                        $teacherWorkload,
                        $teacherDailyCount,
                        $teacherRoomCount,
                        $teacherBusySlots
                    );
                }
            }
        }
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
