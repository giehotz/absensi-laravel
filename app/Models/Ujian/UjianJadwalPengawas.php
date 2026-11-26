<?php

namespace App\Models\Ujian;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianJadwalPengawas extends Model
{
    use HasFactory;

    protected $table = 'ujian_jadwal_pengawas';

    protected $fillable = [
        'ujian_jadwal_id',
        'ujian_ruang_id',
        'pengawas_1_id',
        'pengawas_2_id',
        'assignment_source',
        'is_locked',
        'status_p1',
        'status_p2',
        'check_in_p1_at',
        'check_in_p2_at',
        'status_kehadiran',
        'pengawas_pengganti',
        'pengawas_pengganti_1',
        'pengawas_pengganti_2',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'check_in_p1_at' => 'datetime',
            'check_in_p2_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function getAssignmentSourceLabelAttribute(): string
    {
        return match ($this->assignment_source) {
            'subject_teacher' => 'Pengampu',
            'swapped' => 'Ditukar',
            'manual' => 'Manual',
            default => 'Rotasi',
        };
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(UjianJadwal::class, 'ujian_jadwal_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(UjianRuang::class, 'ujian_ruang_id');
    }

    public function pengawas1(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'pengawas_1_id');
    }

    public function pengawas2(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'pengawas_2_id');
    }

    public function getPengawas1NameAttribute(): string
    {
        return $this->pengawas1?->user?->name ?? '-';
    }

    public function getPengawas2NameAttribute(): ?string
    {
        return $this->pengawas2?->user?->name;
    }

    public function beritaAcara()
    {
        return $this->hasOne(UjianBeritaAcara::class, 'ujian_jadwal_id', 'ujian_jadwal_id')
            ->where('ujian_ruang_id', $this->ujian_ruang_id);
    }

    public function syncLegacyStatusKehadiran(): void
    {
        if ($this->status_p1 === 'Hadir' || $this->status_p2 === 'Hadir') {
            $this->status_kehadiran = 'Hadir';
        } elseif ($this->status_p1 === 'Digantikan' || $this->status_p2 === 'Digantikan') {
            $this->status_kehadiran = 'Digantikan';
        } elseif ($this->status_p1 === 'Izin' || $this->status_p2 === 'Izin') {
            $this->status_kehadiran = 'Izin';
        } elseif ($this->status_p1 === 'Sakit' || $this->status_p2 === 'Sakit') {
            $this->status_kehadiran = 'Izin';
        } elseif ($this->status_p1 === 'Alpa' || $this->status_p2 === 'Alpa') {
            $this->status_kehadiran = 'Alpa';
        } else {
            $this->status_kehadiran = $this->status_p1 ?: 'Terjadwal';
        }
    }
}
