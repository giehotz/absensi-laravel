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
        'logo',
        'favicon',
        'kop_government_name',
        'kop_institution_name',
        'kop_school_name',
        'kop_address',
        'kop_postal_code',
        'kop_phone',
        'kop_email',
        'kop_website',
        'kop_logo_left',
        'kop_logo_right',
        'kop_border_style',
        'kop_is_active',
    ];

    protected function casts(): array
    {
        return [
            'tolerance_minutes' => 'integer',
            'kop_is_active' => 'boolean',
        ];
    }
}
