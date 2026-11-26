<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiPraObservasi extends Model
{
    public const STATUS_DRAFT = 'draft_guru';

    public const STATUS_DIAJUKAN = 'diajukan';

    public const STATUS_DISEPAKATI = 'disepakati';

    protected $table = 'supervisi_pra_observasi';

    protected $fillable = [
        'jadwal_id',
        'fokus_perilaku_dipilih',
        'upaya_mempelajari',
        'tujuan_pembelajaran_sesi',
        'area_pengembangan',
        'strategi_persiapan',
        'catatan_supervisor_pra',
        'status_kesepakatan',
        'submitted_at',
        'disepakati_pada',
    ];

    protected function casts(): array
    {
        return [
            'fokus_perilaku_dipilih' => 'array',
            'submitted_at' => 'datetime',
            'disepakati_pada' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }

    public function isAgreed(): bool
    {
        return $this->status_kesepakatan === self::STATUS_DISEPAKATI;
    }
}
