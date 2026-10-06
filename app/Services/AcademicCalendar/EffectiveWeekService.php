<?php

namespace App\Services\AcademicCalendar;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\Holiday;
use App\Models\Schedule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Menghitung Analisis Minggu Efektif per semester secara dinamis:
 * - academic_years     : rentang tanggal semester
 * - holidays           : hari libur nasional & cuti bersama aktif (sinkron API)
 * - academic_calendars : kegiatan kalender berkategori libur / ujian (non-KBM)
 * - schedules          : deteksi beban JP per minggu dari jadwal mengajar
 */
class EffectiveWeekService
{
    /**
     * Kategori kalender pendidikan yang meniadakan KBM.
     *
     * @var list<string>
     */
    public const BLOCKING_CATEGORIES = ['libur', 'ujian'];

    /**
     * @var array<int, string>
     */
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Analisis jumlah minggu kalender, minggu efektif, dan hari efektif dalam satu semester.
     *
     * @param  int  $schoolDays  Jumlah hari sekolah per pekan (5 = Senin-Jumat, 6 = Senin-Sabtu).
     * @param  int  $minEffectiveDays  Minimal hari KBM dalam satu pekan agar dihitung 1 minggu efektif.
     * @return array{
     *     year_name: string,
     *     semester: string,
     *     start_date: string,
     *     end_date: string,
     *     months: list<array{key: string, label: string, weeks: int, effective_weeks: int, effective_days: int, notes: string}>,
     *     weeks: list<array{start: string, end: string, month: string, school_days: int, effective_days: int, is_effective: bool, reasons: list<string>}>,
     *     total_weeks: int,
     *     total_effective_weeks: int,
     *     total_effective_days: int
     * }
     */
    public function analyze(string $yearName, string $semester, int $schoolDays = 5, int $minEffectiveDays = 3): array
    {
        $schoolDays = max(1, min(7, $schoolDays));
        $minEffectiveDays = max(1, $minEffectiveDays);

        [$start, $end] = $this->resolveRange($yearName, $semester);
        $blocked = $this->blockedDays($yearName, $semester, $start, $end);

        $weeks = [];
        $weekNumber = 1;

        for ($weekStart = $start->startOfWeek(CarbonImmutable::MONDAY); $weekStart->lte($end); $weekStart = $weekStart->addWeek()) {
            $schoolDayCount = 0;
            $effectiveDays = 0;
            $reasons = [];

            for ($i = 0; $i < $schoolDays; $i++) {
                $day = $weekStart->addDays($i);

                if ($day->lt($start) || $day->gt($end)) {
                    continue;
                }

                $schoolDayCount++;
                $key = $day->toDateString();

                if (isset($blocked[$key])) {
                    $reasons = array_merge($reasons, $blocked[$key]);

                    continue;
                }

                $effectiveDays++;
            }

            // Pekan yang hanya berisi akhir pekan di dalam rentang semester tidak dihitung.
            if ($schoolDayCount === 0) {
                continue;
            }

            // Pekan parsial di awal/akhir semester cukup memenuhi hari yang memang ada.
            $required = min($minEffectiveDays, $schoolDayCount);

            // Pekan diasosiasikan ke bulan tempat hari Kamis jatuh (dijepit ke rentang semester),
            // sesuai standar penomoran pekan ISO-8601 agar tiap pekan terhitung di tepat satu bulan.
            $anchor = $weekStart->addDays(3);
            if ($anchor->lt($start)) {
                $anchor = $start;
            } elseif ($anchor->gt($end)) {
                $anchor = $end;
            }

            $uniqueReasons = array_values(array_unique(array_filter($reasons)));
            $monthKey = $anchor->format('Y-m');
            $monthNum = (int) $anchor->format('m');

            $weeks[] = [
                'week_number' => $weekNumber++,
                'start' => $weekStart->toDateString(),
                'start_date' => $weekStart->toDateString(),
                'end' => $weekStart->addDays(6)->toDateString(),
                'end_date' => $weekStart->addDays(6)->toDateString(),
                'month' => $monthKey,
                'month_name' => self::MONTH_NAMES[$monthNum] ?? 'Bulan',
                'school_days' => $schoolDayCount,
                'effective_days' => $effectiveDays,
                'is_effective' => $effectiveDays > 0 && $effectiveDays >= $required,
                'reasons' => $uniqueReasons,
                'reason' => implode(', ', $uniqueReasons),
            ];
        }

        $months = [];

        foreach ($weeks as $week) {
            $key = $week['month'];

            $monthName = self::MONTH_NAMES[(int) substr($key, 5, 2)] ?? 'Bulan';

            $months[$key] ??= [
                'key' => $key,
                'label' => $monthName,
                'month_name' => $monthName,
                'weeks' => 0,
                'total_weeks' => 0,
                'effective_weeks' => 0,
                'non_effective_weeks' => 0,
                'effective_days' => 0,
                'reasons' => [],
            ];

            $months[$key]['weeks']++;
            $months[$key]['total_weeks']++;
            $months[$key]['effective_days'] += $week['effective_days'];
            $months[$key]['reasons'] = array_merge($months[$key]['reasons'], $week['reasons']);

            if ($week['is_effective']) {
                $months[$key]['effective_weeks']++;
            }
        }

        $months = array_values(array_map(function (array $month): array {
            $month['non_effective_weeks'] = max(0, $month['total_weeks'] - $month['effective_weeks']);
            $uniqueReasons = array_values(array_unique(array_filter($month['reasons'])));
            $month['notes'] = implode(', ', $uniqueReasons);
            $month['reasons'] = $uniqueReasons;

            return $month;
        }, $months));

        $totalWeeks = array_sum(array_column($months, 'total_weeks'));
        $totalEffectiveWeeks = array_sum(array_column($months, 'effective_weeks'));
        $totalNonEffectiveWeeks = max(0, $totalWeeks - $totalEffectiveWeeks);

        return [
            'year_name' => $yearName,
            'semester' => $semester,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'months' => $months,
            'weeks' => $weeks,
            'total_weeks' => $totalWeeks,
            'total_effective_weeks' => $totalEffectiveWeeks,
            'total_non_effective_weeks' => $totalNonEffectiveWeeks,
            'total_effective_days' => array_sum(array_column($months, 'effective_days')),
        ];
    }

