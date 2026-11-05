<?php

namespace App\Models\Ujian;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UjianPeriode extends Model
{
    use HasFactory;

    protected $table = 'ujian_periode';

    protected $fillable = [
        'ujian_jenis_id',
        'academic_year_id',
        'nama_periode',
        'tahun_pelajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'tempat_cetak',
        'tanggal_cetak_kartu',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_cetak_kartu' => 'date',
        ];
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(UjianJenis::class, 'ujian_jenis_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function panitias(): HasMany
    {
        return $this->hasMany(UjianPanitia::class, 'ujian_periode_id');
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(UjianJadwal::class, 'ujian_periode_id');
    }

    public function pesertas(): HasMany
    {
        return $this->hasMany(UjianPeserta::class, 'ujian_periode_id');
    }

    public function pengaturanTtd(): HasOne
    {
        return $this->hasOne(UjianPengaturanTtd::class, 'ujian_periode_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
