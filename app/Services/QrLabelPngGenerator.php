<?php

namespace App\Services;

use App\Models\Asset;
use RuntimeException;

class QrLabelPngGenerator
{
    private const WIDTH = 1800;

    private const HEIGHT = 2400;

    private const CONTENT_MARGIN = 30;

    private const HEADER_HEIGHT = 250;

    private const DIVIDER_HEIGHT = 18;

    private const HEADER_PADDING = 48;

    private const QR_AREA = 1610;

    private const QR_TOP = 270;

    private const HEADER_TEXT_SIZE = 60;

    private const HEADER_PHONE_TEXT_SIZE = 72;

    private const TEXT_SIZE = 72;

    private const HEADER_TEXT_LINE_HEIGHT = 68;

    private const TEXT_LINE_HEIGHT = 76;

    private const TEXT_START_Y = 1870;

    private const HEADER_TEXT_Y = 116;

    public function __construct(private readonly QrCodeSvgGenerator $qrGenerator) {}

    public function generate(Asset $asset, string $publicUrl, string $consultUrl): string
    {
        if (! function_exists('imagecreatetruecolor')) {
            throw new RuntimeException('La extensión GD es necesaria para generar la etiqueta de impresión.');
        }

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imageresolution($image, 300, 300);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $ink = imagecolorallocate($image, 17, 17, 17);
        $yellow = imagecolorallocate($image, 250, 177, 24);
        imagefill($image, 0, 0, $white);
        imagefilledrectangle($image, 0, 0, self::WIDTH - 1, self::HEADER_HEIGHT - 1, $black);
        imagefilledrectangle($image, 0, self::HEADER_HEIGHT, self::WIDTH - 1, self::HEADER_HEIGHT + self::DIVIDER_HEIGHT - 1, $yellow);

        $this->drawHeader($image, $white);
        $contentWidth = self::WIDTH - (self::CONTENT_MARGIN * 2);
        $qrLeft = intdiv(self::WIDTH - self::QR_AREA, 2);
        $this->drawQr($image, $publicUrl, $qrLeft, self::QR_TOP, self::QR_AREA);

        $y = $this->wrappedText($image, $this->labelText($asset->name), self::CONTENT_MARGIN, self::TEXT_START_Y, self::TEXT_SIZE, $ink, $contentWidth, self::TEXT_LINE_HEIGHT);
        $y = $this->wrappedText($image, $this->labelText($asset->serial_number), self::CONTENT_MARGIN, $y + 4, self::TEXT_SIZE, $ink, $contentWidth, self::TEXT_LINE_HEIGHT);
        $y = $this->wrappedText($image, $this->labelText('Codigo: '.$asset->qr_code), self::CONTENT_MARGIN, $y + 4, self::TEXT_SIZE, $ink, $contentWidth, self::TEXT_LINE_HEIGHT);
        $y = $this->wrappedText($image, $this->labelText('Si no puede escanear:'), self::CONTENT_MARGIN, $y + 8, self::TEXT_SIZE, $ink, $contentWidth, self::TEXT_LINE_HEIGHT);
        $this->wrappedText($image, $this->displayConsultUrl($consultUrl), self::CONTENT_MARGIN, $y + 4, self::TEXT_SIZE, $ink, $contentWidth, self::TEXT_LINE_HEIGHT);

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

    private function drawHeader(\GdImage $image, int $white): void
    {
        $this->text($image, 'EMERGENCIAS, ESCANEAR QR', self::HEADER_PADDING, self::HEADER_TEXT_Y, self::HEADER_TEXT_SIZE, $white);
        $phonePrefix = 'O LLAMAR AL ';
        $phoneY = self::HEADER_TEXT_Y + self::HEADER_TEXT_LINE_HEIGHT;
        $this->text($image, $phonePrefix, self::HEADER_PADDING, $phoneY, self::HEADER_TEXT_SIZE, $white);
        $this->text($image, '+56 9 5619 2168', self::HEADER_PADDING + $this->textWidth($phonePrefix, self::HEADER_TEXT_SIZE), $phoneY, self::HEADER_PHONE_TEXT_SIZE, $white);

        $this->drawLogo($image);
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
        $targetWidth = min(590, $targetWidth);
        $left = self::WIDTH - self::HEADER_PADDING - $targetWidth;
        imagecopyresampled($image, $logo, $left, 26, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($logo);
    }

    private function wrappedText(
        \GdImage $image,
        string $text,
        int $x,
        int $y,
        int $size,
        int $color,
        int $maxWidth,
        int $lineHeight,
    ): int {
        $lines = [];
        $line = '';
        foreach (preg_split('/\s+/', trim($text)) ?: [] as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;
            if ($this->textWidth($candidate, $size) <= $maxWidth) {
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
            $lineWidth = $this->textWidth($value, $size);
            $lineX = $x + intdiv(max(0, $maxWidth - $lineWidth), 2);
            $this->text($image, $value, $lineX, $y + ($index * $lineHeight), $size, $color);
        }

        return $y + (max(1, count($lines)) * $lineHeight);
    }

    private function text(\GdImage $image, string $text, int $x, int $y, int $size, int $color): void
    {
        // Production labels intentionally use GD's built-in bitmap font.
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

    private function textWidth(string $text, int $size): int
    {
        return imagefontwidth(5) * strlen($text) * $this->bitmapScale($size);
    }

    private function bitmapScale(int $size): int
    {
        // Built-in GD font 5 is ~15px high.
        return max(1, (int) round($size / 15));
    }

    private function displayUrl(string $url): string
    {
        return (string) preg_replace('~^https?://~i', '', rtrim($url, '/'));
    }

    private function displayConsultUrl(string $url): string
    {
        return $this->labelText($this->displayUrl($url));
    }

    private function labelText(string $text): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', $text));
        $text = strtr($text, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ]);
        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($ascii) && $ascii !== '') {
                $text = $ascii;
            }
        }

        return strtoupper($text);
    }
}
