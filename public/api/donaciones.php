<?php
/**
 * API de Donaciones
 * Maneja donaciones y sus detalles
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getDonaciones($db);
        break;
    case 'POST':
        createDonacion($db);
        break;
    case 'PUT':
        updateDonacion($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener donaciones con sus detalles
 */
function getDonaciones($db) {
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            // Obtener una donación específica con detalles
            $query = "SELECT 
                        d.id,
                        d.id_persona,
                        p.nombre_completo,
                        p.email,
                        p.telefono,
                        p.direccion,
                        d.estado,
                        d.observaciones,
                        d.fecha_donacion,
                        d.fecha_actualizacion
                      FROM donaciones d
                      JOIN personas p ON p.id = d.id_persona
                      WHERE d.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $donacion = $stmt->fetch();
            
            if ($donacion) {
                // Obtener detalles de la donación
                $queryDetalle = "SELECT 
                                    dd.id,
                                    dd.id_tipo_donacion,
                                    td.codigo,
                                    td.nombre as tipo_nombre,
                                    td.unidad,
                                    td.es_monetaria,
                                    dd.cantidad,
                                    dd.monto_lempiras,
                                    dd.notas
                                 FROM detalle_donacion dd
                                 JOIN tipos_donacion td ON td.id = dd.id_tipo_donacion
                                 WHERE dd.id_donacion = :id";
                
                $stmtDetalle = $db->prepare($queryDetalle);
                $stmtDetalle->bindParam(':id', $id);
                $stmtDetalle->execute();
                $donacion['detalles'] = $stmtDetalle->fetchAll();
                
                jsonResponse(['success' => true, 'data' => $donacion]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Donación no encontrada'], 404);
            }
            
        } else {
            // Obtener todas las donaciones
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            $estado = isset($_GET['estado']) ? sanitizeInput($_GET['estado']) : null;
            
            $whereClause = $estado ? "WHERE d.estado = :estado" : "";
            
            $query = "SELECT 
                        d.id,
                        d.id_persona,
                        p.nombre_completo,
                        p.email,
                        p.telefono,
                        d.estado,
                        d.fecha_donacion,
                        COUNT(dd.id) as total_items
                      FROM donaciones d
                      JOIN personas p ON p.id = d.id_persona
                      LEFT JOIN detalle_donacion dd ON dd.id_donacion = d.id
                      $whereClause
                      GROUP BY d.id
                      ORDER BY d.fecha_donacion DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($query);
            if ($estado) {
                $stmt->bindParam(':estado', $estado);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $donaciones = $stmt->fetchAll();
            
            // Contar total
            $countQuery = "SELECT COUNT(*) as total FROM donaciones" . ($estado ? " WHERE estado = :estado" : "");
            $countStmt = $db->prepare($countQuery);
            if ($estado) {
                $countStmt->bindParam(':estado', $estado);
            }
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            jsonResponse([
                'success' => true,
                'data' => $donaciones,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Error en getDonaciones: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Error al obtener donaciones', 'error' => $e->getMessage()], 500);
    }
}

/**
 * Crear nueva donación con sus detalles
 */
function createDonacion($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        jsonResponse(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    $missing = validateRequired($data, ['id_persona', 'detalles']);
    if (!empty($missing)) {
        jsonResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }

    if (!is_array($data['detalles']) || empty($data['detalles'])) {
        jsonResponse([
            'success' => false,
            'message' => 'Debe incluir al menos un artículo en la donación'
        ], 400);
    }

    try {
        $db->beginTransaction();

        // Crear la donación principal
        $query = "INSERT INTO donaciones (id_persona, estado, observaciones) 
                  VALUES (:id_persona, :estado, :observaciones)";
        
        $stmt = $db->prepare($query);
        
        $idPersona = intval($data['id_persona']);
        $estado = isset($data['estado']) ? sanitizeInput($data['estado']) : 'pendiente';
        $observaciones = isset($data['observaciones']) ? sanitizeInput($data['observaciones']) : null;
        
        $stmt->bindParam(':id_persona', $idPersona);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->execute();
        
        $donacionId = $db->lastInsertId();

        // Insertar detalles de la donación
        $queryDetalle = "INSERT INTO detalle_donacion 
                        (id_donacion, id_tipo_donacion, cantidad, monto_lempiras, notas)
                        VALUES (:id_donacion, :id_tipo, :cantidad, :monto, :notas)";
        
        $stmtDetalle = $db->prepare($queryDetalle);

        foreach ($data['detalles'] as $detalle) {
            if (!isset($detalle['id_tipo_donacion']) || !isset($detalle['cantidad'])) {
                $db->rollBack();
                jsonResponse([
                    'success' => false,
                    'message' => 'Cada detalle debe incluir id_tipo_donacion y cantidad'
                ], 400);
            }

            $idTipo = intval($detalle['id_tipo_donacion']);
            $cantidad = floatval($detalle['cantidad']);
            $monto = isset($detalle['monto_lempiras']) ? floatval($detalle['monto_lempiras']) : null;
            $notas = isset($detalle['notas']) ? sanitizeInput($detalle['notas']) : null;

            $stmtDetalle->bindParam(':id_donacion', $donacionId);
            $stmtDetalle->bindParam(':id_tipo', $idTipo);
            $stmtDetalle->bindParam(':cantidad', $cantidad);
            $stmtDetalle->bindParam(':monto', $monto);
            $stmtDetalle->bindParam(':notas', $notas);
            $stmtDetalle->execute();
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Donación registrada exitosamente',
            'id' => $donacionId
        ], 201);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error en createDonacion: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al crear donación',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Actualizar estado de donación
 */
function updateDonacion($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    try {
        $id = intval($data['id']);
        $updates = [];
        $params = [':id' => $id];
        
        if (isset($data['estado'])) {
            $updates[] = "estado = :estado";
            $params[':estado'] = sanitizeInput($data['estado']);
        }
        if (isset($data['observaciones'])) {
            $updates[] = "observaciones = :observaciones";
            $params[':observaciones'] = sanitizeInput($data['observaciones']);
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'No hay campos para actualizar'], 400);
        }
        
        $query = "UPDATE donaciones SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        jsonResponse([
            'success' => true,
            'message' => 'Donación actualizada exitosamente'
        ]);

    } catch (PDOException $e) {
        error_log("Error en updateDonacion: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al actualizar donación',
            'error' => $e->getMessage()
        ], 500);
    }
}