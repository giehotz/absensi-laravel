<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiDetailHasil extends Model
{
    protected $table = 'supervisi_detail_hasil';

    protected $fillable = [
        'hasil_id',
        'aspek_id',
        'skor',
        'catatan',
        'rekomendasi',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
        ];
    }

    public function hasil(): BelongsTo
    {
        return $this->belongsTo(SupervisiHasil::class, 'hasil_id');
    }

    public function aspek(): BelongsTo
    {
        return $this->belongsTo(SupervisiAspekPenilaian::class, 'aspek_id');
    }
}
