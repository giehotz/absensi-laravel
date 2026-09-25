<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'card_school_name',
        'card_title',
        'card_logo',
        'card_validity_text',
        'card_back_instructions',
        'card_width_cm',
        'card_height_cm',
        'card_theme_color',
        'card_show_back_token',
        'card_show_signature',
        'card_principal_name',
        'card_principal_nip',
        'card_signature_image',
    ];

    protected function casts(): array
    {
        return [
            'tolerance_minutes' => 'integer',
            'kop_is_active' => 'boolean',
            'card_show_back_token' => 'boolean',
            'card_show_signature' => 'boolean',
            'card_width_cm' => 'decimal:2',
            'card_height_cm' => 'decimal:2',
        ];
    }

    public function getCardLogoUrlAttribute(): ?string
    {
        if ($this->card_logo && Storage::disk('public')->exists($this->card_logo)) {
            return asset('storage/'.$this->card_logo);
        }

        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset('storage/'.$this->logo);
        }

        return null;
    }

    public function getCardSignatureUrlAttribute(): ?string
    {
        if ($this->card_signature_image && Storage::disk('public')->exists($this->card_signature_image)) {
            return asset('storage/'.$this->card_signature_image);
        }

        return null;
    }

    public function getResolvedCardSchoolNameAttribute(): string
    {
        return ! empty($this->card_school_name)
            ? $this->card_school_name
            : ($this->school_name ?? 'MIN 2 TANGGAMUS');
    }

    public function getResolvedCardTitleAttribute(): string
    {
        return ! empty($this->card_title)
            ? $this->card_title
            : 'KARTU SISWA';
    }

    public function getResolvedCardValidityTextAttribute(): string
    {
        return ! empty($this->card_validity_text)
            ? $this->card_validity_text
            : 'BERLAKU SELAMA MENJADI SISWA';
    }

    public function getResolvedCardBackInstructionsAttribute(): string
    {
        return ! empty($this->card_back_instructions)
            ? $this->card_back_instructions
            : 'Arahkan layar ponsel ini ke kamera scanner sekolah. Pastikan kecerahan layar maksimal.';
    }
}
