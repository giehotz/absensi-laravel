<?php

namespace App\Models\Ujian;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjianRuang extends Model
{
    use HasFactory;

    protected $table = 'ujian_ruang';

    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
        'kapasitas_maksimal',
        'tipe_ruang',
        'school_class_id',
        'lokasi_gedung',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas_maksimal' => 'integer',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'ujian_ruang_id');
    }

    public function jadwalPengawas(): HasMany
    {
        return $this->hasMany(UjianJadwalPengawas::class, 'ujian_ruang_id');
    }

    public function isKelas(): bool
    {
        return $this->tipe_ruang === 'kelas';
    }

    public function isManual(): bool
    {
        return $this->tipe_ruang === 'manual';
    }

    public function scopeKelas(Builder $query): Builder
    {
        return $query->where('tipe_ruang', 'kelas');
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('tipe_ruang', 'manual');
    }
}
