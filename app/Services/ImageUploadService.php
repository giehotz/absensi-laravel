<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload dan konversi file gambar ke format WebP terkompresi.
     * Mempertahankan alpha transparency (transparansi PNG/WebP) dan menangani orientasi EXIF kamera.
     * Jika file adalah dokumen (PDF) atau vector (SVG), disimpan langsung secara aman.
     *
     * @param  string  $directory  Direktori tujuan (relatif terhadap disk)
     * @param  int  $quality  Kualitas WebP (0-100, default 82)
     * @param  int|null  $maxWidth  Batas lebar maksimal dalam piksel (opsional)
     * @param  int|null  $maxHeight  Batas tinggi maksimal dalam piksel (opsional)
     * @param  string  $disk  Storage disk (default: 'public')
     * @return string Path file relatif yang disimpan di disk
     */
    public function uploadAsWebp(
        UploadedFile $file,
        string $directory = 'uploads',
        int $quality = 82,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        string $disk = 'public'
    ): string {
        $mime = strtolower($file->getMimeType() ?: '');
        $extension = strtolower($file->getClientOriginalExtension());

        // File non-raster: SVG, PDF, dan ICO disimpan langsung tanpa rasterisasi GD
        if ($mime === 'image/svg+xml' || $extension === 'svg' ||
            $mime === 'application/pdf' || $extension === 'pdf' ||
            $mime === 'image/x-icon' || $mime === 'image/vnd.microsoft.icon' || $extension === 'ico'
        ) {
            return $file->store($directory, $disk);
        }

        try {
            $realPath = $file->getRealPath();
            $binaryContent = file_get_contents($realPath);
            if ($binaryContent === false) {
                return $file->store($directory, $disk);
            }

            $image = @imagecreatefromstring($binaryContent);
            if (! $image instanceof GdImage) {
                return $file->store($directory, $disk);
            }

            // Tangani auto-rotate orientasi EXIF kamera HP
            $image = $this->fixExifOrientation($image, $realPath);

            // Resize jika melebihi batas resolusi maksimal
            if ($maxWidth !== null || $maxHeight !== null) {
                $image = $this->resizeIfLarger($image, $maxWidth, $maxHeight);
            }

            // Konfigurasi transparansi penuh untuk WebP
            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            // Render ke buffer biner WebP
            ob_start();
            imagewebp($image, null, $quality);
            $webpData = ob_get_clean();
            imagedestroy($image);

            if ($webpData === false || strlen($webpData) === 0) {
                return $file->store($directory, $disk);
            }

            $filename = trim($directory, '/').'/'.Str::random(40).'.webp';
            Storage::disk($disk)->put($filename, $webpData);

            return $filename;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengonversi gambar ke WebP, fallback ke penyimpanan asli: '.$e->getMessage());

            return $file->store($directory, $disk);
        }
    }

    /**
     * Konversi data gambar Base64 (misalnya hasil cropping CropperJS) ke format WebP.
     *
     * @return string Path file yang tersimpan
     */
    public function uploadBase64AsWebp(
        string $base64String,
        string $directory = 'uploads',
        int $quality = 82,
        ?int $maxWidth = null,
        ?int $maxHeight = null,
        string $disk = 'public'
    ): string {
        try {
            $commaPos = strpos($base64String, ',');
            $rawBase64 = $commaPos !== false ? substr($base64String, $commaPos + 1) : $base64String;
            $binary = base64_decode($rawBase64);

            if ($binary === false) {
                throw new \InvalidArgumentException('Data Base64 gambar tidak valid.');
            }

            $image = @imagecreatefromstring($binary);
            if (! $image instanceof GdImage) {
                throw new \RuntimeException('Gagal memproses biner gambar dari Base64.');
            }

            if ($maxWidth !== null || $maxHeight !== null) {
                $image = $this->resizeIfLarger($image, $maxWidth, $maxHeight);
            }

            imagepalettetotruecolor($image);
            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            imagewebp($image, null, $quality);
            $webpData = ob_get_clean();
            imagedestroy($image);

            $filename = trim($directory, '/').'/crop_'.Str::random(32).'.webp';
            Storage::disk($disk)->put($filename, $webpData);

            return $filename;
        } catch (\Throwable $e) {
            Log::error('Error uploadBase64AsWebp: '.$e->getMessage());
            // Fallback: simpan biner jpg biasa jika konversi webp gagal
            $filename = trim($directory, '/').'/crop_'.uniqid().'.jpg';
            Storage::disk($disk)->put($filename, $binary ?? '');

            return $filename;
        }
    }

    /**
     * Menghapus file lama dari storage jika ada.
     */
    public function deleteOldFile(?string $path, string $disk = 'public'): void
    {
        if (! empty($path) && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Koreksi rotasi gambar berdasarkan metadata EXIF (kamera smartphone).
     */
    protected function fixExifOrientation(GdImage $image, string $filePath): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($filePath);
            if (! empty($exif['Orientation'])) {
                $rotated = match ($exif['Orientation']) {
                    3 => imagerotate($image, 180, 0),
                    6 => imagerotate($image, -90, 0),
                    8 => imagerotate($image, 90, 0),
                    default => $image,
                };

                if ($rotated instanceof GdImage && $rotated !== $image) {
                    imagedestroy($image);

                    return $rotated;
                }
            }
        } catch (\Throwable) {
            // Ignore EXIF errors on non-JPEG or stripped files
        }

        return $image;
    }

    /**
     * Perkecil resolusi gambar jika melebihi ukuran maksimum tanpa mengubah rasio aspek.
     */
    protected function resizeIfLarger(GdImage $image, ?int $maxWidth, ?int $maxHeight): GdImage
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth <= 0 || $origHeight <= 0) {
            return $image;
        }

        $targetWidth = $maxWidth ?? $origWidth;
        $targetHeight = $maxHeight ?? $origHeight;

        if ($origWidth <= $targetWidth && $origHeight <= $targetHeight) {
            return $image;
        }

        $ratio = min($targetWidth / $origWidth, $targetHeight / $origHeight);
        $newWidth = max(1, (int) round($origWidth * $ratio));
        $newHeight = max(1, (int) round($origHeight * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($image);

        return $resized;
    }
}
