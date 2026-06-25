<?php

namespace Tests\Unit;

use App\Services\QrCodeSvgGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class QrCodeSvgGeneratorTest extends TestCase
{
    public function test_it_generates_deterministic_svg_with_quiet_zone(): void
    {
        $generator = new QrCodeSvgGenerator;
        $svg = $generator->generate('https://qrfy.test/qr/example-token');

        $this->assertSame($svg, $generator->generate('https://qrfy.test/qr/example-token'));
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('shape-rendering="crispEdges"', $svg);
        $this->assertMatchesRegularExpression('/viewBox="0 0 \d+ \d+"/', $svg);
        $this->assertStringNotContainsString('https://qrfy.test', $svg);
    }

    public function test_it_rejects_content_larger_than_supported_versions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new QrCodeSvgGenerator)->generate(str_repeat('x', 107));
    }
}
