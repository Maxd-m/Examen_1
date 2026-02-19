<?php
class Shortener {

    private $conn;

    private $url;
    private $short_url;
    private $length;
    private $exp_date; // optional
    private $max_uses; // optional

    // metadata
    private $created_at;
    private $creator_ip;
    private $uses = 0;

    function __construct($db){
        $this->conn = $db;
        $this->length = 6; // longitud por defecto del código corto
        $this->created_at = date('Y-m-d H:i:s');
        $this->creator_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // POST /shorten
    // Inserta la URL en BD y retorna el short_code
    function shorten($original_url, $exp_date = null, $max_uses = null) {
        $this->url = $original_url;
        $this->exp_date = $exp_date;
        $this->max_uses = $max_uses;

        // Validar URL
        if (!filter_var($this->url, FILTER_VALIDATE_URL)) {
            return ['error' => 'URL inválida'];
        }

        // Evitar acortar ya URLs cortas de tu dominio
        $parsed = parse_url($this->url);
        if (isset($parsed['host']) && $parsed['host'] === $_SERVER['HTTP_HOST']) {
            return ['error' => 'No se pueden acortar URLs de este dominio'];
        }

        // Generar short code
        $this->short_url = $this->create();

        // Insertar en BD
        $sql = "INSERT INTO short_urls (short_code, original_url, created_at, created_ip, expires_at, max_uses)
                VALUES (:short_code, :original_url, :created_at, :created_ip, :expires_at, :max_uses)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':short_code', $this->short_url);
        $stmt->bindParam(':original_url', $this->url);
        $stmt->bindParam(':created_at', $this->created_at);
        $stmt->bindParam(':created_ip', $this->creator_ip);
        $stmt->bindParam(':expires_at', $this->exp_date);
        $stmt->bindParam(':max_uses', $this->max_uses);

        if($stmt->execute()) {
            return ['short_url' => $this->short_url];
        } else {
            return ['error' => 'Error al guardar la URL'];
        }
    }

    // Generar short_code único
    function create() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max_attempts = 5;

        for ($i = 0; $i < $max_attempts; $i++) {
            $code = '';
            for ($j = 0; $j < $this->length; $j++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Verificar que no exista en BD
            $sql = "SELECT id FROM short_urls WHERE short_code = :code LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':code', $code);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return $code;
            }
        }

        throw new Exception("No se pudo generar un código único. Intente nuevamente.");
    }

    // GET /redirect/{short_url}
    function redirect($short_code) {
        // Buscar URL
        $sql = "SELECT * FROM short_urls WHERE short_code = :code AND is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':code', $short_code);
        $stmt->execute();

        if($stmt->rowCount() === 0){
            http_response_code(404);
            echo "URL no encontrada o inactiva.";
            exit;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar expiración
        if($row['expires_at'] && strtotime($row['expires_at']) < time()){
            http_response_code(410);
            echo "URL expirada.";
            exit;
        }

        // Verificar max_uses
        if($row['max_uses'] && $row['visit_count'] >= $row['max_uses']){
            http_response_code(410);
            echo "Límite de usos alcanzado.";
            exit;
        }

        // Registrar visita
        $this->updateUses($row['id']);

        // Redirigir
        header("Location: " . $row['original_url'], true, 302);
        exit;
    }

    // Actualiza contador y registra visita
    function updateUses($short_url_id){
        $visitor_ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $visited_at = date('Y-m-d H:i:s');

        // Insertar visita
        $sql = "INSERT INTO url_visits (short_url_id, visited_at, visitor_ip, user_agent)
                VALUES (:id, :visited_at, :visitor_ip, :user_agent)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $short_url_id);
        $stmt->bindParam(':visited_at', $visited_at);
        $stmt->bindParam(':visitor_ip', $visitor_ip);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->execute();

        // Incrementar contador
        $sql2 = "UPDATE short_urls SET visit_count = visit_count + 1 WHERE id = :id";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bindParam(':id', $short_url_id);
        $stmt2->execute();
    }

    // GET /stats/{short_url}
    function stats($short_code){
        $sql = "SELECT id, original_url, visit_count FROM short_urls WHERE short_code = :code LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':code', $short_code);
        $stmt->execute();

        if($stmt->rowCount() === 0){
            return ['error' => 'URL no encontrada'];
        }

        $url = $stmt->fetch(PDO::FETCH_ASSOC);
        $short_url_id = $url['id'];

        // Vistas por día
        $sql2 = "SELECT DATE(visited_at) as day, COUNT(*) as count
                 FROM url_visits
                 WHERE short_url_id = :id
                 GROUP BY DATE(visited_at)
                 ORDER BY day DESC";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->bindParam(':id', $short_url_id);
        $stmt2->execute();
        $visits_by_day = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // Últimos accesos
        $sql3 = "SELECT visited_at, visitor_ip, user_agent
                 FROM url_visits
                 WHERE short_url_id = :id
                 ORDER BY visited_at DESC
                 LIMIT 10";
        $stmt3 = $this->conn->prepare($sql3);
        $stmt3->bindParam(':id', $short_url_id);
        $stmt3->execute();
        $last_accesses = $stmt3->fetchAll(PDO::FETCH_ASSOC);

        return [
            'short_code' => $short_code,
            'original_url' => $url['original_url'],
            'total_visits' => $url['visit_count'],
            'visits_by_day' => $visits_by_day,
            'last_accesses' => $last_accesses
        ];
    }
}
?>
