<?php

namespace App\Models\Cbt;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtExamLog extends Model
{
    use HasFactory;

    public const LOG_TYPE_START = 1;

    public const LOG_TYPE_FINISH = 2;

    protected $table = 'cbt_exam_logs';

    protected $fillable = [
        'cbt_schedule_id',
        'student_id',
        'log_type',
        'ip_address',
        'user_agent',
        'device',
        'is_reset',
        'violation_reason',
        'logged_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'log_type' => 'integer',
            'is_reset' => 'boolean',
            'logged_at' => 'datetime',
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
