<?php

namespace App\Models\Cbt;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtStudentDuration extends Model
{
    use HasFactory;

    public const STATUS_NOT_STARTED = 0;

    public const STATUS_IN_PROGRESS = 1;

    public const STATUS_FINISHED = 2;

    public const RESET_NONE = 0;

    public const RESET_FROM_ZERO = 1;

    public const RESET_CONTINUE_REMAINING = 2;

    public const RESET_REPEAT_ALL = 3;

    protected $table = 'cbt_student_durations';

    protected $fillable = [
        'cbt_schedule_id',
        'student_id',
        'status',
        'elapsed_time',
        'elapsed_seconds',
        'started_at',
        'finished_at',
        'reset_mode',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'elapsed_seconds' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'reset_mode' => 'integer',
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
