<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbgDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'menu_name',
        'target_students',
        'distributed_students',
        'target_teachers',
        'distributed_teachers',
        'leftover_portions',
        'status',
        'notes',
        'documentation_image',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'target_students' => 'integer',
            'distributed_students' => 'integer',
            'target_teachers' => 'integer',
            'distributed_teachers' => 'integer',
            'leftover_portions' => 'integer',
        ];
    }

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function recordedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function classDistributions(): HasMany
    {
        return $this->hasMany(MbgDistributionClass::class);
    }

    public function getTotalTargetAttribute(): int
    {
        return $this->target_students + $this->target_teachers;
    }

    public function getTotalDistributedAttribute(): int
    {
        return $this->distributed_students + $this->distributed_teachers;
    }

    public function getSuccessRateAttribute(): float
    {
        $target = $this->total_target;

        if ($target <= 0) {
            return 0.0;
        }

        return round(($this->total_distributed / $target) * 100, 1);
    }
}
