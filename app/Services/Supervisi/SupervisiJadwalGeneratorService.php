<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Models\Schedule;
use App\Models\SlotTemplate;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupervisiJadwalGeneratorService
{
    public const HARI_INDONESIA = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    private const DEFAULT_SLOTS = [
        ['jam_ke' => '1', 'jam_mulai' => '07:30:00', 'jam_selesai' => '08:05:00'],
        ['jam_ke' => '2', 'jam_mulai' => '08:05:00', 'jam_selesai' => '08:40:00'],
        ['jam_ke' => '3', 'jam_mulai' => '08:40:00', 'jam_selesai' => '09:15:00'],
        ['jam_ke' => '4', 'jam_mulai' => '09:15:00', 'jam_selesai' => '09:50:00'],
        ['jam_ke' => '6', 'jam_mulai' => '10:10:00', 'jam_selesai' => '10:45:00'],
        ['jam_ke' => '7', 'jam_mulai' => '10:45:00', 'jam_selesai' => '11:20:00'],
    ];

    /**
     * Hasilkan pratinjau jadwal supervisi bebas bentrok berbasis 2 lapis sumber mengajar:
     * Tier 1: schedules (KBM harian) -> Tier 2: teacher_assignments (kelas & mapel ampuan).
     *
     * @param  array{
     *     academic_year_id: int,
     *     tanggal_mulai: string,
     *     tanggal_selesai: string,
     *     supervisor_teacher_ids: array<int, int|string>,
     *     teacher_ids: array<int, int|string>,
     *     hari_aktif: array<int, int|string>,
     *     max_per_day?: int,
     *     indikator_pmm_id?: int|null,
     *     pendekatan?: string|null,
     *     teknik_supervisi?: string|null
     * }  $params
     * @return array{
     *     generated: array<int, array<string, mixed>>,
     *     unscheduled_teachers: array<int, string>,
     *     working_days_count: int
     * }
     */
    public function preview(array $params): array
    {
        $academicYearId = (int) $params['academic_year_id'];
        $supervisorIds = array_values(array_unique(array_map('intval', $params['supervisor_teacher_ids'])));
        $teacherIds = array_values(array_unique(array_map('intval', $params['teacher_ids'])));
        $activeDays = array_values(array_unique(array_map('intval', $params['hari_aktif'])));
        $maxPerDay = max(1, (int) ($params['max_per_day'] ?? 2));
        $indikatorPmmId = ! empty($params['indikator_pmm_id']) ? (int) $params['indikator_pmm_id'] : null;
        $pendekatan = (string) ($params['pendekatan'] ?? 'kolaboratif');
        $teknikSupervisi = (string) ($params['teknik_supervisi'] ?? 'kunjungan_kelas');

        $workingDates = [];
        foreach (CarbonPeriod::create($params['tanggal_mulai'], $params['tanggal_selesai']) as $date) {
            if (in_array($date->dayOfWeekIso, $activeDays, true)) {
                $workingDates[] = $date->copy();
            }
        }

        $supervisors = Teacher::with('user')
            ->whereIn('id', $supervisorIds)
            ->get()
            ->keyBy('id');

        $teachers = Teacher::with([
            'user',
            'schedules.schoolClass',
            'schedules.subject',
            'assignments.schoolClass',
            'assignments.subject',
        ])
            ->excludeKepala()
            ->whereIn('id', $teacherIds)
            ->get()
            ->sortBy(fn (Teacher $t): string => $t->user?->name ?? '')
            ->values();

        $slotTemplates = SlotTemplate::query()
            ->where('academic_year_id', $academicYearId)
            ->where('k_jadwal', SlotTemplate::K_KBM)
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('day_of_week');

        // KBM Supervisor untuk soft-constraint agar supervisor tidak ditarik saat sedang mengajar
        $supervisorKbmSchedules = Schedule::query()
            ->whereIn('teacher_id', $supervisorIds)
            ->get()
            ->groupBy('teacher_id');

        $supervisorTotalWorkload = SupervisiJadwal::query()
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->whereIn('supervisor_teacher_id', $supervisorIds)
            ->selectRaw('supervisor_teacher_id, COUNT(*) as total')
            ->groupBy('supervisor_teacher_id')
            ->pluck('total', 'supervisor_teacher_id')
            ->map(fn (mixed $v): int => (int) $v)
            ->all();

        $existingSupervisedSubjects = SupervisiJadwal::query()
            ->where('academic_year_id', $academicYearId)
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->whereIn('teacher_id', $teacherIds)
            ->get(['teacher_id', 'subject_id', 'school_class_id'])
            ->groupBy('teacher_id');

        // Muat jadwal supervisi aktif yang sudah ada di rentang tanggal agar bebas bentrok
        $supervisorBusy = [];
        $teacherBusy = [];
        $existingJadwalRows = SupervisiJadwal::query()
            ->where(function ($query) use ($supervisorIds, $teacherIds): void {
                $query->whereIn('supervisor_teacher_id', $supervisorIds)
                    ->orWhereIn('teacher_id', $teacherIds);
            })
            ->whereBetween('tanggal', [$params['tanggal_mulai'], $params['tanggal_selesai']])
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->get();

        foreach ($existingJadwalRows as $row) {
            $dateKey = $row->tanggal->toDateString();
            $slotRange = [
                'start' => $row->jam_mulai ?: '07:30:00',
                'end' => $row->jam_selesai ?: '08:05:00',
            ];
            if ($row->supervisor_teacher_id) {
                $supervisorBusy[$row->supervisor_teacher_id][$dateKey][] = $slotRange;
            }
            if ($row->teacher_id) {
                $teacherBusy[$row->teacher_id][$dateKey][] = $slotRange;
            }
        }

        $generated = [];
        $unscheduled = [];

        // Prioritaskan guru dengan jadwal KBM tetap (dan slot paling sedikit) sebelum guru fallback mapel ampuan
        $orderedEntries = $teachers->map(function (Teacher $teacher) use ($academicYearId, $activeDays, $existingSupervisedSubjects): array {
            $kbmActive = $this->filterTeacherKbm($teacher, $academicYearId, $activeDays);
            $hasTier1Kbm = $kbmActive->isNotEmpty();
            $fallbackAssignment = $hasTier1Kbm
                ? null
                : $this->resolvePriorityAssignment($teacher, $academicYearId, $existingSupervisedSubjects->get($teacher->id, collect()));

            return [
                'teacher' => $teacher,
                'has_kbm' => $hasTier1Kbm,
                'kbm_active' => $kbmActive,
                'fallback_assignment' => $fallbackAssignment,
                'constraint_weight' => $hasTier1Kbm ? $kbmActive->count() : PHP_INT_MAX,
            ];
        })->sortBy(fn (array $entry): array => [
            $entry['has_kbm'] ? 0 : 1,
            $entry['constraint_weight'],
            $entry['teacher']->user?->name ?? '',
        ])->values();

        foreach ($orderedEntries as $entry) {
            /** @var Teacher $teacher */
            $teacher = $entry['teacher'];
            $kbmActive = $entry['kbm_active'];
            $hasTier1Kbm = $entry['has_kbm'];
            $fallbackAssignment = $entry['fallback_assignment'];

            if (! $hasTier1Kbm && ! $fallbackAssignment) {
                $unscheduled[] = ($teacher->user?->name ?? "Teacher #{$teacher->id}").' (Belum ada jadwal di /admin/schedules maupun mapel/kelas ampuan)';

                continue;
            }

            $placed = false;

            foreach ($workingDates as $date) {
                $dateStr = $date->toDateString();
                $dayIso = $date->dayOfWeekIso;

                $candidateSlots = $hasTier1Kbm
                    ? $this->buildKbmSlotsForDay($kbmActive, $dayIso, $slotTemplates->get($dayIso, collect()))
                    : $this->buildAssignmentSlotsForDay($fallbackAssignment, $slotTemplates->get($dayIso, collect()));

                if (empty($candidateSlots)) {
                    continue;
                }

                foreach ($candidateSlots as $slot) {
                    if ($this->hasTimeOverlap($teacherBusy[$teacher->id][$dateStr] ?? [], $slot['jam_mulai'], $slot['jam_selesai'])) {
                        continue;
                    }

                    $bestSupervisorId = $this->pickBestSupervisor(
                        $supervisorIds,
                        (int) $teacher->id,
                        $dateStr,
                        $dayIso,
                        $slot['jam_mulai'],
                        $slot['jam_selesai'],
                        $maxPerDay,
                        $supervisorBusy,
                        $supervisorTotalWorkload,
                        $supervisorKbmSchedules
                    );

                    if ($bestSupervisorId === null) {
                        continue;
                    }

                    $supervisor = $supervisors->get($bestSupervisorId);
                    $generated[] = [
                        'academic_year_id' => $academicYearId,
                        'teacher_id' => $teacher->id,
                        'nama_guru' => $teacher->user?->name ?? 'Guru',
                        'nip_guru' => $teacher->nip ?? '-',
                        'supervisor_teacher_id' => $bestSupervisorId,
                        'nama_supervisor' => $supervisor?->user?->name ?? 'Supervisor',
                        'school_class_id' => $slot['school_class_id'],
                        'nama_kelas' => $slot['nama_kelas'],
                        'subject_id' => $slot['subject_id'],
                        'nama_mapel' => $slot['nama_mapel'],
                        'indikator_pmm_id' => $indikatorPmmId,
                        'pendekatan' => $pendekatan,
                        'teknik_supervisi' => $teknikSupervisi,
                        'sumber_slot' => $slot['sumber_slot'],
                        'tanggal' => $dateStr,
                        'hari' => self::HARI_INDONESIA[$dayIso] ?? 'Senin',
                        'jam_ke' => $slot['jam_ke'],
                        'jam_mulai' => substr($slot['jam_mulai'], 0, 5),
                        'jam_selesai' => substr($slot['jam_selesai'], 0, 5),
                        'materi' => 'Supervisi Akademik Proses Pembelajaran',
                    ];

                    $slotRange = ['start' => $slot['jam_mulai'], 'end' => $slot['jam_selesai']];
                    $supervisorBusy[$bestSupervisorId][$dateStr][] = $slotRange;
                    $teacherBusy[$teacher->id][$dateStr][] = $slotRange;
                    $supervisorTotalWorkload[$bestSupervisorId] = ($supervisorTotalWorkload[$bestSupervisorId] ?? 0) + 1;

                    $placed = true;
                    break 2;
                }
            }

            if (! $placed) {
                $unscheduled[] = ($teacher->user?->name ?? "Teacher #{$teacher->id}").' (Seluruh slot bentrok dengan batas harian / jadwal lain)';
            }
        }

        return [
            'generated' => $generated,
            'unscheduled_teachers' => $unscheduled,
            'working_days_count' => count($workingDates),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, array<string, mixed>>  $overrides
     * @param  array<int, int|string>  $excludedIndices
     */
    public function storeBatch(array $items, array $overrides = [], array $excludedIndices = []): int
    {
        $excluded = array_map('intval', $excludedIndices);
        $count = 0;

        DB::transaction(function () use ($items, $overrides, $excluded, &$count): void {
            $dates = collect($items)
                ->map(fn (array $item, int $idx): ?string => in_array($idx, $excluded, true) ? null : ($overrides[$idx]['tanggal'] ?? $item['tanggal'] ?? null))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $supervisorBusy = [];
            $teacherBusy = [];

            if (! empty($dates)) {
                $existingRows = SupervisiJadwal::query()
                    ->whereIn('tanggal', $dates)
                    ->where('status', '!=', SupervisiStatus::Batal->value)
                    ->get();

                foreach ($existingRows as $row) {
                    $dKey = $row->tanggal->toDateString();
                    $range = [
                        'start' => $row->jam_mulai ?: '07:30:00',
                        'end' => $row->jam_selesai ?: '08:05:00',
                    ];
                    if ($row->supervisor_teacher_id) {
                        $supervisorBusy[$row->supervisor_teacher_id][$dKey][] = $range;
                    }
                    if ($row->teacher_id) {
                        $teacherBusy[$row->teacher_id][$dKey][] = $range;
                    }
                }
            }

            foreach ($items as $idx => $item) {
                if (in_array((int) $idx, $excluded, true)) {
                    continue;
                }

                $teacherId = (int) $item['teacher_id'];
                $supervisorId = (int) ($overrides[$idx]['supervisor_teacher_id'] ?? $item['supervisor_teacher_id']);
                if ($supervisorId === $teacherId) {
                    throw new \DomainException('Guru yang disupervisi tidak boleh sama dengan supervisor penilai.');
                }

                $tanggal = (string) ($overrides[$idx]['tanggal'] ?? $item['tanggal']);
                $jamMulai = (string) ($overrides[$idx]['jam_mulai'] ?? $item['jam_mulai'] ?? '07:30');
                $jamSelesai = (string) ($overrides[$idx]['jam_selesai'] ?? $item['jam_selesai'] ?? '08:05');

                if (
                    $this->hasTimeOverlap($supervisorBusy[$supervisorId][$tanggal] ?? [], $jamMulai, $jamSelesai)
                    || $this->hasTimeOverlap($teacherBusy[$teacherId][$tanggal] ?? [], $jamMulai, $jamSelesai)
                ) {
                    throw new \DomainException("Terdapat bentrok waktu jadwal supervisi pada tanggal {$tanggal} ({$jamMulai}-{$jamSelesai}).");
                }

                $dayIso = Carbon::parse($tanggal)->dayOfWeekIso;

                SupervisiJadwal::create([
                    'academic_year_id' => (int) $item['academic_year_id'],
                    'teacher_id' => $teacherId,
                    'supervisor_teacher_id' => $supervisorId,
                    'school_class_id' => ! empty($overrides[$idx]['school_class_id']) ? (int) $overrides[$idx]['school_class_id'] : ($item['school_class_id'] ?: null),
                    'subject_id' => ! empty($item['subject_id']) ? (int) $item['subject_id'] : null,
                    'indikator_pmm_id' => ! empty($item['indikator_pmm_id']) ? (int) $item['indikator_pmm_id'] : null,
                    'tanggal' => $tanggal,
                    'hari' => self::HARI_INDONESIA[$dayIso] ?? ($item['hari'] ?? 'Senin'),
                    'jam_ke' => (string) ($overrides[$idx]['jam_ke'] ?? $item['jam_ke'] ?? '1'),
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'materi' => $item['materi'] ?? 'Supervisi Akademik Proses Pembelajaran',
                    'pendekatan' => (string) ($overrides[$idx]['pendekatan'] ?? $item['pendekatan'] ?? 'kolaboratif'),
                    'teknik_supervisi' => (string) ($overrides[$idx]['teknik_supervisi'] ?? $item['teknik_supervisi'] ?? 'kunjungan_kelas'),
                    'sumber_slot' => (string) ($item['sumber_slot'] ?? 'manual'),
                    'status' => SupervisiStatus::Terjadwal->value,
                ]);

                $slotRange = ['start' => $jamMulai, 'end' => $jamSelesai];
                $supervisorBusy[$supervisorId][$tanggal][] = $slotRange;
                $teacherBusy[$teacherId][$tanggal][] = $slotRange;

                $count++;
            }
        });

        return $count;
    }

    /**
     * @param  array<int, int>  $activeDays
     * @return Collection<int, Schedule>
     */
    private function filterTeacherKbm(Teacher $teacher, int $academicYearId, array $activeDays): Collection
    {
        $yearMatches = $teacher->schedules
            ->whereIn('day_of_week', $activeDays)
            ->filter(fn (Schedule $s): bool => (int) $s->schoolClass?->academic_year_id === $academicYearId)
            ->values();

        if ($yearMatches->isNotEmpty()) {
            return $yearMatches;
        }

        return $teacher->schedules
            ->whereIn('day_of_week', $activeDays)
            ->values();
    }

    /**
     * @param  Collection<int, SupervisiJadwal>  $supervisedRows
     */
    private function resolvePriorityAssignment(Teacher $teacher, int $academicYearId, Collection $supervisedRows): ?TeacherAssignment
    {
        $assignments = $teacher->assignments
            ->filter(fn (TeacherAssignment $a): bool => (int) $a->schoolClass?->academic_year_id === $academicYearId)
            ->values();

        if ($assignments->isEmpty()) {
            $assignments = $teacher->assignments->values();
        }

        if ($assignments->isEmpty()) {
            return null;
        }

        $supervisedSubjectIds = $supervisedRows->pluck('subject_id')->filter()->map(fn (mixed $id): int => (int) $id)->all();

        return $assignments
            ->sortBy(function (TeacherAssignment $assignment) use ($teacher, $supervisedSubjectIds): array {
                $isHomeroom = (int) $assignment->schoolClass?->homeroom_teacher_id === (int) $teacher->id ? 0 : 1;
                $isUnsupervised = ! in_array((int) $assignment->subject_id, $supervisedSubjectIds, true) ? 0 : 1;

                return [$isHomeroom, $isUnsupervised, (string) ($assignment->schoolClass?->name ?? '')];
            })
            ->first();
    }

    /**
     * @param  Collection<int, Schedule>  $kbmSchedules
     * @param  Collection<int, SlotTemplate>  $daySlotTemplates
     * @return array<int, array<string, mixed>>
     */
    private function buildKbmSlotsForDay(Collection $kbmSchedules, int $dayIso, Collection $daySlotTemplates): array
    {
        return $kbmSchedules
            ->where('day_of_week', $dayIso)
            ->sortBy('start_time')
            ->values()
            ->map(function (Schedule $schedule) use ($daySlotTemplates): array {
                $matchedSlot = $daySlotTemplates->first(
                    fn (SlotTemplate $slot): bool => substr((string) $slot->start_time, 0, 5) === substr((string) $schedule->start_time, 0, 5)
                );

                return [
                    'school_class_id' => $schedule->school_class_id,
                    'nama_kelas' => $schedule->schoolClass?->name ?? '-',
                    'subject_id' => $schedule->subject_id,
                    'nama_mapel' => $schedule->subject?->name ?? '-',
                    'jam_ke' => (string) ($matchedSlot?->jam_ke ?? '1'),
                    'jam_mulai' => (string) $schedule->start_time,
                    'jam_selesai' => (string) $schedule->end_time,
                    'sumber_slot' => 'schedule_kbm',
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, SlotTemplate>  $daySlotTemplates
     * @return array<int, array<string, mixed>>
     */
    private function buildAssignmentSlotsForDay(TeacherAssignment $assignment, Collection $daySlotTemplates): array
    {
        $slots = $daySlotTemplates->isNotEmpty()
            ? $daySlotTemplates->map(fn (SlotTemplate $slot): array => [
                'jam_ke' => (string) $slot->jam_ke,
                'jam_mulai' => (string) $slot->start_time,
                'jam_selesai' => (string) $slot->end_time,
            ])->all()
            : self::DEFAULT_SLOTS;

        return array_map(fn (array $slot): array => [
            'school_class_id' => $assignment->school_class_id,
            'nama_kelas' => $assignment->schoolClass?->name ?? '-',
            'subject_id' => $assignment->subject_id,
            'nama_mapel' => $assignment->subject?->name ?? '-',
            'jam_ke' => $slot['jam_ke'],
            'jam_mulai' => $slot['jam_mulai'],
            'jam_selesai' => $slot['jam_selesai'],
            'sumber_slot' => 'teacher_assignment_fallback',
        ], $slots);
    }

    /**
     * @param  array<int, int>  $supervisorIds
     * @param  array<int, array<string, array<int, array{start: string, end: string}>>>  $supervisorBusy
     * @param  array<int, int>  $supervisorTotalWorkload
     * @param  Collection<int, Collection<int, Schedule>>  $supervisorKbmSchedules
     */
    private function pickBestSupervisor(
        array $supervisorIds,
        int $teacherId,
        string $dateStr,
        int $dayIso,
        string $start,
        string $end,
        int $maxPerDay,
        array $supervisorBusy,
        array $supervisorTotalWorkload,
        Collection $supervisorKbmSchedules
    ): ?int {
        $candidates = [];

        foreach ($supervisorIds as $supId) {
            if ($supId === $teacherId) {
                continue;
            }

            $busyToday = $supervisorBusy[$supId][$dateStr] ?? [];
            if (count($busyToday) >= $maxPerDay || $this->hasTimeOverlap($busyToday, $start, $end)) {
                continue;
            }

            $kbmToday = $supervisorKbmSchedules->get($supId, collect())
                ->where('day_of_week', $dayIso)
                ->map(fn (Schedule $s): array => ['start' => (string) $s->start_time, 'end' => (string) $s->end_time])
                ->all();

            $candidates[] = [
                'id' => $supId,
                'teaching_clash' => $this->hasTimeOverlap($kbmToday, $start, $end) ? 1 : 0,
                'daily_count' => count($busyToday),
                'total_workload' => $supervisorTotalWorkload[$supId] ?? 0,
            ];
        }

        if (empty($candidates)) {
            return null;
        }

        usort(
            $candidates,
            fn (array $a, array $b): int => [$a['teaching_clash'], $a['daily_count'], $a['total_workload'], $a['id']]
                <=> [$b['teaching_clash'], $b['daily_count'], $b['total_workload'], $b['id']]
        );

        return $candidates[0]['id'];
    }

    /**
     * @param  array<int, array{start: string, end: string}>  $busyList
     */
    private function hasTimeOverlap(array $busyList, string $start, string $end): bool
    {
        $startNorm = substr($start, 0, 5);
        $endNorm = substr($end, 0, 5);

        foreach ($busyList as $busy) {
            $bStart = substr((string) $busy['start'], 0, 5);
            $bEnd = substr((string) $busy['end'], 0, 5);

            if ($startNorm < $bEnd && $endNorm > $bStart) {
                return true;
            }
        }

        return false;
    }
}
