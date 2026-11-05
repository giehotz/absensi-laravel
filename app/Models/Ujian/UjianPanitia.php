<?php

namespace App\Models\Ujian;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianPanitia extends Model
{
    use HasFactory;

    protected $table = 'ujian_panitia';

    protected $fillable = [
        'ujian_periode_id',
        'teacher_id',
        'jabatan',
        'sk_nomor',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function getJabatanLabelAttribute(): string
    {
        return match ($this->jabatan) {
            'ketua' => 'Ketua Pelaksana',
            'sekretaris' => 'Sekretaris',
            'bendahara' => 'Bendahara',
            'anggota' => 'Anggota Panitia',
            default => ucfirst($this->jabatan),
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
