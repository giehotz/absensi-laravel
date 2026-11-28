<?php

namespace App\Models\Cbt;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtStudentQuestion extends Model
{
    use HasFactory;

    protected $table = 'cbt_student_questions';

    protected $fillable = [
        'cbt_schedule_id',
        'student_id',
        'cbt_question_bank_id',
        'cbt_question_id',
        'question_type',
        'original_number',
        'alias_number',
        'option_aliases',
        'answer_alias',
        'answer_key',
        'student_answer',
        'max_point',
        'corrected_score',
        'is_manual_graded',
        'is_doubtful',
        'is_finished_flag',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'question_type' => 'integer',
            'original_number' => 'integer',
            'alias_number' => 'integer',
            'option_aliases' => 'array',
            'answer_key' => 'array',
            'student_answer' => 'array',
            'max_point' => 'float',
            'corrected_score' => 'float',
            'is_manual_graded' => 'boolean',
            'is_doubtful' => 'boolean',
            'is_finished_flag' => 'boolean',
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

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionBank::class, 'cbt_question_bank_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }
}
