<?php
/**
 * API de Tipos de Donación
 * Obtiene el catálogo de tipos de donación disponibles
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

try {
    $query = "SELECT 
                id,
                codigo,
                nombre,
                descripcion,
                unidad,
                es_monetaria,
                activo
              FROM tipos_donacion
              WHERE activo = 1
              ORDER BY FIELD(codigo, 'alimentos', 'ropa', 'medicamentos', 'utiles', 'juguetes', 'dinero')";
    
    $stmt = $db->query($query);
    $tipos = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'data' => $tipos
    ]);

} catch (PDOException $e) {
    error_log("Error en tipos-donacion: " . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Error al obtener tipos de donación',
        'error' => $e->getMessage()
    ], 500);
}