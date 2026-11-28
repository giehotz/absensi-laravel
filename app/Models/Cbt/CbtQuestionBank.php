<?php

namespace App\Models\Cbt;

use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Ujian\UjianJenis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtQuestionBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cbt_question_banks';

    protected $fillable = [
        'academic_year_id',
        'ujian_jenis_id',
        'subject_id',
        'teacher_id',
        'bank_code',
        'level',
        'target_class_ids',
        'option_count',
        'religion_filter',
        'pg_total',
        'pg_display',
        'pg_weight',
        'kompleks_total',
        'kompleks_display',
        'kompleks_weight',
        'jodohkan_total',
        'jodohkan_display',
        'jodohkan_weight',
        'isian_total',
        'isian_display',
        'isian_weight',
        'esai_total',
        'esai_display',
        'esai_weight',
        'kkm',
        'is_active',
        'is_questions_ready',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_class_ids' => 'array',
            'option_count' => 'integer',
            'pg_total' => 'integer',
            'pg_display' => 'integer',
            'pg_weight' => 'float',
            'kompleks_total' => 'integer',
            'kompleks_display' => 'integer',
            'kompleks_weight' => 'float',
            'jodohkan_total' => 'integer',
            'jodohkan_display' => 'integer',
            'jodohkan_weight' => 'float',
            'isian_total' => 'integer',
            'isian_display' => 'integer',
            'isian_weight' => 'float',
            'esai_total' => 'integer',
            'esai_display' => 'integer',
            'esai_weight' => 'float',
            'kkm' => 'float',
            'is_active' => 'boolean',
            'is_questions_ready' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function ujianJenis(): BelongsTo
    {
        return $this->belongsTo(UjianJenis::class, 'ujian_jenis_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class, 'cbt_question_bank_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(CbtSchedule::class, 'cbt_question_bank_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }
}