    /**
     * Hitung jam pelajaran sesuai formula:
     * - Jam KBM = Minggu Efektif x JP
     * - Jam Ulangan = input
     * - Jam Cadangan = input
     * - Jam Efektif = Jam KBM
     * - Sisa Jam Materi = Jam Efektif - Jam Ulangan - Jam Cadangan
     *
     * @return array{teaching_hours: int, exam_hours: int, reserve_hours: int, effective_hours: int, material_hours: int}
     */
    public function calculateHours(int $effectiveWeeks, int $jpPerWeek, int $examHours = 0, int $reserveHours = 0): array
    {
        $teaching = $effectiveWeeks * $jpPerWeek;

        return [
            'teaching_hours' => $teaching,
            'exam_hours' => $examHours,
            'reserve_hours' => $reserveHours,
            'effective_hours' => $teaching,
            'material_hours' => max(0, $teaching - $examHours - $reserveHours),
        ];
    }

    /**
     * Deteksi otomatis estimasi JP per minggu dari data jadwal KBM (Schedule).
     */
    public function detectJpPerWeek(?int $subjectId, ?int $teacherId = null, ?int $schoolClassId = null): ?int
    {
        if (! $subjectId) {
            return null;
        }

        $query = Schedule::where('subject_id', $subjectId);
        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }
        if ($schoolClassId) {
            $query->where('school_class_id', $schoolClassId);
        }

        $schedules = $query->get();
        if ($schedules->isEmpty()) {
            return null;
        }

        // Hitung total JP per kelas dalam sepekan
        $jpPerClass = $schedules->groupBy('school_class_id')->map(function ($classSchedules) {
            return $classSchedules->sum(function ($s) {
                $start = Carbon::parse($s->start_time);
                $end = Carbon::parse($s->end_time);

                return max(1, (int) round($start->diffInMinutes($end) / 40));
            });
        });

        $avgJp = $jpPerClass->avg();

        return $avgJp ? (int) round($avgJp) : null;
    }

    /**
     * Rentang semester: ambil dari tabel academic_years, jika belum ada gunakan default Juli-Desember / Januari-Juni.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    public function resolveRange(string $yearName, string $semester): array
    {
        $record = AcademicYear::where('name', $yearName)->where('semester', $semester)->first();

        if ($record && $record->start_date && $record->end_date) {
            return [
                CarbonImmutable::parse($record->start_date->toDateString())->startOfDay(),
                CarbonImmutable::parse($record->end_date->toDateString())->startOfDay(),
            ];
        }

        [$startYear, $endYear] = $this->yearsFromName($yearName);

        return $semester === 'ganjil'
            ? [CarbonImmutable::create($startYear, 7, 1)->startOfDay(), CarbonImmutable::create($startYear, 12, 31)->startOfDay()]
            : [CarbonImmutable::create($endYear, 1, 1)->startOfDay(), CarbonImmutable::create($endYear, 6, 30)->startOfDay()];
    }

    /**
     * Peta tanggal => daftar alasan KBM ditiadakan (libur nasional/cuti bersama aktif + kalender libur/ujian).
     *
     * @return array<string, list<string>>
     */
    private function blockedDays(string $yearName, string $semester, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $blocked = [];

        Holiday::active()
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->each(function (Holiday $holiday) use (&$blocked): void {
                $blocked[$holiday->holiday_date->toDateString()][] = $holiday->name;
            });

        AcademicCalendar::forYear($yearName)
            ->semester($semester)
            ->whereIn('category', self::BLOCKING_CATEGORIES)
            ->get()
            ->each(function (AcademicCalendar $event) use (&$blocked, $start, $end): void {
                $from = CarbonImmutable::parse($event->start_date->toDateString());
                $to = CarbonImmutable::parse(($event->end_date ?? $event->start_date)->toDateString());

                if ($from->lt($start)) {
                    $from = $start;
                }

                if ($to->gt($end)) {
                    $to = $end;
                }

                for ($day = $from; $day->lte($to); $day = $day->addDay()) {
                    $blocked[$day->toDateString()][] = $event->description;
                }
            });

        return array_map(fn (array $reasons): array => array_values(array_unique($reasons)), $blocked);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function yearsFromName(string $yearName): array
    {
        if (preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', trim($yearName), $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        $year = now()->year;

        return [$year, $year + 1];
    }
}
