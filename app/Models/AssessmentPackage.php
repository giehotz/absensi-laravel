<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'school_class_id',
        'subject_id',
        'teacher_id',
        'title',
        'type',
        'kktp_default',
        'status',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'kktp_default' => 'integer',
            'locked_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->orderBy('sheet_number');
    }

    public function activeAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class)->where('is_active', true)->orderBy('sheet_number');
    }

    /**
     * Dapatkan koleksi sumatif yang aktif (minimal S1 selalu aktif).
     */
    public function getActiveAssessments()
    {
        $actives = $this->assessments->where('is_active', true)->sortBy('sheet_number')->values();
        if ($actives->isEmpty() && $this->assessments->isNotEmpty()) {
            $first = $this->assessments->firstWhere('sheet_number', 1);
            if ($first) {
                $first->is_active = true;
                $first->saveQuietly();
                $actives = collect([$first]);
            }
        }

        return $actives;
    }

    public function uploadBatches(): HasMany
    {
        return $this->hasMany(AssessmentUploadBatch::class)->latest();
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isMateri(): bool
    {
        return ($this->type ?? 'materi') === 'materi';
    }

    public function isSts(): bool
    {
        return $this->type === 'sts';
    }

    public function isSas(): bool
    {
        return $this->type === 'sas';
    }

    public function scopeMateri($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'materi')->orWhereNull('type');
        });
    }

    public function scopeSts($query)
    {
        return $query->where('type', 'sts');
    }

    public function scopeSas($query)
    {
        return $query->where('type', 'sas');
    }

    public function scopeOfType($query, ?string $type)
    {
        if (! $type || $type === 'all') {
            return $query;
        }

        if ($type === 'materi') {
            return $this->scopeMateri($query);
        }

        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sts' => 'Sumatif Tengah Semester (STS)',
            'sas' => 'Sumatif Akhir Semester (SAS)',
            default => 'Sumatif Lingkup Materi',
        };
    }

    public function getTypeShortLabelAttribute(): string
    {
        return match ($this->type) {
            'sts' => 'STS',
            'sas' => 'SAS',
            default => 'Materi',
        };
    }

    public function getTypeBadgeBgAttribute(): string
    {
        return match ($this->type) {
            'sts' => 'bg-[#FFF3BF] text-amber-950 border-amber-950',
            'sas' => 'bg-[#FFE3E3] text-rose-950 border-rose-950',
            default => 'bg-[#D3F9D8] text-emerald-950 border-emerald-950',
        };
    }
}
