<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceUploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_uuid',
        'uploaded_by',
        'school_class_id',
        'schedule_id',
        'date',
        'original_filename',
        'total_rows',
        'success_rows',
        'failed_rows',
        'status',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'error_log' => 'array',
            'total_rows' => 'integer',
            'success_rows' => 'integer',
            'failed_rows' => 'integer',
        ];
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'upload_batch_id');
    }
}
