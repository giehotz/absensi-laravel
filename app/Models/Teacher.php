<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
        'is_savings_officer',
        'savings_scope',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_savings_officer' => 'boolean',
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

    /**
     * Memeriksa apakah guru adalah wali kelas untuk kelas tertentu.
     */
    public function isHomeroomFor(int|SchoolClass $schoolClass): bool
    {
        $classId = $schoolClass instanceof SchoolClass ? $schoolClass->id : (int) $schoolClass;

        return $this->homeroomClasses()->where('id', $classId)->exists();
    }

    /**
     * Ambil seluruh ID kelas yang berhak diakses oleh guru ini
     * (Gabungan Kelas Binaan/Wali Kelas + Jadwal KBM Terjadwal + Penugasan Mengajar Resmi).
     */
    public function getAccessibleClassIds(): Collection
    {
        $homeroomIds = $this->homeroomClasses()->pluck('id');
        $scheduledIds = $this->schedules()->pluck('school_class_id');
        $assignedIds = $this->assignedClasses()->pluck('school_classes.id');

        return $homeroomIds
            ->merge($scheduledIds)
            ->merge($assignedIds)
            ->unique()
            ->values();
    }

    /**
     * Ambil koleksi SchoolClass yang berhak diakses guru.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SchoolClass>
     */
    public function getAccessibleClasses(?int $academicYearId = null): \Illuminate\Database\Eloquent\Collection
    {
        $classIds = $this->getAccessibleClassIds();

        return SchoolClass::with(['academicYear', 'homeroomTeacher.user'])
            ->whereIn('id', $classIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('level')
            ->orderBy('name')
            ->get();
    }

    /**
     * Cek apakah guru berhak mengakses data kelas tertentu.
     */
    public function canAccessClass(int|SchoolClass $schoolClass): bool
    {
        $classId = $schoolClass instanceof SchoolClass ? $schoolClass->id : (int) $schoolClass;

        return $this->getAccessibleClassIds()->contains($classId);
    }

    public function managedSavingsClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_savings_classes');
    }

    public function managesAllSavingsClasses(): bool
    {
        return (bool) $this->is_savings_officer && ($this->savings_scope === 'all' || ! $this->managedSavingsClasses()->exists());
    }

    public function getAllowedSavingsClassIds(): array
    {
        if (! $this->is_savings_officer) {
            return [];
        }

        if ($this->savings_scope === 'all') {
            return SchoolClass::pluck('id')->all();
        }

        return $this->managedSavingsClasses()->pluck('school_classes.id')->all();
    }

    public function teachingJournals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class);
    }

    public function assessmentPackages(): HasMany
    {
        return $this->hasMany(AssessmentPackage::class);
    }
}
