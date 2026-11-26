<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisiAspekPenilaian extends Model
{
    public const SKALA_REGULER = 'skor_1_4';

    public const SKALA_PMM = 'perilaku_pmm_1_3';

    protected $table = 'supervisi_aspek_penilaian';

    protected $fillable = [
        'jenis_penilaian_id',
        'nama_aspek',
        'fokus_perilaku',
        'perilaku_dianjurkan',
        'perilaku_dihindari',
        'tipe_skala',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function jenisPenilaian(): BelongsTo
    {
        return $this->belongsTo(SupervisiJenisPenilaian::class, 'jenis_penilaian_id');
    }

    public function detailHasils(): HasMany
    {
        return $this->hasMany(SupervisiDetailHasil::class, 'aspek_id');
    }

    public function maxSkor(): int
    {
        return $this->tipe_skala === self::SKALA_PMM ? 3 : 4;
    }
}
