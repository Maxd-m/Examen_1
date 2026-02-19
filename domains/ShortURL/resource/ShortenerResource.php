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

        //validar formato de url
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['error' => 'URL inválida']);
            exit;
        }
        

        $result = $this->shortener->shorten($url, $expiresAt, $maxUses);

        if(isset($result['error'])){
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
