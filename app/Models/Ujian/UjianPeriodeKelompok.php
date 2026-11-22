<?php

namespace App\Models\Ujian;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianPeriodeKelompok extends Model
{
    use HasFactory;

    protected $table = 'ujian_periode_kelompok';

    protected $fillable = [
        'ujian_periode_id',
        'kelompok',
        'tingkat_list',
        'mode_jadwal',
        'tanggal_mulai',
        'tanggal_selesai',
        'kecualikan_minggu',
        'tanggal_libur',
        'jumlah_sesi_per_hari',
        'sesi_hari_terakhir',
        'jam_sesi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'kecualikan_minggu' => 'boolean',
            'tingkat_list' => 'array',
            'tanggal_libur' => 'array',
            'jam_sesi' => 'array',
            'jumlah_sesi_per_hari' => 'integer',
            'sesi_hari_terakhir' => 'integer',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function getKelompokLabelAttribute(): string
    {
        return $this->kelompok === 'bawah' ? 'Kelas Bawah (1–2)' : 'Kelas Atas (3–6)';
    }

    public function getModeLabelAttribute(): string
    {
        return $this->mode_jadwal === 'per_rombel' ? 'Per Rombel' : 'Seragam Tingkat';
    }
}
