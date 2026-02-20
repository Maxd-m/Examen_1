<?php
require_once __DIR__.'/../../../config/Database.php';
require_once __DIR__.'/../Shortener.php';

class ShortenerResource {
    private $db;
    private $shortener;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->shortener = new Shortener($this->db);
    }

    // POST /shorten
    public function shorten() {
        // Leer body JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if(!$input || !isset($input['url'])){
            http_response_code(400);
            echo json_encode(['error' => 'Falta el parámetro url']);
            exit;
        }

        $url = $input['url'];
        $expiresAt = $input['expiresAt'] ?? null;
        $maxUses = $input['maxUses'] ?? null;
        $length = $input['length'] ?? null;

        // validar que url no tenga caracteres no permitidos
        if (preg_match('/[^\w\-.:\/?&=]/', $url)) {
            http_response_code(400);
            echo json_encode(['error' => 'URL contiene caracteres no permitidos']);
            exit;
        }

        //validar formato de url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'URL inválida']);
            exit;
        }
        
        //validar que length sea mayor a 5 y menor a 15
        if ($length !== null && (!is_int($length) || $length < 5 || $length > 15)) {
            http_response_code(400);
            echo json_encode(['error' => 'Length debe ser un entero entre 5 y 15']);
            exit;
        }

        //validar formato de fecha
        if ($expiresAt !== null && !DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt)) {
            http_response_code(400);
            echo json_encode(['error' => 'expiresAt debe tener formato Y-m-d H:i:s']);
            exit;
        }

        //validar que expiresAt no sea una fecha pasada
        $tz = new DateTimeZone('America/Mexico_City'); // cambia según tu país

        $expires = DateTime::createFromFormat('Y-m-d H:i:s', $expiresAt, $tz);
        $now = new DateTime('now', $tz);

        if ($expiresAt !== null && $expires < $now) {
            http_response_code(400);
            echo json_encode([
                'error' => 'expiresAt no puede ser una fecha pasada'
                // 'server_time' => $now->format('Y-m-d H:i:s')
            ]);
            exit;
        }

        //validar que maxUses sea un entero positivo
        if ($maxUses !== null && (!is_int($maxUses) || $maxUses < 1)) {
            http_response_code(400);
            echo json_encode(['error' => 'maxUses debe ser un entero positivo']);
            exit;
        }

        $result = $this->shortener->shorten($url, $expiresAt, $maxUses, $length);

        if(isset($result['ya_acortada']) && $result['ya_acortada'] === true){
            http_response_code(200);
        } else if(isset($result['error'])){
            http_response_code(400);
        } else {
            http_response_code(201);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }

    // GET /redirect/{short_url}
    public function redirect($short_code) {
        // Llama directamente a la función de redirección
        $this->shortener->redirect($short_code);
        // La función redirect() hace el exit() después de redirigir
    }

    // GET /stats/{short_url}
    public function stats($short_code) {
        $result = $this->shortener->stats($short_code);

        if(isset($result['error'])){
            http_response_code(404);
        } else {
            http_response_code(200);
        }

        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
?>
