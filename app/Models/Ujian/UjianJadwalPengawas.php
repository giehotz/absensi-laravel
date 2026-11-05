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
        'status_kehadiran',
        'pengawas_pengganti',
        'catatan',
    ];

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
}
