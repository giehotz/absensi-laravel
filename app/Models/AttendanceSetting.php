<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mode',
        'tolerance_minutes',
        'school_name',
        'npsn',
        'level',
        'school_address',
    ];

    protected function casts(): array
    {
        return [
            'tolerance_minutes' => 'integer',
        ];
    }
}
