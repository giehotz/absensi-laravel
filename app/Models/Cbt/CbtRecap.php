<?php

namespace App\Models\Cbt;

use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Ujian\UjianJenis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtRecap extends Model
{
    use HasFactory;

    protected $table = 'cbt_recaps';

    protected $fillable = [
        'cbt_schedule_id',
        'academic_year_id',
        'ujian_jenis_id',
        'subject_id',
        'teacher_id',
        'bank_code',
        'exam_title',
        'exam_date',
        'bank_config_snapshot',
        'classes_snapshot',
        'questions_snapshot',
        'recapped_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'bank_config_snapshot' => 'array',
            'classes_snapshot' => 'array',
            'questions_snapshot' => 'array',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CbtSchedule::class, 'cbt_schedule_id');
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

    public function recappedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recapped_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CbtRecapScore::class, 'cbt_recap_id');
    }
}
