<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingJournal extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'school_class_id',
        'subject_id',
        'schedule_id',
        'date',
        'day_name',
        'meeting_number',
        'learning_objective',
        'teaching_activity',
        'teaching_problem',
        'total_students',
        'count_hadir',
        'count_sakit',
        'count_izin',
        'count_alpa',
        'count_terlambat',
        'attendance_percentage',
        'is_shared_with_students',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meeting_number' => 'integer',
            'total_students' => 'integer',
            'count_hadir' => 'integer',
            'count_sakit' => 'integer',
            'count_izin' => 'integer',
            'count_alpa' => 'integer',
            'count_terlambat' => 'integer',
            'attendance_percentage' => 'decimal:2',
            'is_shared_with_students' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date ? Carbon::parse($this->date)->translatedFormat('d/m/Y') : '-';
    }

    public function getFormattedDateLongAttribute(): string
    {
        return $this->date ? Carbon::parse($this->date)->translatedFormat('d F Y') : '-';
    }

    public function getDayAndDateAttribute(): string
    {
        return ($this->day_name ?: Carbon::parse($this->date)->translatedFormat('l')).', '.$this->formatted_date_long;
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->where('school_class_id', $classId);
    }

    public function scopeSharedWithStudents(Builder $query): Builder
    {
        return $query->where('is_shared_with_students', true);
    }

    public function scopeDateRange(Builder $query, ?string $startDate, ?string $endDate): Builder
    {
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return $query;
    }
}
