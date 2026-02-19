<?php

namespace Maxim\Examen1\Domains\QrCode\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;


class QRCodeGenerator
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__, 3) . '/storage/qr';
    }

    public function generateAndSave(string $payload, int $size = 300, string $errorLevel = 'M'): string
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }

        // Validar tamaño
        if ($size < 100 || $size > 1000) {
            throw new \InvalidArgumentException('El tamaño debe estar entre 100 y 1000 px');
        }

        // Mapear nivel de corrección de errores
        $errorMap = [
            'L' => ErrorCorrectionLevel::Low,
            'M' => ErrorCorrectionLevel::Medium,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
        ];


        if (!isset($errorMap[$errorLevel])) {
            throw new \InvalidArgumentException('Nivel de corrección inválido: use L, M, Q o H');
        }

        $qrCode = new QrCode(
            data: $payload,
            size: $size,
            errorCorrectionLevel: $errorMap[$errorLevel]
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $filename = $this->storagePath . '/' . uniqid('qr_', true) . '.png';
        $result->saveToFile($filename);

        return $filename;
    }
}
