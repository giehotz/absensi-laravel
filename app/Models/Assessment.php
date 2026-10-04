<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_package_id',
        'sheet_number',
        'sheet_name',
        'materi',
        'kktp',
        'max_score',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sheet_number' => 'integer',
            'kktp' => 'integer',
            'max_score' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(AssessmentPackage::class, 'assessment_package_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }
}
