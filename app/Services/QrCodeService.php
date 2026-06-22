<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use InvalidArgumentException;

class QrCodeService
{
    /**
     * Generate a QR code as base64 PNG data URI.
     */
    public function generateAsDataUri(string $data, int $size = 200): string
    {
        $data = $this->assertData($data);
        $size = $this->normalizeSize($size);

        $builder = new Builder;
        $result = $builder->build(
            writer: new PngWriter,
            data: $data,
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }

    /**
     * Generate a QR code and return as raw PNG binary.
     */
    public function generate(string $data, int $size = 200): string
    {
        $data = $this->assertData($data);
        $size = $this->normalizeSize($size);

        $builder = new Builder;
        $result = $builder->build(
            writer: new PngWriter,
            data: $data,
            encoding: new Encoding('UTF-8'),
            size: $size,
            margin: 10,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        return $result->getString();
    }

    private function assertData(string $data): string
    {
        $trimmed = trim($data);

        if ($trimmed === '') {
            throw new InvalidArgumentException('QR code data must be a non-empty string.');
        }

        return $trimmed;
    }

    private function normalizeSize(int $size): int
    {
        $min = 50;
        $max = 1000;

        if ($size < $min) {
            return $min;
        }

        if ($size > $max) {
            return $max;
        }

        return $size;
    }
}
