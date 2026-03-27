<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once 'conexion.php';

try {
    $sqlFile = __DIR__ . '/database/triggers.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("No se encontró triggers.sql");
    }

    $sql = file_get_contents($sqlFile);

    // ⚠️ NO debe tener DELIMITER

    $pdo->exec($sql);

    echo json_encode([
        "status" => "OK",
        "mensaje" => "Triggers creados correctamente"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => $e->getMessage()
    ]);
}
?>