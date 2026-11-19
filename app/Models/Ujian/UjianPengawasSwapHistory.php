<?php

namespace App\Models\Ujian;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjianPengawasSwapHistory extends Model
{
    use HasFactory;

    protected $table = 'ujian_pengawas_swap_histories';

    protected $fillable = [
        'ujian_periode_id',
        'source_pengawas_id',
        'target_pengawas_id',
        'teacher_1_id',
        'teacher_2_id',
        'jadwal_1_id',
        'jadwal_2_id',
        'slot_position_1',
        'slot_position_2',
        'alasan',
        'processed_by_user_id',
        'status',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function sourcePengawas(): BelongsTo
    {
        return $this->belongsTo(UjianJadwalPengawas::class, 'source_pengawas_id');
    }

    public function targetPengawas(): BelongsTo
    {
        return $this->belongsTo(UjianJadwalPengawas::class, 'target_pengawas_id');
    }

    public function teacher1(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_1_id');
    }

    public function teacher2(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_2_id');
    }

    public function jadwal1(): BelongsTo
    {
        return $this->belongsTo(UjianJadwal::class, 'jadwal_1_id');
    }

    public function jadwal2(): BelongsTo
    {
        return $this->belongsTo(UjianJadwal::class, 'jadwal_2_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
