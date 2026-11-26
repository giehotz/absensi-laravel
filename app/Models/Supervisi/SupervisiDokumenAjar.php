<?php

namespace App\Models\Supervisi;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiDokumenAjar extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'supervisi_dokumen_ajar';

    protected $fillable = [
        'jadwal_id',
        'teacher_id',
        'nama_dokumen',
        'kategori_dokumen',
        'fase',
        'tujuan_pembelajaran',
        'strategi_diferensiasi',
        'link_drive',
        'link_eksternal',
        'file_path',
        'keterangan',
        'status',
        'feedback',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
