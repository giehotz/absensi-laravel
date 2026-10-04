<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'score',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isTuntas(): bool
    {
        return $this->status === 'tuntas';
    }

    public function isRemedial(): bool
    {
        return $this->status === 'remedial';
    }
}
