<?php

namespace App\Models\Ujian;

use App\Models\AttendanceSetting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UjianPengaturanTtd extends Model
{
    use HasFactory;

    protected $table = 'ujian_pengaturan_ttd';

    protected $fillable = [
        'ujian_periode_id',
        'nama_instansi',
        'kop_baris_1',
        'kop_baris_2',
        'kop_baris_3',
        'kop_baris_4',
        'jabatan_penandatangan',
        'nama_kepala',
        'nip_kepala',
        'tempat_penetapan',
        'tanggal_penetapan',
        'path_logo_kiri',
        'path_watermark',
        'opacity_watermark',
        'path_gambar_ttd',
        'path_stempel',
        'lebar_ttd_cm',
        'tinggi_ttd_cm',
        'offset_atas_ttd_cm',
        'offset_kanan_ttd_cm',
        'tampilkan_stempel',
        'tampilkan_qrcode_validasi',
        'is_active',
        'konten_tata_tertib',
        'konten_sk_panitia',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penetapan' => 'date',
            'opacity_watermark' => 'decimal:2',
            'lebar_ttd_cm' => 'decimal:2',
            'tinggi_ttd_cm' => 'decimal:2',
            'offset_atas_ttd_cm' => 'decimal:2',
            'offset_kanan_ttd_cm' => 'decimal:2',
            'tampilkan_stempel' => 'boolean',
            'tampilkan_qrcode_validasi' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(UjianPeriode::class, 'ujian_periode_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->path_logo_kiri && Storage::disk('public')->exists($this->path_logo_kiri)) {
            return asset('storage/'.$this->path_logo_kiri);
        }

        $mainSetting = AttendanceSetting::first();
        if ($mainSetting?->logo && Storage::disk('public')->exists($mainSetting->logo)) {
            return asset('storage/'.$mainSetting->logo);
        }

        return null;
    }

    public function getWatermarkUrlAttribute(): ?string
    {
        if ($this->path_watermark && Storage::disk('public')->exists($this->path_watermark)) {
            return asset('storage/'.$this->path_watermark);
        }

        return $this->logo_url;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        if ($this->path_gambar_ttd && Storage::disk('public')->exists($this->path_gambar_ttd)) {
            return asset('storage/'.$this->path_gambar_ttd);
        }

        return null;
    }

    public function getStempelUrlAttribute(): ?string
    {
        if ($this->path_stempel && Storage::disk('public')->exists($this->path_stempel)) {
            return asset('storage/'.$this->path_stempel);
        }

        return null;
    }
}
