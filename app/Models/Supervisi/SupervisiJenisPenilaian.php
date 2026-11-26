<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisiJenisPenilaian extends Model
{
    public const KATEGORI_REGULER = 'reguler';

    public const KATEGORI_OBSERVASI_PMM = 'observasi_kelas_pmm';

    public const KATEGORI_PERANGKAT_AJAR = 'perangkat_ajar';

    protected $table = 'supervisi_jenis_penilaian';

    protected $fillable = [
        'nama',
        'kode',
        'kategori_supervisi',
        'dimensi_pmm',
        'skor_maksimal',
        'kategori_skor',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'skor_maksimal' => 'integer',
            'kategori_skor' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function aspekPenilaians(): HasMany
    {
        return $this->hasMany(SupervisiAspekPenilaian::class, 'jenis_penilaian_id')->orderBy('urutan');
    }

    public function hasilSupervisis(): HasMany
    {
        return $this->hasMany(SupervisiHasil::class, 'jenis_penilaian_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori_supervisi', $kategori);
    }

    public function isPmm(): bool
    {
        return $this->kategori_supervisi === self::KATEGORI_OBSERVASI_PMM;
    }
}
