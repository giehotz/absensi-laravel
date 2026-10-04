<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentUploadBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_package_id',
        'uploaded_by',
        'filename',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_log',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'success_rows' => 'integer',
            'failed_rows' => 'integer',
            'error_log' => 'array',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(AssessmentPackage::class, 'assessment_package_id');
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
