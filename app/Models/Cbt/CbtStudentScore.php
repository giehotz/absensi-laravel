<?php

namespace App\Models\Cbt;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtStudentScore extends Model
{
    use HasFactory;

    protected $table = 'cbt_student_scores';

    protected $fillable = [
        'cbt_schedule_id',
        'student_id',
        'pg_correct',
        'pg_score',
        'kompleks_score',
        'jodohkan_score',
        'isian_score',
        'esai_score',
        'total_score',
        'katrol_score',
        'is_corrected',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pg_correct' => 'integer',
            'pg_score' => 'float',
            'kompleks_score' => 'float',
            'jodohkan_score' => 'float',
            'isian_score' => 'float',
            'esai_score' => 'float',
            'total_score' => 'float',
            'katrol_score' => 'float',
            'is_corrected' => 'boolean',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CbtSchedule::class, 'cbt_schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
