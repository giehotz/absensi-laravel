<?php

namespace App\Services\Supervisi;

use App\Enums\SupervisiStatus;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\SlotTemplate;
use App\Models\Supervisi\SupervisiJadwal;
use App\Models\Teacher;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SupervisiJadwalGeneratorService
{
    private const HARI_INDONESIA = [
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
     * Hasilkan pratinjau jadwal supervisi bebas bentrok berbasis tabel schedules & slot_templates.
     *
     * @param  array{
     *     academic_year_id: int,
     *     tanggal_mulai: string,
     *     tanggal_selesai: string,
     *     supervisor_teacher_ids: array<int, int|string>,
     *     teacher_ids: array<int, int|string>,
     *     hari_aktif: array<int, int|string>,
     *     max_per_day?: int,
     *     indikator_pmm_id?: int|null
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

        $teachers = Teacher::with(['user', 'schedules.schoolClass', 'schedules.subject'])
            ->excludeKepala()
            ->whereIn('id', $teacherIds)
            ->get()
            ->sortBy(fn (Teacher $t) => $t->user?->name ?? '')
            ->values();

        $classes = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->ordered()
            ->get();

        if ($classes->isEmpty()) {
            $classes = SchoolClass::query()->ordered()->get();
        }

        $slotTemplates = SlotTemplate::query()
            ->where('academic_year_id', $academicYearId)
            ->where('k_jadwal', SlotTemplate::K_KBM)
            ->orderBy('jam_ke')
            ->get()
            ->groupBy('day_of_week');

        // Ambil jadwal supervisi yang sudah ada di DB agar bebas bentrok
        $existingBusy = [];
        $existingSupervisorRows = SupervisiJadwal::query()
            ->whereIn('supervisor_teacher_id', $supervisorIds)
            ->whereBetween('tanggal', [$params['tanggal_mulai'], $params['tanggal_selesai']])
            ->where('status', '!=', SupervisiStatus::Batal->value)
            ->get();

        foreach ($existingSupervisorRows as $row) {
            $dateKey = $row->tanggal->toDateString();
            $existingBusy[$row->supervisor_teacher_id][$dateKey][] = [
                'start' => $row->jam_mulai ?: '07:30:00',
                'end' => $row->jam_selesai ?: '08:05:00',
            ];
        }

        $generated = [];
        $unscheduled = [];
        $supCount = count($supervisorIds);
        $supPointer = 0;
        $classFallbackIndex = 0;

        foreach ($teachers as $teacher) {
            $placed = false;

            foreach ($workingDates as $date) {
                $dateStr = $date->toDateString();
                $dayIso = $date->dayOfWeekIso;

                $candidateSlots = $this->resolveCandidateSlotsForTeacher(
                    $teacher,
                    $dayIso,
                    $slotTemplates->get($dayIso, collect()),
                    $classes,
                    $classFallbackIndex
                );

                for ($attempt = 0; $attempt < $supCount; $attempt++) {
                    $supId = $supervisorIds[($supPointer + $attempt) % $supCount];
                    if ($supId === (int) $teacher->id) {
                        continue;
                    }

                    $busyToday = $existingBusy[$supId][$dateStr] ?? [];
                    if (count($busyToday) >= $maxPerDay) {
                        continue;
                    }

                    foreach ($candidateSlots as $slot) {
                        if ($this->hasTimeOverlap($busyToday, $slot['jam_mulai'], $slot['jam_selesai'])) {
                            continue;
                        }

                        $supervisor = $supervisors->get($supId);
                        $generated[] = [
                            'academic_year_id' => $academicYearId,
                            'teacher_id' => $teacher->id,
                            'nama_guru' => $teacher->user?->name ?? 'Guru',
                            'nip_guru' => $teacher->nip ?? '-',
                            'supervisor_teacher_id' => $supId,
                            'nama_supervisor' => $supervisor?->user?->name ?? 'Supervisor',
                            'school_class_id' => $slot['school_class_id'],
                            'nama_kelas' => $slot['nama_kelas'],
                            'subject_id' => $slot['subject_id'],
                            'nama_mapel' => $slot['nama_mapel'],
                            'indikator_pmm_id' => $indikatorPmmId,
                            'tanggal' => $dateStr,
                            'hari' => self::HARI_INDONESIA[$dayIso] ?? 'Senin',
                            'jam_ke' => $slot['jam_ke'],
                            'jam_mulai' => substr($slot['jam_mulai'], 0, 5),
                            'jam_selesai' => substr($slot['jam_selesai'], 0, 5),
                            'materi' => 'Supervisi Akademik Proses Pembelajaran',
                        ];

                        $existingBusy[$supId][$dateStr][] = [
                            'start' => $slot['jam_mulai'],
                            'end' => $slot['jam_selesai'],
                        ];

                        $supPointer = ($supPointer + $attempt + 1) % $supCount;
                        $placed = true;
                        break 3;
                    }
                }
            }

            if (! $placed) {
                $unscheduled[] = $teacher->user?->name ?? "Teacher #{$teacher->id}";
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
            foreach ($items as $idx => $item) {
                if (in_array((int) $idx, $excluded, true)) {
                    continue;
                }

                $tanggal = $overrides[$idx]['tanggal'] ?? $item['tanggal'];
                $dayIso = Carbon::parse($tanggal)->dayOfWeekIso;

                SupervisiJadwal::create([
                    'academic_year_id' => (int) $item['academic_year_id'],
                    'teacher_id' => (int) $item['teacher_id'],
                    'supervisor_teacher_id' => (int) ($overrides[$idx]['supervisor_teacher_id'] ?? $item['supervisor_teacher_id']),
                    'school_class_id' => ! empty($overrides[$idx]['school_class_id']) ? (int) $overrides[$idx]['school_class_id'] : ($item['school_class_id'] ?: null),
                    'subject_id' => ! empty($item['subject_id']) ? (int) $item['subject_id'] : null,
                    'indikator_pmm_id' => ! empty($item['indikator_pmm_id']) ? (int) $item['indikator_pmm_id'] : null,
                    'tanggal' => $tanggal,
                    'hari' => self::HARI_INDONESIA[$dayIso] ?? ($item['hari'] ?? 'Senin'),
                    'jam_ke' => (string) ($overrides[$idx]['jam_ke'] ?? $item['jam_ke'] ?? '1'),
                    'jam_mulai' => $overrides[$idx]['jam_mulai'] ?? $item['jam_mulai'] ?? '07:30',
                    'jam_selesai' => $overrides[$idx]['jam_selesai'] ?? $item['jam_selesai'] ?? '08:05',
                    'materi' => $item['materi'] ?? 'Supervisi Akademik Proses Pembelajaran',
                    'status' => SupervisiStatus::Terjadwal->value,
                ]);

                $count++;
            }
        });

        return $count;
    }

    /**
     * @param  Collection<int, SlotTemplate>  $daySlotTemplates
     * @param  Collection<int, SchoolClass>  $classes
     * @return array<int, array<string, mixed>>
     */
    private function resolveCandidateSlotsForTeacher(
        Teacher $teacher,
        int $dayIso,
        Collection $daySlotTemplates,
        Collection $classes,
        int &$classFallbackIndex
    ): array {
        $teacherSchedules = $teacher->schedules
            ->where('day_of_week', $dayIso)
            ->sortBy('start_time')
            ->values();

        if ($teacherSchedules->isNotEmpty()) {
            return $teacherSchedules->map(function (Schedule $schedule) use ($daySlotTemplates): array {
                $matchedSlot = $daySlotTemplates->first(
                    fn (SlotTemplate $slot) => substr((string) $slot->start_time, 0, 5) === substr((string) $schedule->start_time, 0, 5)
                );

                return [
                    'school_class_id' => $schedule->school_class_id,
                    'nama_kelas' => $schedule->schoolClass?->name ?? '-',
                    'subject_id' => $schedule->subject_id,
                    'nama_mapel' => $schedule->subject?->name ?? '-',
                    'jam_ke' => (string) ($matchedSlot?->jam_ke ?? '1'),
                    'jam_mulai' => (string) $schedule->start_time,
                    'jam_selesai' => (string) $schedule->end_time,
                ];
            })->all();
        }

        $fallbackClass = $classes->isNotEmpty() ? $classes[$classFallbackIndex % $classes->count()] : null;
        $classFallbackIndex++;

        if ($daySlotTemplates->isNotEmpty()) {
            return $daySlotTemplates->map(fn (SlotTemplate $slot): array => [
                'school_class_id' => $fallbackClass?->id,
                'nama_kelas' => $fallbackClass?->name ?? 'Semua Kelas',
                'subject_id' => null,
                'nama_mapel' => 'Mata Pelajaran Umum',
                'jam_ke' => (string) $slot->jam_ke,
                'jam_mulai' => (string) $slot->start_time,
                'jam_selesai' => (string) $slot->end_time,
            ])->all();
        }

        return array_map(fn (array $slot): array => [
            'school_class_id' => $fallbackClass?->id,
            'nama_kelas' => $fallbackClass?->name ?? 'Semua Kelas',
            'subject_id' => null,
            'nama_mapel' => 'Mata Pelajaran Umum',
            'jam_ke' => $slot['jam_ke'],
            'jam_mulai' => $slot['jam_mulai'],
            'jam_selesai' => $slot['jam_selesai'],
        ], self::DEFAULT_SLOTS);
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
