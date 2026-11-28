<?php

namespace App\Models\Cbt;

use App\Models\AcademicYear;
use App\Models\Ujian\UjianJadwal;
use App\Models\Ujian\UjianJenis;
use App\Models\Ujian\UjianPeriode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtSchedule extends Model
{
    use HasFactory;

    protected $table = 'cbt_schedules';

    protected $fillable = [
        'academic_year_id',
        'ujian_periode_id',
        'ujian_jadwal_id',
        'ujian_jenis_id',
        'cbt_question_bank_id',
        'start_date',
        'end_date',
        'duration_minutes',
        'min_finish_minutes',
        'jam_ke',
        'shuffle_questions',
        'shuffle_options',
        'show_result',
        'require_token',
        'reset_login_lock',
        'allow_repeat',
        'is_active',
        'is_recapped',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'duration_minutes' => 'integer',
            'min_finish_minutes' => 'integer',
            'jam_ke' => 'integer',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_result' => 'boolean',
            'require_token' => 'boolean',
            'reset_login_lock' => 'boolean',
            'allow_repeat' => 'boolean',
            'is_active' => 'boolean',
            'is_recapped' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function ujianPeriode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function ujianJadwal(): BelongsTo
    {
        return $this->belongsTo(UjianJadwal::class, 'ujian_jadwal_id');
    }

    public function ujianJenis(): BelongsTo
    {
        return $this->belongsTo(UjianJenis::class, 'ujian_jenis_id');
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionBank::class, 'cbt_question_bank_id');
    }

    public function studentDurations(): HasMany
    {
        return $this->hasMany(CbtStudentDuration::class, 'cbt_schedule_id');
    }

    public function studentQuestions(): HasMany
    {
        return $this->hasMany(CbtStudentQuestion::class, 'cbt_schedule_id');
    }

    public function studentScores(): HasMany
    {
        return $this->hasMany(CbtStudentScore::class, 'cbt_schedule_id');
    }

    public function examLogs(): HasMany
    {
        return $this->hasMany(CbtExamLog::class, 'cbt_schedule_id');
    }

    public function recaps(): HasMany
    {
        return $this->hasMany(CbtRecap::class, 'cbt_schedule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
