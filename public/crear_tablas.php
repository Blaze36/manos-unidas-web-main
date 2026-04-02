<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once 'conexion.php';

try {
    $sqlFile = __DIR__ . '/database/schema.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró schema.sql");
    }

    $sql = file_get_contents($sqlFile);

    // ⚠️ IMPORTANTE: schema.sql NO debe tener triggers ni DELIMITER

    $pdo->exec($sql);

    echo json_encode([
        "status" => "OK",
        "mensaje" => "Tablas creadas correctamente"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => $e->getMessage()
    ]);
}
?>