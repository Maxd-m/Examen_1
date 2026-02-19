<?php

namespace Maxim\Examen1\Domains\QrCode\Service;


use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeGenerator
{
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__, 3) . '/storage/qr';
    }

    public function generateAndSave(string $payload): string
    {
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }

        $filename = $this->storagePath . '/' . uniqid('qr_', true) . '.png';

        $qrCode = new QrCode(
            data: $payload,
            size: 300,
            margin: 10
        );

        // writer
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // guarda a fichero
        $result->saveToFile($filename);

        return $filename;
    }
}
