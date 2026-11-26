<?php

namespace App\Models\Supervisi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisiFotoBukti extends Model
{
    protected $table = 'supervisi_foto_bukti';

    protected $fillable = [
        'jadwal_id',
        'file_path',
        'keterangan',
        'uploaded_by',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(SupervisiJadwal::class, 'jadwal_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
    }
}
