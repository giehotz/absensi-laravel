<?php

namespace App\Models\Supervisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiTindakLanjut extends Model
{
    protected $table = 'supervisi_tindak_lanjut';

    protected $fillable = [
        'jadwal_id',
        'refleksi_guru',
        'pilihan_belajar_pmm',
        'pilihan_belajar_lainnya',
        'tujuan_tindak_lanjut',
        'upaya_tindak_lanjut',
        'periode_tindak_lanjut',
        'kebutuhan_dukungan',
        'detail_dukungan',
        'respon_supervisor_c',
        'catatan_supervisor_c',
        'inspirasi_baru',
        'perubahan_praktik',
        'kendala_tindak_lanjut',
        'rencana_perbaikan_berkelanjutan',
        'penilaian_supervisor_d',
        'catatan_supervisor_d',
        'status_tindak_lanjut',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }
}
