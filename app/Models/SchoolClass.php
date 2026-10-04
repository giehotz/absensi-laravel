<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'name',
        'level',
        'homeroom_teacher_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class)->whereHas('user', function ($q) {
            $q->where('role', 'siswa');
        });
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function assessmentPackages(): HasMany
    {
        return $this->hasMany(AssessmentPackage::class);
    }

    /**
     * Scope query untuk rombel berdasarkan nama tahun ajaran induk (contoh: '2026/2027').
     */
    public function scopeForAcademicYearName(Builder $query, ?string $yearName): Builder
    {
        if (empty($yearName)) {
            return $query;
        }

        return $query->whereHas('academicYear', function ($q) use ($yearName) {
            $q->where('name', $yearName);
        });
    }

    /**
     * Scope query untuk rombel pada tahun ajaran aktif saat ini.
     */
    public function scopeCurrentAcademicYear(Builder $query): Builder
    {
        $activeYearName = AcademicYear::activeYearName();

        return $this->scopeForAcademicYearName($query, $activeYearName);
    }
}
