<?php

namespace App\Models\Ujian;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UjianSesi extends Model
{
    use HasFactory;

    protected $table = 'ujian_sesi';

    protected $fillable = [
        'sesi_ke',
        'nama_sesi',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sesi_ke' => 'integer',
            'durasi_menit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Format rentang jam pelaksanaan (contoh: 07:30 - 09:30).
     */
    public function getFormattedWaktuAttribute(): string
    {
        $mulai = substr($this->jam_mulai, 0, 5);
        $selesai = substr($this->jam_selesai, 0, 5);

        return "{$mulai} - {$selesai}";
    }
}
