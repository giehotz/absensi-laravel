<?php

namespace App\Models;

use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school_class_id',
        'nis',
        'nisn',
        'qr_code_identifier',
        'gender',
        'birth_place',
        'birth_date',
        'photo',
        'phone',
        'address',
        'religion',
        'family_status',
        'child_number',
        'previous_school',
        'admission_date',
        'entry_grade',
        'father_name',
        'mother_name',
        'father_job',
        'mother_job',
        'parent_address',
        'guardian_name',
        'guardian_job',
        'guardian_address',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function getQrDataUriAttribute(): string
    {
        return app(QrCodeService::class)->generateDataUri($this->qr_code_identifier, 180, 2);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(Parents::class, 'student_parent', 'student_id', 'parent_id');
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function studentNotes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function savingsAccount(): HasOne
    {
        return $this->hasOne(SavingsAccount::class);
    }

    public function assessmentScores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    /**
     * Scope query siswa berdasarkan nama tahun ajaran induk rombelnya (contoh: '2026/2027').
     */
    public function scopeForAcademicYearName(Builder $query, ?string $yearName): Builder
    {
        if (blank($yearName)) {
            return $query;
        }

        return $query->whereHas('schoolClass', function (Builder $q) use ($yearName) {
            $q->forAcademicYearName($yearName);
        });
    }

    /**
     * Scope query siswa berdasarkan tahun ajaran induk yang sedang aktif.
     */
    public function scopeCurrentAcademicYear(Builder $query): Builder
    {
        return $query->whereHas('schoolClass', function (Builder $q) {
            $q->currentAcademicYear();
        });
    }
}
