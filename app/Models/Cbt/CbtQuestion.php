<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtQuestion extends Model
{
    use HasFactory;

    public const TYPE_PG = 1;

    public const TYPE_PG_KOMPLEKS = 2;

    public const TYPE_MENJODOHKAN = 3;

    public const TYPE_ISIAN = 4;

    public const TYPE_ESAI = 5;

    protected $table = 'cbt_questions';

    protected $fillable = [
        'cbt_question_bank_id',
        'question_type',
        'question_number',
        'question_html',
        'options',
        'answer_key',
        'attachments',
        'difficulty',
        'timer_enabled',
        'timer_minutes',
        'is_displayed',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'question_type' => 'integer',
            'question_number' => 'integer',
            'options' => 'array',
            'answer_key' => 'array',
            'attachments' => 'array',
            'difficulty' => 'integer',
            'timer_enabled' => 'boolean',
            'timer_minutes' => 'integer',
            'is_displayed' => 'boolean',
        ];
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionBank::class, 'cbt_question_bank_id');
    }

    public function studentQuestions(): HasMany
    {
        return $this->hasMany(CbtStudentQuestion::class, 'cbt_question_id');
    }

    public function scopeDisplayed(Builder $query): Builder
    {
        return $query->where('is_displayed', true);
    }
}
