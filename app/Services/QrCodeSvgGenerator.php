<?php

namespace App\Services;

use InvalidArgumentException;

class QrCodeSvgGenerator
{
    private const BLOCKS = [
        1 => [[1, 16, 10]],
        2 => [[1, 28, 16]],
        3 => [[1, 44, 26]],
        4 => [[2, 32, 18]],
        5 => [[2, 43, 24]],
        6 => [[4, 27, 16]],
    ];

    private const ALIGNMENT = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
    ];

    /** @var array<int, int>|null */
    private static ?array $exp = null;

    /** @var array<int, int>|null */
    private static ?array $log = null;

    public function generate(string $content, int $scale = 6, int $quietZone = 4): string
    {
        return $this->toSvg($this->matrix($content), $scale, $quietZone);
    }

    /** @return array<int, array<int, bool>> */
    public function matrix(string $content): array
    {
        $bytes = array_values(unpack('C*', $content) ?: []);
        $version = $this->selectVersion(count($bytes));
        $codewords = $this->createCodewords($bytes, $version);
        $best = null;
        $bestPenalty = PHP_INT_MAX;

        for ($mask = 0; $mask < 8; $mask++) {
            $matrix = $this->buildMatrix($version, $codewords, $mask);
            $penalty = $this->penalty($matrix);
            if ($penalty < $bestPenalty) {
                $best = $matrix;
                $bestPenalty = $penalty;
            }
        }

        return $best ?? [];
    }

    private function selectVersion(int $byteCount): int
    {
        foreach (self::BLOCKS as $version => $groups) {
            $capacity = array_sum(array_map(fn (array $group) => $group[0] * $group[1], $groups));
            if (4 + 8 + ($byteCount * 8) <= ($capacity * 8)) {
                return $version;
            }
        }

        throw new InvalidArgumentException('La URL es demasiado larga para el generador QR.');
    }

    /** @param array<int, int> $bytes @return array<int, int> */
    private function createCodewords(array $bytes, int $version): array
    {
        $dataCapacity = array_sum(array_map(fn (array $group) => $group[0] * $group[1], self::BLOCKS[$version]));
        $bits = [0, 1, 0, 0];
        $this->appendBits($bits, count($bytes), 8);
        foreach ($bytes as $byte) {
            $this->appendBits($bits, $byte, 8);
        }

        $remaining = ($dataCapacity * 8) - count($bits);
        for ($i = 0; $i < min(4, $remaining); $i++) {
            $bits[] = 0;
        }
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $data = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $value = 0;
            for ($j = 0; $j < 8; $j++) {
                $value = ($value << 1) | $bits[$i + $j];
            }
            $data[] = $value;
        }
        for ($pad = 0; count($data) < $dataCapacity; $pad++) {
            $data[] = $pad % 2 === 0 ? 0xEC : 0x11;
        }

        $dataBlocks = [];
        $errorBlocks = [];
        $offset = 0;
        foreach (self::BLOCKS[$version] as [$count, $dataCount, $errorCount]) {
            for ($i = 0; $i < $count; $i++) {
                $block = array_slice($data, $offset, $dataCount);
                $offset += $dataCount;
                $dataBlocks[] = $block;
                $errorBlocks[] = $this->errorCorrection($block, $errorCount);
            }
        }

        $result = [];
        $maxData = max(array_map('count', $dataBlocks));
        $maxError = max(array_map('count', $errorBlocks));
        for ($i = 0; $i < $maxData; $i++) {
            foreach ($dataBlocks as $block) {
                if (isset($block[$i])) {
                    $result[] = $block[$i];
                }
            }
        }
        for ($i = 0; $i < $maxError; $i++) {
            foreach ($errorBlocks as $block) {
                if (isset($block[$i])) {
                    $result[] = $block[$i];
                }
            }
        }

        return $result;
    }

    /** @param array<int, int> $bits */
    private function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /** @param array<int, int> $data @return array<int, int> */
    private function errorCorrection(array $data, int $degree): array
    {
        $generator = [1];
        for ($i = 0; $i < $degree; $i++) {
            $generator = $this->polyMultiply($generator, [1, $this->gexp($i)]);
        }

        $remainder = array_merge($data, array_fill(0, $degree, 0));
        for ($i = 0; $i < count($data); $i++) {
            $factor = $remainder[$i];
            if ($factor === 0) {
                continue;
            }
            $log = $this->glog($factor);
            foreach ($generator as $j => $coefficient) {
                $remainder[$i + $j] ^= $coefficient === 0 ? 0 : $this->gexp($this->glog($coefficient) + $log);
            }
        }

        return array_slice($remainder, -$degree);
    }

    /** @param array<int, int> $left @param array<int, int> $right @return array<int, int> */
    private function polyMultiply(array $left, array $right): array
    {
        $result = array_fill(0, count($left) + count($right) - 1, 0);
        foreach ($left as $i => $a) {
            foreach ($right as $j => $b) {
                if ($a !== 0 && $b !== 0) {
                    $result[$i + $j] ^= $this->gexp($this->glog($a) + $this->glog($b));
                }
            }
        }

        return $result;
    }

    private function initializeField(): void
    {
        if (self::$exp !== null) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);
        $value = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $value;
            self::$log[$value] = $i;
            $value <<= 1;
            if (($value & 0x100) !== 0) {
                $value ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    private function gexp(int $value): int
    {
        $this->initializeField();

        return self::$exp[$value % 255];
    }

    private function glog(int $value): int
    {
        $this->initializeField();
        if ($value === 0) {
            throw new InvalidArgumentException('No existe logaritmo de cero en GF(256).');
        }

        return self::$log[$value];
    }

    /** @param array<int, int> $codewords @return array<int, array<int, bool|null>> */
    private function buildMatrix(int $version, array $codewords, int $mask): array
    {
        $size = 17 + ($version * 4);
        $matrix = array_fill(0, $size, array_fill(0, $size, null));
        $this->addFinder($matrix, 0, 0);
        $this->addFinder($matrix, $size - 7, 0);
        $this->addFinder($matrix, 0, $size - 7);
        $this->addAlignment($matrix, $version);

        for ($i = 8; $i < $size - 8; $i++) {
            if ($matrix[6][$i] === null) {
                $matrix[6][$i] = $i % 2 === 0;
            }
            if ($matrix[$i][6] === null) {
                $matrix[$i][6] = $i % 2 === 0;
            }
        }

        $this->addFormat($matrix, $mask);
        $this->placeData($matrix, $codewords, $mask);

        return $matrix;
    }

    /** @param array<int, array<int, bool|null>> $matrix */
    private function addFinder(array &$matrix, int $row, int $column): void
    {
        $size = count($matrix);
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $y = $row + $dy;
                $x = $column + $dx;
                if ($y < 0 || $y >= $size || $x < 0 || $x >= $size) {
                    continue;
                }
                $matrix[$y][$x] = ($dy >= 0 && $dy <= 6 && ($dx === 0 || $dx === 6))
                    || ($dx >= 0 && $dx <= 6 && ($dy === 0 || $dy === 6))
                    || ($dy >= 2 && $dy <= 4 && $dx >= 2 && $dx <= 4);
            }
        }
    }

    /** @param array<int, array<int, bool|null>> $matrix */
    private function addAlignment(array &$matrix, int $version): void
    {
        foreach (self::ALIGNMENT[$version] as $row) {
            foreach (self::ALIGNMENT[$version] as $column) {
                if ($matrix[$row][$column] !== null) {
                    continue;
                }
                for ($dy = -2; $dy <= 2; $dy++) {
                    for ($dx = -2; $dx <= 2; $dx++) {
                        $matrix[$row + $dy][$column + $dx] = max(abs($dy), abs($dx)) !== 1;
                    }
                }
            }
        }
    }

    /** @param array<int, array<int, bool|null>> $matrix */
    private function addFormat(array &$matrix, int $mask): void
    {
        $size = count($matrix);
        $data = $mask;
        $value = $data << 10;
        $generator = 0x537;
        while ($this->bitLength($value) >= $this->bitLength($generator)) {
            $value ^= $generator << ($this->bitLength($value) - $this->bitLength($generator));
        }
        $bits = (($data << 10) | $value) ^ 0x5412;

        for ($i = 0; $i < 15; $i++) {
            $dark = (($bits >> $i) & 1) === 1;
            $verticalRow = $i < 6 ? $i : ($i < 8 ? $i + 1 : $size - 15 + $i);
            $matrix[$verticalRow][8] = $dark;

            $horizontalColumn = $i < 8 ? $size - $i - 1 : ($i === 8 ? 7 : 14 - $i);
            $matrix[8][$horizontalColumn] = $dark;
        }
        $matrix[$size - 8][8] = true;
    }

    private function bitLength(int $value): int
    {
        $length = 0;
        while ($value !== 0) {
            $length++;
            $value >>= 1;
        }

        return $length;
    }

    /** @param array<int, array<int, bool|null>> $matrix @param array<int, int> $codewords */
    private function placeData(array &$matrix, array $codewords, int $mask): void
    {
        $size = count($matrix);
        $byteIndex = 0;
        $bitIndex = 7;
        $row = $size - 1;
        $direction = -1;

        for ($column = $size - 1; $column > 0; $column -= 2) {
            if ($column === 6) {
                $column--;
            }
            while (true) {
                for ($offset = 0; $offset < 2; $offset++) {
                    $x = $column - $offset;
                    if ($matrix[$row][$x] !== null) {
                        continue;
                    }
                    $dark = $byteIndex < count($codewords)
                        && (($codewords[$byteIndex] >> $bitIndex) & 1) === 1;
                    if ($this->mask($mask, $row, $x)) {
                        $dark = ! $dark;
                    }
                    $matrix[$row][$x] = $dark;
                    $bitIndex--;
                    if ($bitIndex < 0) {
                        $byteIndex++;
                        $bitIndex = 7;
                    }
                }
                $row += $direction;
                if ($row < 0 || $row >= $size) {
                    $row -= $direction;
                    $direction = -$direction;
                    break;
                }
            }
        }
    }

    private function mask(int $mask, int $row, int $column): bool
    {
        return match ($mask) {
            0 => ($row + $column) % 2 === 0,
            1 => $row % 2 === 0,
            2 => $column % 3 === 0,
            3 => ($row + $column) % 3 === 0,
            4 => (intdiv($row, 2) + intdiv($column, 3)) % 2 === 0,
            5 => (($row * $column) % 2) + (($row * $column) % 3) === 0,
            6 => ((($row * $column) % 2) + (($row * $column) % 3)) % 2 === 0,
            7 => ((($row * $column) % 3) + (($row + $column) % 2)) % 2 === 0,
        };
    }

    /** @param array<int, array<int, bool>> $matrix */
    private function penalty(array $matrix): int
    {
        $size = count($matrix);
        $score = 0;
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($x + 1 < $size && $y + 1 < $size) {
                    $value = $matrix[$y][$x];
                    if ($value === $matrix[$y][$x + 1] && $value === $matrix[$y + 1][$x] && $value === $matrix[$y + 1][$x + 1]) {
                        $score += 3;
                    }
                }
            }
            $score += $this->runPenalty($matrix[$y]);
            $score += $this->runPenalty(array_column($matrix, $y));
        }

        $dark = array_sum(array_map(fn (array $row) => count(array_filter($row)), $matrix));
        $score += intdiv(abs(($dark * 100) - ($size * $size * 50)), $size * $size * 5) * 10;

        return $score;
    }

    /** @param array<int, bool> $line */
    private function runPenalty(array $line): int
    {
        $score = 0;
        $run = 1;
        for ($i = 1; $i < count($line); $i++) {
            if ($line[$i] === $line[$i - 1]) {
                $run++;
            } else {
                if ($run >= 5) {
                    $score += 3 + ($run - 5);
                }
                $run = 1;
            }
        }

        return $score + ($run >= 5 ? 3 + ($run - 5) : 0);
    }

    /** @param array<int, array<int, bool>> $matrix */
    private function toSvg(array $matrix, int $scale, int $quietZone): string
    {
        $dimension = (count($matrix) + ($quietZone * 2)) * $scale;
        $path = [];
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $dark) {
                if ($dark) {
                    $path[] = 'M'.(($x + $quietZone) * $scale).' '.(($y + $quietZone) * $scale).'h'.$scale.'v'.$scale.'h-'.$scale.'z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$dimension.' '.$dimension.'" role="img" aria-label="Código QR">'
            .'<rect width="100%" height="100%" fill="#fff"/><path d="'.implode('', $path).'" fill="#111827" shape-rendering="crispEdges"/></svg>';
    }
}
