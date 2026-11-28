<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiCoachingTirta extends Model
{
    public const TAHAP_PRA = 'pra_observasi';

    public const TAHAP_PASCA = 'pasca_observasi';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_CONFIRMED = 'confirmed';

    protected $table = 'supervisi_coaching_tirta';

    protected $fillable = [
        'jadwal_id',
        'tahap',
        'tujuan_coaching',
        'identifikasi_kondisi',
        'rencana_aksi',
        'tanggung_jawab',
        'catatan_coach',
        'catatan_guru',
        'status',
        'submitted_at',
        'guru_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'guru_confirmed_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }
}
