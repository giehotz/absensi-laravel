<?php

namespace App\Models\Ujian;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianPengawasMapping extends Model
{
    use HasFactory;

    protected $table = 'ujian_pengawas_mappings';

    protected $fillable = [
        'ujian_periode_id',
        'teacher_id',
        'assigned_levels',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'assigned_levels' => 'array',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Memeriksa apakah guru dipetakan pada tingkat kelas tertentu.
     */
    public function isAssignedToLevel(int|string $level): bool
    {
        $levels = array_map('strval', $this->assigned_levels ?? []);

        return in_array((string) $level, $levels, true);
    }

    /**
     * Memeriksa apakah pemetaan mencakup seluruh kelas rendah (1, 2, 3).
     */
    public function isKelasRendah(): bool
    {
        $levels = array_map('strval', $this->assigned_levels ?? []);

        return in_array('1', $levels, true) && in_array('2', $levels, true) && in_array('3', $levels, true);
    }

    /**
     * Memeriksa apakah pemetaan mencakup seluruh kelas atas (4, 5, 6).
     */
    public function isKelasAtas(): bool
    {
        $levels = array_map('strval', $this->assigned_levels ?? []);

        return in_array('4', $levels, true) && in_array('5', $levels, true) && in_array('6', $levels, true);
    }

    /**
     * Label representasi kelompok tingkat kelas yang dipetakan.
     */
    public function getFormattedLevelsAttribute(): string
    {
        $levels = array_map('strval', $this->assigned_levels ?? []);
        sort($levels, SORT_NUMERIC);

        if (empty($levels)) {
            return 'Belum Dipetakan';
        }

        if (count($levels) === 6) {
            return 'Semua Kelas (1–6)';
        }

        if ($levels === ['1', '2', '3']) {
            return 'Kelas Rendah (1, 2, 3)';
        }

        if ($levels === ['4', '5', '6']) {
            return 'Kelas Atas (4, 5, 6)';
        }

        return 'Kelas '.implode(', ', $levels);
    }
}
