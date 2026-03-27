<?php
/**
 * Configuracion de conexion a MySQL en Railway
 * CONEXION DIRECTA - No requiere variables de entorno
 */

/**
 * Funcion auxiliar para responder con JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Funcion auxiliar para validar datos requeridos
 */
function validateRequired($data, $required_fields) {
    $missing = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $missing[] = $field;
            continue;
        }
        
        // Si es un array, verificar que no esté vacío
        if (is_array($data[$field])) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
            continue;
        }
        
        // Si es string, verificar que no esté vacío después de trim
        if (empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Funcion auxiliar para sanitizar datos
 */
function sanitizeInput($data) {
    if ($data === null) {
        return null;
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Habilitar CORS para permitir peticiones desde el frontend
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Si es una peticion OPTIONS (preflight), responder OK
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// CREDENCIALES RAILWAY - CONEXION DIRECTA
define('DB_HOST', 'centerbeam.proxy.rlwy.net');
define('DB_PORT', '45148');
define('DB_NAME', 'railway');
define('DB_USER', 'root');
define('DB_PASS', 'LxJDEFdcejMqwJaLhUCmqsJkOzxKqbfC');
define('DB_CHARSET', 'utf8mb4');

/**
 * Clase para manejar la conexion a la base de datos
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->port = DB_PORT;
    }

    /**
     * Obtener conexion a la base de datos
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            error_log("Error de conexion: " . $exception->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexion a la base de datos',
                'error' => $exception->getMessage()
            ]);
            exit();
        }

        return $this->conn;
    }

    /**
     * Cerrar conexion
     */
    public function closeConnection() {
        $this->conn = null;
    }
}