<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        // Intentamos una consulta simple
        $version = $db->query('SELECT VERSION()')->fetchColumn();
        jsonResponse([
            "success" => true,
            "message" => "Conexión exitosa a MySQL en Railway",
            "mysql_version" => $version,
            "database" => getenv('MYSQLDATABASE') ?: "manos_unidas (Local)"
        ]);
    } else {
        jsonResponse(["success" => false, "message" => "No se pudo establecer la conexión"], 500);
    }
} catch (Exception $e) {
    jsonResponse(["success" => false, "message" => "Error crítico: " . $e->getMessage()], 500);
}
?>