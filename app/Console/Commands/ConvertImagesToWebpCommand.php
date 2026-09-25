<?php

namespace App\Console\Commands;

use App\Models\AttendanceSetting;
use App\Models\CustomQrCode;
use App\Models\LeaveRequest;
use App\Models\Student;
use App\Services\ImageUploadService;
use GdImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ConvertImagesToWebpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-to-webp 
                            {--quality=82 : Kualitas WebP dari 1-100} 
                            {--dry-run : Simulasi pengecekan tanpa mengubah file dan database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konversi gambar-gambar lama yang tersimpan di storage public ke format WebP untuk menghemat ruang penyimpanan';

    /**
     * Execute the console command.
     */
    public function handle(ImageUploadService $imageService): int
    {
        $quality = (int) $this->option('quality');
        $isDryRun = (bool) $this->option('dry-run');

        $this->components->info('Memulai pemindaian dan konversi gambar lama ke WebP...');
        if ($isDryRun) {
            $this->components->warn('Mode DRY RUN aktif: File dan database tidak akan dimodifikasi.');
        }

        $totalOriginalBytes = 0;
        $totalWebpBytes = 0;
        $convertedCount = 0;

        // 1. Konversi foto siswa
        $this->components->task('Memeriksa foto siswa (students)...', function () use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
            Student::whereNotNull('photo')
                ->where('photo', 'not like', '%.webp')
                ->chunkById(50, function ($students) use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
                    foreach ($students as $student) {
                        $newPath = $this->convertFile($student->photo, $quality, $isDryRun, $totalOriginalBytes, $totalWebpBytes);
                        if ($newPath) {
                            $convertedCount++;
                            if (! $isDryRun) {
                                $student->update(['photo' => $newPath]);
                            }
                        }
                    }
                });

            return true;
        });

        // 2. Konversi pengaturan absensi & kartu pelajar (logo, kop logos, favicon, card assets)
        $this->components->task('Memeriksa logo sekolah, favicon, kop surat, dan kartu pelajar...', function () use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
            $setting = AttendanceSetting::first();
            if ($setting) {
                $fields = ['logo', 'favicon', 'kop_logo_left', 'kop_logo_right', 'card_logo', 'card_signature_image'];
                $updates = [];
                foreach ($fields as $field) {
                    $val = $setting->{$field};
                    if ($val && ! str_ends_with(strtolower($val), '.webp') && ! str_ends_with(strtolower($val), '.svg') && ! str_ends_with(strtolower($val), '.ico')) {
                        $newPath = $this->convertFile($val, $quality, $isDryRun, $totalOriginalBytes, $totalWebpBytes);
                        if ($newPath) {
                            $convertedCount++;
                            $updates[$field] = $newPath;
                        }
                    }
                }
                if (! empty($updates) && ! $isDryRun) {
                    $setting->update($updates);
                }
            }

            return true;
        });

        // 4. Konversi logo kustom QR Code
        $this->components->task('Memeriksa logo kustom QR Code...', function () use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
            CustomQrCode::whereNotNull('custom_logo_path')
                ->where('custom_logo_path', 'not like', '%.webp')
                ->where('custom_logo_path', 'not like', '%.svg')
                ->chunkById(50, function ($qrCodes) use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
                    foreach ($qrCodes as $qr) {
                        $newPath = $this->convertFile($qr->custom_logo_path, $quality, $isDryRun, $totalOriginalBytes, $totalWebpBytes);
                        if ($newPath) {
                            $convertedCount++;
                            if (! $isDryRun) {
                                $qr->update(['custom_logo_path' => $newPath]);
                            }
                        }
                    }
                });

            return true;
        });

        // 5. Konversi lampiran surat izin (non-PDF)
        $this->components->task('Memeriksa lampiran surat izin/sakit (gambar)...', function () use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
            LeaveRequest::whereNotNull('attachment_path')
                ->where('attachment_path', 'not like', '%.webp')
                ->where('attachment_path', 'not like', '%.pdf')
                ->chunkById(50, function ($leaveRequests) use (&$totalOriginalBytes, &$totalWebpBytes, &$convertedCount, $quality, $isDryRun) {
                    foreach ($leaveRequests as $lr) {
                        $newPath = $this->convertFile($lr->attachment_path, $quality, $isDryRun, $totalOriginalBytes, $totalWebpBytes);
                        if ($newPath) {
                            $convertedCount++;
                            if (! $isDryRun) {
                                $lr->update(['attachment_path' => $newPath]);
                            }
                        }
                    }
                });

            return true;
        });

        $this->newLine();
        $this->components->info("Selesai! Total file yang dikonversi: {$convertedCount}");

        if ($convertedCount > 0) {
            $savedBytes = max(0, $totalOriginalBytes - $totalWebpBytes);
            $origMb = round($totalOriginalBytes / 1024 / 1024, 2);
            $webpMb = round($totalWebpBytes / 1024 / 1024, 2);
            $savedMb = round($savedBytes / 1024 / 1024, 2);
            $percent = $totalOriginalBytes > 0 ? round(($savedBytes / $totalOriginalBytes) * 100, 1) : 0;

            $this->components->bulletList([
                "Ukuran Awal: {$origMb} MB ({$totalOriginalBytes} bytes)",
                "Ukuran WebP: {$webpMb} MB ({$totalWebpBytes} bytes)",
                "Ruang Penyimpanan Dihemat: {$savedMb} MB ({$percent}%)",
            ]);
        }

        return self::SUCCESS;
    }

    /**
     * Konversi single file lama di disk public ke WebP.
     */
    protected function convertFile(
        ?string $relativePath,
        int $quality,
        bool $isDryRun,
        int &$totalOriginalBytes,
        int &$totalWebpBytes
    ): ?string {
        if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            $originalBytes = $disk->size($relativePath);
            $binary = $disk->get($relativePath);

            $image = @imagecreatefromstring($binary);
            if (! $image instanceof GdImage) {
                return null;
            }

            // Pertahankan channel transparansi
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);

            // Buffer WebP
            ob_start();
            $success = imagewebp($image, null, $quality);
            $webpData = ob_get_clean();

            if (! $success || empty($webpData)) {
                return null;
            }

            $webpBytes = strlen($webpData);
            $totalOriginalBytes += $originalBytes;
            $totalWebpBytes += $webpBytes;

            // Generate nama file baru .webp
            $info = pathinfo($relativePath);
            $dir = $info['dirname'] === '.' ? '' : $info['dirname'].'/';
            $newRelativePath = $dir.$info['filename'].'.webp';

            if (! $isDryRun) {
                $disk->put($newRelativePath, $webpData);
                // Hapus file lama jika path berbeda
                if ($newRelativePath !== $relativePath) {
                    $disk->delete($relativePath);
                }
            }

            return $newRelativePath;
        } catch (Throwable $e) {
            $this->components->error("Gagal mengonversi file {$relativePath}: {$e->getMessage()}");

            return null;
        }
    }
}
