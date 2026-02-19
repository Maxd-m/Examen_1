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
    public function generateText(): void
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['content'])) {
            http_response_code(400);
            echo json_encode(["message" => "Campo 'content' requerido"]);
            return;
        }

            $text = $data['content'];
            $size = $data['size'] ?? 300;
            $errorLevel = $data['errorLevel'] ?? 'M';

        $file = $this->generator->generateAndSave($text, $size, $errorLevel);

        http_response_code(201);
        echo json_encode([
            "message" => "QR generado",
            "file" => basename($file)
        ]);
    }

    public function generateURL(): void
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['URL'])) {
            http_response_code(400);
            echo json_encode(["message" => "Campo 'URL' requerido"]);
            return;
        }

        // Validar formato de URL
        if (!filter_var($data['URL'], FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(["message" => "URL no válida"]);
            return;
        }

            $text = $data['URL'];
            $size = $data['size'] ?? 300;
            $errorLevel = $data['errorLevel'] ?? 'M';

        $file = $this->generator->generateAndSave($text, $size, $errorLevel);

        http_response_code(201);
        echo json_encode([
            "message" => "QR generado",
            "file" => basename($file)
        ]);
    }
    
    public function generateWifi(): void
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['SSID']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["message" => "Campos 'SSID' y 'password' requeridos"]);
            return;
        }

        $ssid = $data['SSID'];
        $password = $data['password'];
        $encryption = $data['encryption'] ?? 'WPA';
        $size = $data['size'] ?? 300;
        $errorLevel = $data['errorLevel'] ?? 'M';

        // Formato para WiFi: WIFI:T:WPA;S:SSID;P:password;;
        $wifiString = "WIFI:T:$encryption;S:$ssid;P:$password;;";

        $file = $this->generator->generateAndSave($wifiString, $size, $errorLevel);

        http_response_code(201);
        echo json_encode([
            "message" => "QR de WiFi generado",
            "file" => basename($file)
        ]);
    }

    public function generateCordinates(): void
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['latitude']) || empty($data['longitude'])) {
            http_response_code(400);
            echo json_encode(["message" => "Campos 'latitude' y 'longitude' requeridos"]);
            return;
        }

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $size = $data['size'] ?? 300;
        $errorLevel = $data['errorLevel'] ?? 'M';

        // Formato para coordenadas en google maps:
        $geoString = "https://www.google.com/maps/@$latitude,$longitude,15z";

        $file = $this->generator->generateAndSave($geoString, $size, $errorLevel);

        http_response_code(201);
        echo json_encode([
            "message" => "QR de coordenadas generado",
            "file" => basename($file)
        ]);
    }

}
