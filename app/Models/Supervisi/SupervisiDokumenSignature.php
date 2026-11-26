<?php

namespace App\Models\Supervisi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiDokumenSignature extends Model
{
    public const ROLE_GURU = 'guru';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_KEPALA = 'kepala';

    protected $table = 'supervisi_dokumen_signatures';

    protected $fillable = [
        'jadwal_id',
        'dokumen_type',
        'signer_role',
        'user_id',
        'signer_nama',
        'signer_nip',
        'token_verifikasi',
        'tanggal_ttd',
        'signed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ttd' => 'date',
            'signed_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
