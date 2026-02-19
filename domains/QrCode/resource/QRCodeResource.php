<?php

namespace Maxim\Examen1\Domains\QrCode\Resource;

use Maxim\Examen1\Domains\QrCode\Service\QRCodeGenerator;

class QRCodeResource
{
    private QRCodeGenerator $generator;

    public function __construct()
    {
        $this->generator = new QRCodeGenerator();
    }

    // POST /api/v1/qr/gen
    public function generate(): void
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['text'])) {
            http_response_code(400);
            echo json_encode(["message" => "Campo 'text' requerido"]);
            return;
        }

        $file = $this->generator->generateAndSave($data['text']);

        http_response_code(201);
        echo json_encode([
            "message" => "QR generado",
            "file" => basename($file)
        ]);
    }
}
