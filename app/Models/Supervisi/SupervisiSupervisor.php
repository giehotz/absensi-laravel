<?php

namespace App\Models\Supervisi;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupervisiSupervisor extends Model
{
    protected $table = 'supervisi_supervisors';

    protected $fillable = [
        'teacher_id',
        'jabatan',
        'tanggal_penugasan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penugasan' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(SupervisiJadwal::class, 'supervisor_teacher_id', 'teacher_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
