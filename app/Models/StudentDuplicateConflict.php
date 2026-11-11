<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDuplicateConflict extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_duplicate_conflicts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'duplicate_nis',
        'student_id',
        'conflict_with_student_id',
        'assigned_temp_nis',
        'nisn',
        'nik',
        'source',
        'status',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke data siswa terkait.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Relasi ke data siswa yang memiliki nomor NIS yang sama.
     */
    public function conflictWith(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'conflict_with_student_id');
    }

    /**
     * Relasi ke user admin yang menyelesaikan konflik.
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope untuk konflik yang masih pending (belum diselesaikan).
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk konflik yang sudah diselesaikan (resolved).
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }
}
