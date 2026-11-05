<?php

namespace App\Models\Ujian;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UjianJenis extends Model
{
    use HasFactory;

    protected $table = 'ujian_jenis';

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
        'kategori_kurikulum',
        'fase_sasaran',
        'judul_kop_kartu',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function periodes(): HasMany
    {
        return $this->hasMany(UjianPeriode::class, 'ujian_jenis_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }
}
