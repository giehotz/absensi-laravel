<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisiHasil extends Model
{
    protected $table = 'supervisi_hasil';

    protected $fillable = [
        'jadwal_id',
        'jenis_penilaian_id',
        'total_skor',
        'skor_maksimal',
        'nilai_akhir',
        'ketercapaian',
        'rekomendasi',
    ];

    protected function casts(): array
    {
        return [
            'total_skor' => 'integer',
            'skor_maksimal' => 'integer',
            'nilai_akhir' => 'decimal:2',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }

    public function jenisPenilaian(): BelongsTo
    {
        return $this->belongsTo(SupervisiJenisPenilaian::class, 'jenis_penilaian_id');
    }

    public function detailHasils(): HasMany
    {
        return $this->hasMany(SupervisiDetailHasil::class, 'hasil_id');
    }
}
