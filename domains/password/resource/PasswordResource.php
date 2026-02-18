<?php
// echo 'entra';
// echo(file_exists(__DIR__ . '/../Password.php'));
require_once __DIR__ . '/../Password.php';


class PasswordResource
{
    private $password;

    public function __construct()
    {
        $this->password = new Password(16, true, false, false, 1);
    }

    // GET /api/v1/password/gen?length=20&upper=1&lower=1&digits=1&symbols=0&avoid_ambiguous=1&require_each=1&exclude=@#$
    public function generate()
    {
        header("Content-Type: application/json");

        // Longitud (default 16)
        $length = isset($_GET['length']) ? (int)$_GET['length'] : 16;

        // Reglas booleanas (acepta 1/0, true/false)
        $opts = [
            'upper' => isset($_GET['upper']) ? filter_var($_GET['upper'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'lower' => isset($_GET['lower']) ? filter_var($_GET['lower'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'digits' => isset($_GET['digits']) ? filter_var($_GET['digits'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'symbols' => isset($_GET['symbols']) ? filter_var($_GET['symbols'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'avoid_ambiguous' => isset($_GET['avoid_ambiguous']) ? filter_var($_GET['avoid_ambiguous'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'require_each' => isset($_GET['require_each']) ? filter_var($_GET['require_each'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true,
            'exclude' => $_GET['exclude'] ?? '',
        ];

        try {
            if ($length < 4 || $length > 128) {
                http_response_code(400);
                echo json_encode(["error" => "length debe estar entre 4 y 128"]);
                return;
            }
            $password = $this->password->generate_password($length, $opts);

            http_response_code(200);
            echo json_encode([
                "password" => $password,
                "length" => $length,
                "rules" => $opts
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "error" => $e->getMessage()
            ]);
        }
    }

    // POST /api/v1/password/validate
    public function validate()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "Debe enviar el campo 'password'"]);
            return;
        }

        $result = $this->password->validate_password($data['password'], [
            'min_length' => 8,
            'upper' => true,
            'lower' => true,
            'digits' => true,
            'symbols' => true
        ]);

        http_response_code(200);
        echo json_encode($result);
    }


}
