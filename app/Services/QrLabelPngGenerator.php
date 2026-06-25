<?php

namespace App\Services;

use App\Models\Asset;
use RuntimeException;

class QrLabelPngGenerator
{
    private const WIDTH = 1800;

    private const HEIGHT = 2400;

    public function __construct(private readonly QrCodeSvgGenerator $qrGenerator) {}

    public function generate(Asset $asset, string $publicUrl, string $consultUrl): string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagettftext')) {
            throw new RuntimeException('Las extensiones GD y FreeType son necesarias para generar la etiqueta de impresión.');
        }

        $regularFont = $this->font(false);
        $boldFont = $this->font(true);
        if ($regularFont === null || $boldFont === null) {
            throw new RuntimeException('No se encontraron las fuentes necesarias para generar la etiqueta de impresión.');
        }

        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imageresolution($image, 300, 300);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 5, 5, 5);
        $ink = imagecolorallocate($image, 17, 17, 17);
        $muted = imagecolorallocate($image, 68, 68, 68);
        $yellow = imagecolorallocate($image, 250, 177, 24);
        imagefill($image, 0, 0, $white);
        imagefilledrectangle($image, 0, 0, self::WIDTH - 1, 320, $black);
        imagefilledrectangle($image, 0, 320, self::WIDTH - 1, 340, $yellow);

        $this->drawLogo($image);
        $this->drawQr($image, $publicUrl, 240, 380, 1320);

        $y = $this->wrappedText($image, $asset->name, 90, 1795, 62, $ink, $boldFont, 1620, 74);
        $y = $this->wrappedText($image, 'Serie: '.$asset->serial_number, 90, $y + 16, 42, $muted, $regularFont, 1620, 54);
        $y = $this->wrappedText($image, 'Código manual: '.$asset->qr_code, 90, $y + 16, 50, $ink, $boldFont, 1620, 62);

        $this->text($image, 'Si su celular no puede leer el QR:', 90, $y + 54, 42, $ink, $boldFont);
        $this->text($image, '1. Ingrese a '.$this->displayUrl($consultUrl), 90, $y + 112, 36, $muted, $regularFont);
        $this->text($image, '2. Escriba el código '.$asset->qr_code, 90, $y + 164, 36, $muted, $regularFont);

        $this->wrappedText(
            $image,
            'Ficha directa: '.$this->displayUrl($publicUrl),
            90,
            $y + 230,
            30,
            $muted,
            $regularFont,
            1620,
            40,
        );

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
            throw new RuntimeException('No se encontró el logo GF7 para generar la etiqueta.');
        }

        $sourceWidth = imagesx($logo);
        $sourceHeight = imagesy($logo);
        $targetHeight = 260;
        $targetWidth = (int) round($sourceWidth * ($targetHeight / $sourceHeight));
        $targetWidth = min(1500, $targetWidth);
        $left = intdiv(self::WIDTH - $targetWidth, 2);
        imagecopyresampled($image, $logo, $left, 28, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagedestroy($logo);
    }

    private function wrappedText(
        \GdImage $image,
        string $text,
        int $x,
        int $y,
        int $size,
        int $color,
        string $font,
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
            $this->text($image, $value, $x, $y + ($index * $lineHeight), $size, $color, $font);
        }

        return $y + (max(1, count($lines)) * $lineHeight);
    }

    private function text(\GdImage $image, string $text, int $x, int $y, int $size, int $color, string $font): void
    {
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
    }

    private function textWidth(string $text, int $size, string $font): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return $box === false ? PHP_INT_MAX : abs($box[2] - $box[0]);
    }

    private function displayUrl(string $url): string
    {
        return (string) preg_replace('~^https?://~i', '', rtrim($url, '/'));
    }

    private function font(bool $bold): ?string
    {
        $candidates = $bold
            ? ['C:\\Windows\\Fonts\\arialbd.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf']
            : ['C:\\Windows\\Fonts\\arial.ttf', '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
