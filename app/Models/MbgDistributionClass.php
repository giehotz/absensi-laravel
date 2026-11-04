<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbgDistributionClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'mbg_distribution_id',
        'school_class_id',
        'target_portions',
        'distributed_portions',
        'leftover_portions',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_portions' => 'integer',
            'distributed_portions' => 'integer',
            'leftover_portions' => 'integer',
        ];
    }

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(MbgDistribution::class, 'mbg_distribution_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }
}
