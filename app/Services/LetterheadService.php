<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class LetterheadService
{
    /**
     * Ambil instance AttendanceSetting.
     */
    public function getSetting(): AttendanceSetting
    {
        return AttendanceSetting::first() ?? new AttendanceSetting([
            'school_name' => 'SMP Negeri 1 Garuda',
        ]);
    }

    /**
     * Dapatkan data lengkap Kop Surat (termasuk path gambar & base64 untuk PDF).
     */
    public function getData(array $overrides = []): array
    {
        $setting = $this->getSetting();

        $govName = $overrides['government_name'] ?? $setting->kop_government_name;
        $instName = $overrides['institution_name'] ?? $setting->kop_institution_name;
        $schoolName = $overrides['school_name'] ?? ($setting->kop_school_name ?: $setting->school_name);
        $address = $overrides['address'] ?? ($setting->kop_address ?: $setting->school_address);
        $postalCode = $overrides['postal_code'] ?? $setting->kop_postal_code;
        $phone = $overrides['phone'] ?? $setting->kop_phone;
        $email = $overrides['email'] ?? $setting->kop_email;
        $website = $overrides['website'] ?? $setting->kop_website;
        $borderStyle = $overrides['border_style'] ?? ($setting->kop_border_style ?: 'double');
        $isActive = $overrides['is_active'] ?? ($setting->kop_is_active ?? true);

        // Logo Kiri
        $logoLeftStorage = $overrides['logo_left'] ?? $setting->kop_logo_left;
        if (empty($logoLeftStorage) && ! empty($setting->logo)) {
            $logoLeftStorage = $setting->logo;
        }

        // Logo Kanan
        $logoRightStorage = $overrides['logo_right'] ?? $setting->kop_logo_right;

        // Path & Base64 untuk kompatibilitas cetak PDF
        $logoLeftUrl = $this->resolveUrl($logoLeftStorage);
        $logoRightUrl = $this->resolveUrl($logoRightStorage);

        $logoLeftPath = $this->resolvePath($logoLeftStorage);
        $logoRightPath = $this->resolvePath($logoRightStorage);

        $logoLeftBase64 = $this->toBase64($logoLeftPath);
        $logoRightBase64 = $this->toBase64($logoRightPath);

        // Buat baris kontak yang rapi
        $contactParts = [];
        if (! empty($address)) {
            $addrLine = $address;
            if (! empty($postalCode)) {
                $addrLine .= ' Kode Pos '.$postalCode;
            }
            $contactParts[] = $addrLine;
        }
        $telecomParts = [];
        if (! empty($phone)) {
            $telecomParts[] = 'Telp: '.$phone;
        }
        if (! empty($email)) {
            $telecomParts[] = 'Email: '.$email;
        }
        if (! empty($website)) {
            $telecomParts[] = 'Website: '.$website;
        }
        if (! empty($telecomParts)) {
            $contactParts[] = implode(' | ', $telecomParts);
        }

        return [
            'government_name' => $govName,
            'institution_name' => $instName,
            'school_name' => $schoolName,
            'address' => $address,
            'postal_code' => $postalCode,
            'phone' => $phone,
            'email' => $email,
            'website' => $website,
            'border_style' => $borderStyle,
            'is_active' => (bool) $isActive,
            'logo_left_url' => $logoLeftUrl,
            'logo_right_url' => $logoRightUrl,
            'logo_left_path' => $logoLeftPath,
            'logo_right_path' => $logoRightPath,
            'logo_left_base64' => $logoLeftBase64,
            'logo_right_base64' => $logoRightBase64,
            'contact_lines' => $contactParts,
        ];
    }

    /**
     * Render komponen Blade Kop Surat menjadi string HTML.
     */
    public function renderHtml(array $overrides = [], bool $useBase64 = false): string
    {
        $data = $this->getData($overrides);

        return View::make('components.kop-surat', array_merge($data, [
            'useBase64' => $useBase64,
        ]))->render();
    }

    /**
     * Resolusi URL untuk web asset.
     */
    protected function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return null;
    }

    /**
     * Resolusi absolute local path file di server.
     */
    protected function resolvePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($path);
        if (file_exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }

    /**
     * Konversi file gambar lokal ke Data URI Base64.
     */
    public function toBase64(?string $absolutePath): ?string
    {
        if (empty($absolutePath) || ! file_exists($absolutePath)) {
            return null;
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $mime = match (strtolower($extension)) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        $content = @file_get_contents($absolutePath);
        if ($content === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($content);
    }
}
