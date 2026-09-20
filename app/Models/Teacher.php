<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nuptk',
        'nip',
        'gender',
        'birth_place',
        'birth_date',
        'last_education',
        'phone',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function homeroomClass()
    {
        return $this->hasOne(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_assignments')->distinct();
    }

    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_assignments')->distinct();
    }

    /**
     * Memeriksa apakah guru memiliki pembatasan penugasan mengajar khusus.
     */
    public function hasTeachingRestrictions(): bool
    {
        return $this->assignments()->exists();
    }

    /**
     * Memeriksa apakah guru diizinkan mengajar mata pelajaran dan kelas tertentu.
     * Jika tidak ada pembatasan khusus, mengembalikan true (bebas mengajar apa saja).
     */
    public function canTeach(int $subjectId, int $schoolClassId): bool
    {
        if (! $this->hasTeachingRestrictions()) {
            return true;
        }

        return $this->assignments()
            ->where('subject_id', $subjectId)
            ->where('school_class_id', $schoolClassId)
            ->exists();
    }

    /**
     * Memeriksa apakah guru berstatus sebagai wali kelas.
     */
    public function isHomeroom(): bool
    {
        return $this->homeroomClasses()->exists();
    }
}
