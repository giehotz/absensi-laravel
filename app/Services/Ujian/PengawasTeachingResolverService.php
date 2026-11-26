<?php

namespace App\Services\Ujian;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Ujian\UjianJadwal;
use Illuminate\Support\Collection;

class PengawasTeachingResolverService
{
    /**
     * Memuat seluruh peta mengajar guru:
     * ['{school_class_id}:{subject_id}' => [teacher_id_1, ...]]
     * Sumber utama: teacher_assignments, fallback: schedules.
     *
     * @return array<string, array<int, int>>
     */
    public function buildTeachingMap(?int $academicYearId = null, ?array $targetLevels = null): array
    {
        $classIds = $this->resolveClasses($academicYearId, $targetLevels)->keys();
        if ($classIds->isEmpty()) {
            return [];
        }

        $map = [];

        foreach ($this->loadAssignmentRows($classIds) as $row) {
            $key = "{$row['school_class_id']}:{$row['subject_id']}";
            $map[$key][] = $row['teacher_id'];
        }

        foreach ($map as $key => $ids) {
            $map[$key] = array_values(array_unique($ids));
        }

        return $map;
    }

    /**
     * Membangun ringkasan beban mengajar per guru untuk UI pemetaan & sinkronisasi otomatis.
     *
     * @return array<int, array{
     *     teacher_id: int,
     *     levels: array<int, string>,
     *     class_ids: array<int, int>,
     *     class_names: array<int, string>,
     *     subject_ids: array<int, int>,
     *     subject_names: array<int, string>
     * }>
     */
    public function buildTeacherTeachingSummary(?int $academicYearId = null, ?array $targetLevels = null): array
    {
        $classes = $this->resolveClasses($academicYearId, $targetLevels);
        if ($classes->isEmpty()) {
            return [];
        }

        $subjectNames = Subject::pluck('name', 'id');
        $summary = [];

        foreach ($this->loadAssignmentRows($classes->keys()) as $row) {
            $tid = $row['teacher_id'];
            $cid = $row['school_class_id'];
            $sid = $row['subject_id'];
            $cls = $classes->get($cid);

            if (! $cls) {
                continue;
            }

            if (! isset($summary[$tid])) {
                $summary[$tid] = [
                    'teacher_id' => $tid,
                    'levels' => [],
                    'class_ids' => [],
                    'class_names' => [],
                    'subject_ids' => [],
                    'subject_names' => [],
                ];
            }

            $level = $this->extractNumericLevel($cls);
            if ($level !== null && ! in_array($level, $summary[$tid]['levels'], true)) {
                $summary[$tid]['levels'][] = $level;
            }
            if (! in_array($cid, $summary[$tid]['class_ids'], true)) {
                $summary[$tid]['class_ids'][] = $cid;
                $summary[$tid]['class_names'][] = $cls->name;
            }
            if (! in_array($sid, $summary[$tid]['subject_ids'], true)) {
                $summary[$tid]['subject_ids'][] = $sid;
                if ($subjectNames->has($sid)) {
                    $summary[$tid]['subject_names'][] = (string) $subjectNames->get($sid);
                }
            }
        }

        return $summary;
    }

    /**
     * Membangun peta wali kelas per guru untuk sinkronisasi & UI pemetaan.
     *
     * @return array<int, array{
     *     teacher_id: int,
     *     levels: array<int, string>,
     *     class_ids: array<int, int>,
     *     class_names: array<int, string>
     * }>
     */
    public function buildHomeroomMap(?int $academicYearId = null, ?array $targetLevels = null): array
    {
        $classes = $this->resolveClasses($academicYearId, $targetLevels);
        $homeroomMap = [];

        foreach ($classes as $cls) {
            if (! $cls->homeroom_teacher_id) {
                continue;
            }

            $tid = (int) $cls->homeroom_teacher_id;
            if (! isset($homeroomMap[$tid])) {
                $homeroomMap[$tid] = [
                    'teacher_id' => $tid,
                    'levels' => [],
                    'class_ids' => [],
                    'class_names' => [],
                ];
            }

            $level = $this->extractNumericLevel($cls);
            if ($level !== null && ! in_array($level, $homeroomMap[$tid]['levels'], true)) {
                $homeroomMap[$tid]['levels'][] = $level;
            }
            $homeroomMap[$tid]['class_ids'][] = (int) $cls->id;
            $homeroomMap[$tid]['class_names'][] = $cls->name;
        }

        return $homeroomMap;
    }

