<?php

namespace App\Services;

use App\Models\CustomQrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomQrGeneratorService
{
    /**
     * Konversi kode warna HEX ke instance Color Endroid.
     */
    public function hexToColor(string $hex, Color $fallback): Color
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return $fallback;
        }

        $r = (int) hexdec(substr($hex, 0, 2));
        $g = (int) hexdec(substr($hex, 2, 2));
        $b = (int) hexdec(substr($hex, 4, 2));

        return new Color($r, $g, $b);
    }

    /**
     * Bangun objek QrCode, Logo, dan Label dari model.
     *
     * @return array{qrCode: QrCode, logo: ?Logo, label: ?Label}
     */
    protected function buildComponents(CustomQrCode $qrCodeModel, bool $punchoutBackground = false): array
    {
        $fgColor = $this->hexToColor($qrCodeModel->qr_color, new Color(0, 0, 0));
        $bgColor = $this->hexToColor($qrCodeModel->bg_color, new Color(255, 255, 255));

        $logoPath = $qrCodeModel->getResolvedLogoAbsolutePath();
        $hasLogo = ! empty($logoPath) && file_exists($logoPath);

        // Jika terdapat logo di tengah, gunakan Error Correction High agar tetap mudah terbaca
        $errorCorrection = $hasLogo ? ErrorCorrectionLevel::High : ErrorCorrectionLevel::Medium;

        $size = max(100, min(1000, $qrCodeModel->size ?: 300));

        $qrCode = new QrCode(
            data: $qrCodeModel->target_url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: $errorCorrection,
            size: $size,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: $fgColor,
            backgroundColor: $bgColor
        );

        $logo = null;
        if ($hasLogo) {
            $logoWidth = (int) round($size * 0.22);
            $logo = new Logo(
                path: $logoPath,
                resizeToWidth: $logoWidth,
                resizeToHeight: $logoWidth,
                punchoutBackground: $punchoutBackground
            );
        }

        $label = null;
        if ($qrCodeModel->show_label && ! empty($qrCodeModel->title)) {
            $label = new Label(
                text: $qrCodeModel->title,
                alignment: LabelAlignment::Center,
                margin: new Margin(5, 5, 5, 5),
                textColor: $fgColor
            );
        }

        return [
            'qrCode' => $qrCode,
            'logo' => $logo,
            'label' => $label,
        ];
    }

    /**
     * Render SVG murni dalam bentuk string.
     */
    public function renderSvgString(CustomQrCode $qrCodeModel): string
    {
        // SvgWriter Endroid tidak mendukung punchoutBackground: true, sehingga harus bernilai false
        $components = $this->buildComponents($qrCodeModel, punchoutBackground: false);
        $writer = new SvgWriter;

        $result = $writer->write(
            $components['qrCode'],
            $components['logo'],
            $components['label']
        );

        $svgString = $result->getString();

        // Jika terdapat logo, sisipkan background rect di belakang logo agar modul QR tidak bertumpuk jika logo transparan
        if ($components['logo'] !== null) {
            $svgString = $this->injectLogoBackgroundInSvg($svgString, $qrCodeModel);
        }

        return $svgString;
    }

    /**
     * Sisipkan background rect di belakang logo SVG agar modul QR tidak bertumpuk jika logo memiliki transparansi.
     */
    protected function injectLogoBackgroundInSvg(string $svgString, CustomQrCode $qrCodeModel): string
    {
        $bgColor = $qrCodeModel->bg_color ?: '#ffffff';

        // Cari tag <image ...> yang disisipkan oleh SvgWriter
        if (preg_match('/<image\s+([^>]*?)x="([^"]+)"\s+y="([^"]+)"\s+width="([^"]+)"\s+height="([^"]+)"/i', $svgString, $matches)) {
            $x = (float) $matches[2];
            $y = (float) $matches[3];
            $w = (float) $matches[4];
            $h = (float) $matches[5];

            // Beri margin padding kecil 2px di sekitar logo
            $pad = 2.0;
            $rectX = $x - $pad;
            $rectY = $y - $pad;
            $rectW = $w + ($pad * 2);
            $rectH = $h + ($pad * 2);

            $rectTag = sprintf(
                '<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" fill="%s" rx="4" ry="4"/>',
                $rectX,
                $rectY,
                $rectW,
                $rectH,
                htmlspecialchars($bgColor, ENT_QUOTES, 'UTF-8')
            );

            $svgString = preg_replace('/(<image\s)/i', $rectTag.'$1', $svgString, 1) ?? $svgString;
        }

        return $svgString;
    }

    /**
     * Render data binary PNG.
     */
    public function renderPngBinary(CustomQrCode $qrCodeModel): string
    {
        // PngWriter mendukung punchoutBackground: true untuk mengosongkan modul QR di belakang logo
        $components = $this->buildComponents($qrCodeModel, punchoutBackground: true);
        $writer = new PngWriter;

        $result = $writer->write(
            $components['qrCode'],
            $components['logo'],
            $components['label']
        );

        return $result->getString();
    }

    /**
     * Generate berkas PNG dan SVG, simpan berkas ke storage publik dan update record model.
     */
    public function generateAndSave(CustomQrCode $qrCodeModel): void
    {
        // 1. Generate SVG
        $svgString = $this->renderSvgString($qrCodeModel);

        // 2. Generate PNG Binary
        $pngBinary = $this->renderPngBinary($qrCodeModel);

        // 3. Simpan berkas PNG ke storage
        $fileName = 'qr_'.$qrCodeModel->id.'_'.Str::random(10).'.png';
        $storagePath = 'qr-codes/'.$fileName;

        // Pastikan folder qr-codes ada di disk public
        if (! Storage::disk('public')->exists('qr-codes')) {
            Storage::disk('public')->makeDirectory('qr-codes');
        }

        // Hapus PNG lama jika ada
        if (! empty($qrCodeModel->png_path) && Storage::disk('public')->exists($qrCodeModel->png_path)) {
            Storage::disk('public')->delete($qrCodeModel->png_path);
        }

        Storage::disk('public')->put($storagePath, $pngBinary);

        // 4. Update model
        $qrCodeModel->updateQuietly([
            'svg_content' => $svgString,
            'png_path' => $storagePath,
        ]);
    }
}
