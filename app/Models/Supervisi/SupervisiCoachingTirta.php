<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiCoachingTirta extends Model
{
    public const TAHAP_PRA = 'pra_observasi';

    public const TAHAP_PASCA = 'pasca_observasi';

    protected $table = 'supervisi_coaching_tirta';

    protected $fillable = [
        'jadwal_id',
        'tahap',
        'tujuan_coaching',
        'identifikasi_kondisi',
        'rencana_aksi',
        'tanggung_jawab',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }
}
