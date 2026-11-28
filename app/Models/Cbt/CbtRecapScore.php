<?php

namespace App\Models\Cbt;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtRecapScore extends Model
{
    use HasFactory;

    protected $table = 'cbt_recap_scores';

    protected $fillable = [
        'cbt_recap_id',
        'student_id',
        'school_class_name',
        'student_name',
        'participant_number',
        'pg_correct',
        'pg_score',
        'kompleks_score',
        'jodohkan_score',
        'isian_score',
        'esai_score',
        'total_score',
        'katrol_score',
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
        ];
    }

    public function recap(): BelongsTo
    {
        return $this->belongsTo(CbtRecap::class, 'cbt_recap_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
