<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomQrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'target_url',
        'size',
        'qr_color',
        'bg_color',
        'logo_type',
        'custom_logo_path',
        'frame_style',
        'show_label',
        'svg_content',
        'png_path',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'show_label' => 'boolean',
        ];
    }

    /**
     * User pembuat QR Code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nama berkas download yang rapi dan aman.
     */
    public function getDownloadFileName(string $extension = 'png'): string
    {
        $baseName = ! empty($this->title)
            ? Str::slug($this->title)
            : 'qrcode-'.$this->id;

        return $baseName.'.'.$extension;
    }

    /**
     * URL Logo yang digunakan (jika ada).
     */
    public function getLogoUrl(): ?string
    {
        if ($this->logo_type === 'custom' && ! empty($this->custom_logo_path)) {
            return Storage::disk('public')->url($this->custom_logo_path);
        }

        if ($this->logo_type === 'default') {
            $setting = AttendanceSetting::first();
            if ($setting && ! empty($setting->logo)) {
                return Storage::disk('public')->url($setting->logo);
            }
        }

        return null;
    }

    /**
     * Path absolut lokal untuk logo (untuk generator Endroid).
     */
    public function getResolvedLogoAbsolutePath(): ?string
    {
        if ($this->logo_type === 'custom' && ! empty($this->custom_logo_path)) {
            $path = Storage::disk('public')->path($this->custom_logo_path);

            return file_exists($path) ? $path : null;
        }

        if ($this->logo_type === 'default') {
            $setting = AttendanceSetting::first();
            if ($setting && ! empty($setting->logo)) {
                $path = Storage::disk('public')->path($setting->logo);

                return file_exists($path) ? $path : null;
            }
        }

        return null;
    }

    /**
     * URL untuk download berkas PNG yang sudah tersimpan.
     */
    public function getPngUrl(): ?string
    {
        if (! empty($this->png_path) && Storage::disk('public')->exists($this->png_path)) {
            return Storage::disk('public')->url($this->png_path);
        }

        return null;
    }
}
