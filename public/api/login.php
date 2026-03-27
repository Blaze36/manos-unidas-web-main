<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once 'config/database.php';
$db = (new Database())->getConnection();

$data = json_decode(file_get_contents("php://input"), true);

if (!empty($data['email'])) {
    try {
        // Buscamos si el email existe en la tabla personas
        $query = "SELECT id, nombre_completo, email FROM personas WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':email', sanitizeInput($data['email']));
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            jsonResponse([
                "success" => true,
                "message" => "Sesión iniciada correctamente",
                "user" => $user
            ]);
        } else {
            jsonResponse(["success" => false, "message" => "El correo no está registrado"], 401);
        }
    } catch (Exception $e) {
        jsonResponse(["success" => false, "message" => $e->getMessage()], 500);
    }
} else {
    jsonResponse(["success" => false, "message" => "Email requerido"], 400);
}
?>