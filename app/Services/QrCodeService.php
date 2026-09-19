<?php

namespace App\Services;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    /**
     * Menghasilkan Data URI SVG murni untuk disematkan langsung pada atribut src tag <img>.
     */
    public function generateDataUri(string $data, int $size = 200, int $margin = 4): string
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $writer = new SvgWriter;
        $result = $writer->write($qrCode);

        return $result->getDataUri();
    }

    /**
     * Menghasilkan string markup XML/SVG langsung.
     */
    public function generateSvg(string $data, int $size = 200, int $margin = 4): string
    {
        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: $margin,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $writer = new SvgWriter;
        $result = $writer->write($qrCode);

        // Bersihkan header xml jika disematkan inline pada blade
        return preg_replace('/<\?xml[^>]*\?>/i', '', $result->getString());
    }
}
