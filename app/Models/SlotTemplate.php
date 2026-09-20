<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotTemplate extends Model
{
    use HasFactory;

    public const K_KBM = 0;

    public const K_UPACARA = 3;

    public const K_ISTIRAHAT = 4;

    public const K_SENAM = 5;

    public const K_PEMBIASAAN = 6;

    public const K_RELIGI = 7;

    public const KEGIATAN_LABELS = [
        self::K_KBM => 'Kegiatan Belajar Mengajar (KBM)',
        self::K_UPACARA => 'Upacara Bendera',
        self::K_ISTIRAHAT => 'Istirahat',
        self::K_SENAM => 'Senam Pagi',
        self::K_PEMBIASAAN => 'Pembiasaan / Literasi',
        self::K_RELIGI => 'Religi / Shalat Berjamaah',
    ];

    public const KEGIATAN_COLORS = [
        self::K_KBM => [
            'bg' => 'bg-white',
            'border' => 'border-black',
            'text' => 'text-black',
            'badge_bg' => 'bg-[#FFF9DB]',
            'badge_text' => 'text-amber-950',
            'name' => 'KBM',
        ],
        self::K_UPACARA => [
            'bg' => 'bg-[#FFE3E3]',
            'border' => 'border-black',
            'text' => 'text-rose-950',
            'badge_bg' => 'bg-[#FF8787]',
            'badge_text' => 'text-white',
            'name' => 'Upacara',
        ],
        self::K_ISTIRAHAT => [
            'bg' => 'bg-[#D0EBFF]',
            'border' => 'border-black',
            'text' => 'text-blue-950',
            'badge_bg' => 'bg-[#74C0FC]',
            'badge_text' => 'text-blue-950',
            'name' => 'Istirahat',
        ],
        self::K_SENAM => [
            'bg' => 'bg-[#FFE8CC]',
            'border' => 'border-black',
            'text' => 'text-amber-950',
            'badge_bg' => 'bg-[#FFA94D]',
            'badge_text' => 'text-black',
            'name' => 'Senam',
        ],
        self::K_PEMBIASAAN => [
            'bg' => 'bg-[#C3FAE8]',
            'border' => 'border-black',
            'text' => 'text-teal-950',
            'badge_bg' => 'bg-[#63E6BE]',
            'badge_text' => 'text-teal-950',
            'name' => 'Pembiasaan',
        ],
        self::K_RELIGI => [
            'bg' => 'bg-[#EEBEFA]',
            'border' => 'border-black',
            'text' => 'text-purple-950',
            'badge_bg' => 'bg-[#DA77F2]',
            'badge_text' => 'text-white',
            'name' => 'Religi',
        ],
    ];

    protected $fillable = [
        'academic_year_id',
        'day_of_week',
        'jam_ke',
        'k_jadwal',
        'name',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'day_of_week' => 'integer',
        'jam_ke' => 'integer',
        'k_jadwal' => 'integer',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function isKbm(): bool
    {
        return $this->k_jadwal === self::K_KBM;
    }

    public function isNonKbm(): bool
    {
        return ! $this->isKbm();
    }

    public function getCategoryLabel(): string
    {
        return self::KEGIATAN_LABELS[$this->k_jadwal] ?? 'Lainnya';
    }

    public function getShortStartTime(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function getShortEndTime(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    /**
     * Template standar bawaan Madrasah / Kemenag (Senin s.d Sabtu, 40 menit per jam tatap muka).
     *
     * @return array<int, array<int, array<string, mixed>>>
     */
    public static function getDefaultMadrasahSlots(): array
    {
        $defaultSchedule = [];

        for ($day = 1; $day <= 6; $day++) {
            $slots = [];

            if ($day === 1) {
                // SENIN: Ada Upacara di jam pertama
                $slots[] = ['jam_ke' => 1, 'k_jadwal' => self::K_UPACARA, 'name' => 'Upacara Bendera', 'start' => '07:00', 'end' => '07:45'];
                $slots[] = ['jam_ke' => 2, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-1', 'start' => '07:45', 'end' => '08:25'];
                $slots[] = ['jam_ke' => 3, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-2', 'start' => '08:25', 'end' => '09:05'];
                $slots[] = ['jam_ke' => 4, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-3', 'start' => '09:05', 'end' => '09:45'];
                $slots[] = ['jam_ke' => 5, 'k_jadwal' => self::K_ISTIRAHAT, 'name' => 'Istirahat 1', 'start' => '09:45', 'end' => '10:15'];
                $slots[] = ['jam_ke' => 6, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-4', 'start' => '10:15', 'end' => '10:55'];
                $slots[] = ['jam_ke' => 7, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-5', 'start' => '10:55', 'end' => '11:35'];
                $slots[] = ['jam_ke' => 8, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-6', 'start' => '11:35', 'end' => '12:15'];
                $slots[] = ['jam_ke' => 9, 'k_jadwal' => self::K_RELIGI, 'name' => 'Shalat Dzuhur', 'start' => '12:15', 'end' => '13:00'];
                $slots[] = ['jam_ke' => 10, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-7', 'start' => '13:00', 'end' => '13:40'];
            } elseif ($day === 5) {
                // JUMAT: Pembiasaan/Yasinan/Religi pagi, pulang sebelum Shalat Jumat
                $slots[] = ['jam_ke' => 1, 'k_jadwal' => self::K_RELIGI, 'name' => 'Pembiasaan Religi / Dhuha', 'start' => '07:00', 'end' => '07:45'];
                $slots[] = ['jam_ke' => 2, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-1', 'start' => '07:45', 'end' => '08:25'];
                $slots[] = ['jam_ke' => 3, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-2', 'start' => '08:25', 'end' => '09:05'];
                $slots[] = ['jam_ke' => 4, 'k_jadwal' => self::K_ISTIRAHAT, 'name' => 'Istirahat', 'start' => '09:05', 'end' => '09:30'];
                $slots[] = ['jam_ke' => 5, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-3', 'start' => '09:30', 'end' => '10:10'];
                $slots[] = ['jam_ke' => 6, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-4', 'start' => '10:10', 'end' => '10:50'];
                $slots[] = ['jam_ke' => 7, 'k_jadwal' => self::K_RELIGI, 'name' => 'Persiapan Shalat Jumat', 'start' => '10:50', 'end' => '11:20'];
            } elseif ($day === 6) {
                // SABTU: Senam Pagi / Pembiasaan Ekstrakurikuler
                $slots[] = ['jam_ke' => 1, 'k_jadwal' => self::K_SENAM, 'name' => 'Senam Pagi & Pembiasaan', 'start' => '07:00', 'end' => '07:45'];
                $slots[] = ['jam_ke' => 2, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-1', 'start' => '07:45', 'end' => '08:25'];
                $slots[] = ['jam_ke' => 3, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-2', 'start' => '08:25', 'end' => '09:05'];
                $slots[] = ['jam_ke' => 4, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-3', 'start' => '09:05', 'end' => '09:45'];
                $slots[] = ['jam_ke' => 5, 'k_jadwal' => self::K_ISTIRAHAT, 'name' => 'Istirahat', 'start' => '09:45', 'end' => '10:15'];
                $slots[] = ['jam_ke' => 6, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-4', 'start' => '10:15', 'end' => '10:55'];
                $slots[] = ['jam_ke' => 7, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-5', 'start' => '10:55', 'end' => '11:35'];
                $slots[] = ['jam_ke' => 8, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-6', 'start' => '11:35', 'end' => '12:15'];
            } else {
                // SELASA, RABU, KAMIS: Regular KBM
                $slots[] = ['jam_ke' => 1, 'k_jadwal' => self::K_PEMBIASAAN, 'name' => 'Pembiasaan Pagi / Literasi', 'start' => '07:00', 'end' => '07:30'];
                $slots[] = ['jam_ke' => 2, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-1', 'start' => '07:30', 'end' => '08:10'];
                $slots[] = ['jam_ke' => 3, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-2', 'start' => '08:10', 'end' => '08:50'];
                $slots[] = ['jam_ke' => 4, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-3', 'start' => '08:50', 'end' => '09:30'];
                $slots[] = ['jam_ke' => 5, 'k_jadwal' => self::K_ISTIRAHAT, 'name' => 'Istirahat 1', 'start' => '09:30', 'end' => '10:00'];
                $slots[] = ['jam_ke' => 6, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-4', 'start' => '10:00', 'end' => '10:40'];
                $slots[] = ['jam_ke' => 7, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-5', 'start' => '10:40', 'end' => '11:20'];
                $slots[] = ['jam_ke' => 8, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-6', 'start' => '11:20', 'end' => '12:00'];
                $slots[] = ['jam_ke' => 9, 'k_jadwal' => self::K_RELIGI, 'name' => 'Shalat Dzuhur', 'start' => '12:00', 'end' => '12:45'];
                $slots[] = ['jam_ke' => 10, 'k_jadwal' => self::K_KBM, 'name' => 'Jam ke-7', 'start' => '12:45', 'end' => '13:25'];
            }

            $defaultSchedule[$day] = $slots;
        }

        return $defaultSchedule;
    }
}