    /**
     * Resolusi subject_id dari UjianJadwal (mendukung nama_mapel_kustom).
     *
     * @param  Collection<string, int>  $subjectsByNormalizedName
     */
    public function resolveSubjectId(UjianJadwal $jadwal, Collection $subjectsByNormalizedName): ?int
    {
        if ($jadwal->subject_id) {
            return (int) $jadwal->subject_id;
        }

        $normalized = $this->normalizeSubjectName((string) $jadwal->nama_mapel_kustom);

        return $normalized !== '' ? $subjectsByNormalizedName->get($normalized) : null;
    }

    public function normalizeSubjectName(string $name): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($name)) ?? '');
    }

    public function extractNumericLevel(SchoolClass $class): ?string
    {
        if (preg_match('/^[1-6]$/', trim((string) $class->level))) {
            return trim((string) $class->level);
        }

        if (preg_match('/\d+/', (string) $class->name, $matches)) {
            $num = (int) $matches[0];
            if ($num >= 1 && $num <= 6) {
                return (string) $num;
            }
        }

        return null;
    }

    /**
     * Memuat SchoolClass dengan penyaringan tingkat numerik di PHP agar aman saat `school_classes.level` bernilai jenjang (mis. 'MI').
     *
     * @return Collection<int, SchoolClass>
     */
    private function resolveClasses(?int $academicYearId, ?array $targetLevels): Collection
    {
        $classes = SchoolClass::query()
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('name')
            ->get(['id', 'name', 'level', 'homeroom_teacher_id', 'academic_year_id']);

        if (empty($targetLevels)) {
            return $classes->keyBy('id');
        }

        $targetLevelsStr = array_map('strval', $targetLevels);

        return $classes->filter(function (SchoolClass $cls) use ($targetLevelsStr) {
            $numericLevel = $this->extractNumericLevel($cls);

            return $numericLevel !== null && in_array($numericLevel, $targetLevelsStr, true);
        })->keyBy('id');
    }

    /**
     * Menggabungkan TeacherAssignment (prioritas utama) dan Schedule (cadangan jika pasangan class+subject belum ada).
     *
     * @param  Collection<int, int>  $classIds
     * @return array<int, array{school_class_id: int, subject_id: int, teacher_id: int}>
     */
    private function loadAssignmentRows(Collection $classIds): array
    {
        $rows = [];
        $coveredPairs = [];

        $assignments = TeacherAssignment::whereIn('school_class_id', $classIds)->get();
        foreach ($assignments as $asg) {
            $key = "{$asg->school_class_id}:{$asg->subject_id}";
            $coveredPairs[$key] = true;
            $rows[] = [
                'school_class_id' => (int) $asg->school_class_id,
                'subject_id' => (int) $asg->subject_id,
                'teacher_id' => (int) $asg->teacher_id,
            ];
        }

        $schedules = Schedule::whereIn('school_class_id', $classIds)
            ->select('school_class_id', 'subject_id', 'teacher_id')
            ->distinct()
            ->get();

        foreach ($schedules as $sch) {
            $key = "{$sch->school_class_id}:{$sch->subject_id}";
            if (! isset($coveredPairs[$key])) {
                $rows[] = [
                    'school_class_id' => (int) $sch->school_class_id,
                    'subject_id' => (int) $sch->subject_id,
                    'teacher_id' => (int) $sch->teacher_id,
                ];
            }
        }

        return $rows;
    }
}
