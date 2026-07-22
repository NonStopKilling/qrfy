<?php

namespace App\Services;

use App\Models\Asset;
use RuntimeException;

class QrLabelPngGenerator
{
    private const WIDTH = 1800;

    private const HEIGHT = 2400;

    private const CONTENT_MARGIN = 30;

    private const TEXT_SIZE = 48;

    private const TEXT_LINE_HEIGHT = 58;

    public function __construct(private readonly QrCodeSvgGenerator $qrGenerator) {}

    public function generate(Asset $asset, string $publicUrl, string $consultUrl): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('La extensión GD es necesaria para generar la etiqueta de impresión.');
        }

        $regularFont = $this->font(false);
        $boldFont = $this->font(true);
        $useBitmapFallback = $this->usingBitmapFallback($regularFont, $boldFont);
        $textSize = $useBitmapFallback ? 84 : self::TEXT_SIZE;
        $textLineHeight = $useBitmapFallback ? 96 : self::TEXT_LINE_HEIGHT;
        $textStartY = $useBitmapFallback ? 1880 : 2060;

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imageresolution($image, 300, 300);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $ink = imagecolorallocate($image, 17, 17, 17);
        $yellow = imagecolorallocate($image, 250, 177, 24);
        imagefill($image, 0, 0, $white);
        imagefilledrectangle($image, 0, 0, self::WIDTH - 1, 250, $black);
        imagefilledrectangle($image, 0, 250, self::WIDTH - 1, 268, $yellow);

        $this->drawLogo($image);
        $contentWidth = self::WIDTH - (self::CONTENT_MARGIN * 2);
        $qrArea = $useBitmapFallback ? 1520 : $contentWidth;
        $qrLeft = self::CONTENT_MARGIN + intdiv($contentWidth - $qrArea, 2);
        $this->drawQr($image, $publicUrl, $qrLeft, 268, $qrArea);

        $y = $this->wrappedText($image, $asset->name, self::CONTENT_MARGIN, $textStartY, $textSize, $ink, $boldFont, $contentWidth, $textLineHeight);
        $y = $this->wrappedText($image, 'Serie: '.$asset->serial_number, self::CONTENT_MARGIN, $y + 4, $textSize, $ink, $regularFont, $contentWidth, $textLineHeight);
        $y = $this->wrappedText($image, 'Codigo: '.$asset->qr_code, self::CONTENT_MARGIN, $y + 4, $textSize, $ink, $boldFont, $contentWidth, $textLineHeight);
        $y = $this->wrappedText($image, 'Si no puede escanear: '.$this->displayUrl($consultUrl), self::CONTENT_MARGIN, $y + 8, $textSize, $ink, $regularFont, $contentWidth, $textLineHeight);
        $this->wrappedText($image, 'Ingrese el codigo '.$asset->qr_code, self::CONTENT_MARGIN, $y + 4, $textSize, $ink, $regularFont, $contentWidth, $textLineHeight);

        ob_start();
        imagepng($image, null, 9);
        $png = ob_get_clean();
        imagedestroy($image);

        if (! is_string($png) || $png === '') {
            throw new RuntimeException('No fue posible generar la etiqueta PNG.');
        }

        return $png;
    }

    private function drawQr(\GdImage $image, string $content, int $left, int $top, int $area): void
    {
        $matrix = $this->qrGenerator->matrix($content);
        $quietZone = 4;
        $modules = count($matrix) + ($quietZone * 2);
        $moduleSize = intdiv($area, $modules);
        $actualSize = $moduleSize * $modules;
        $offset = intdiv($area - $actualSize, 2);
        $black = imagecolorallocate($image, 17, 17, 17);

        foreach ($matrix as $row => $values) {
            foreach ($values as $column => $dark) {
                if (! $dark) {
                    continue;
                }
                $x = $left + $offset + (($column + $quietZone) * $moduleSize);
                $y = $top + $offset + (($row + $quietZone) * $moduleSize);
                imagefilledrectangle($image, $x, $y, $x + $moduleSize - 1, $y + $moduleSize - 1, $black);
            }
        }
    }

    private function drawLogo(\GdImage $image): void
    {
        $path = public_path('images/gfyservicios-nuevo-logo.png');
        $logo = is_file($path) ? @imagecreatefrompng($path) : false;
        if (! $logo instanceof \GdImage) {
            return;
        }

        $sourceWidth = imagesx($logo);
        $sourceHeight = imagesy($logo);
        $targetHeight = 194;
        $targetWidth = (int) round($sourceWidth * ($targetHeight / $sourceHeight));
        $targetWidth = min(1420, $targetWidth);
        $left = intdiv(self::WIDTH - $targetWidth, 2);
        imagecopyresampled($image, $logo, $left, 24, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($logo);
    }

    private function wrappedText(
        \GdImage $image,
        string $text,
        int $x,
        int $y,
        int $size,
        int $color,
        ?string $font,
        int $maxWidth,
        int $lineHeight,
    ): int {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/', trim($text)) ?: [] as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if ($this->textWidth($candidate, $size, $font) <= $maxWidth) {
                $line = $candidate;

                continue;
            }
            if ($line !== '') {
                $lines[] = $line;
            }
            $line = $word;
        }
        if ($line !== '') {
            $lines[] = $line;
        }

        foreach ($lines as $index => $value) {
            $lineWidth = $this->textWidth($value, $size, $font);
            $lineX = $x + intdiv(max(0, $maxWidth - $lineWidth), 2);
            $this->text($image, $value, $lineX, $y + ($index * $lineHeight), $size, $color, $font);
        }

        return $y + (max(1, count($lines)) * $lineHeight);
    }

    private function text(\GdImage $image, string $text, int $x, int $y, int $size, int $color, ?string $font): void
    {
        if ($font !== null && function_exists('imagettftext')) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $text);

            return;
        }

        // Fallback when TTF fonts are not available in production.
        // Draw a bitmap string and scale it up to approximate requested size.
        $bitmapFont = 5;
        $scale = $this->bitmapScale($size);
        $sourceWidth = max(1, imagefontwidth($bitmapFont) * strlen($text));
        $sourceHeight = imagefontheight($bitmapFont);

        $buffer = imagecreatetruecolor($sourceWidth, $sourceHeight);
        imagealphablending($buffer, false);
        imagesavealpha($buffer, true);
        $transparent = imagecolorallocatealpha($buffer, 0, 0, 0, 127);
        imagefill($buffer, 0, 0, $transparent);

        imagealphablending($buffer, true);
        imagestring($buffer, $bitmapFont, 0, 0, $text, $color);

        $targetWidth = $sourceWidth * $scale;
        $targetHeight = $sourceHeight * $scale;
        $top = max(0, $y - $targetHeight);
        imagecopyresized($image, $buffer, $x, $top, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($buffer);
    }

    private function textWidth(string $text, int $size, ?string $font): int
    {
        if ($font !== null && function_exists('imagettfbbox')) {
            $box = imagettfbbox($size, 0, $font, $text);

            return $box === false ? PHP_INT_MAX : abs($box[2] - $box[0]);
        }

        // Approximate width for built-in bitmap fonts.
        return imagefontwidth(5) * strlen($text) * $this->bitmapScale($size);
    }

    private function bitmapScale(int $size): int
    {
        // Built-in GD font 5 is ~15px high.
        return max(1, (int) round($size / 15));
    }

    private function usingBitmapFallback(?string $regularFont, ?string $boldFont): bool
    {
        return ! function_exists('imagettftext')
            || ! function_exists('imagettfbbox')
            || $regularFont === null
            || $boldFont === null;
    }

    private function displayUrl(string $url): string
    {
        return (string) preg_replace('~^https?://~i', '', rtrim($url, '/'));
    }

    private function font(bool $bold): ?string
    {
        $candidates = $bold
            ? [
                'C:\\Windows\\Fonts\\arialbd.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
                '/usr/local/share/fonts/DejaVuSans-Bold.ttf',
            ]
            : [
                'C:\\Windows\\Fonts\\arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/dejavu/DejaVuSans.ttf',
                '/usr/local/share/fonts/DejaVuSans.ttf',
            ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
