<?php
/**
 * API de Voluntarios
 * Maneja el registro y gestión de voluntarios
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getVoluntarios($db);
        break;
    case 'POST':
        createVoluntario($db);
        break;
    case 'PUT':
        updateVoluntario($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener voluntarios
 */
function getVoluntarios($db) {
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            // Obtener un voluntario específico
            $query = "SELECT 
                        v.id,
                        v.id_persona,
                        p.nombre_completo,
                        p.email,
                        p.telefono,
                        p.ciudad,
                        v.disponibilidad,
                        v.habilidades,
                        v.como_conocio,
                        v.estado,
                        v.fecha_inicio
                      FROM voluntarios v
                      JOIN personas p ON p.id = v.id_persona
                      WHERE v.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $voluntario = $stmt->fetch();
            
            if ($voluntario) {
                jsonResponse(['success' => true, 'data' => $voluntario]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Voluntario no encontrado'], 404);
            }
            
        } else {
            // Obtener todos los voluntarios
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            $estado = isset($_GET['estado']) ? sanitizeInput($_GET['estado']) : null;
            
            $whereClause = $estado ? "WHERE v.estado = :estado" : "";
            
            $query = "SELECT 
                        v.id,
                        v.id_persona,
                        p.nombre_completo,
                        p.email,
                        p.telefono,
                        p.ciudad,
                        v.disponibilidad,
                        v.estado,
                        v.fecha_inicio,
                        p.fecha_registro
                      FROM voluntarios v
                      JOIN personas p ON p.id = v.id_persona
                      $whereClause
                      ORDER BY p.fecha_registro DESC
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $db->prepare($query);
            if ($estado) {
                $stmt->bindParam(':estado', $estado);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $voluntarios = $stmt->fetchAll();
            
            // Contar total
            $countQuery = "SELECT COUNT(*) as total FROM voluntarios" . ($estado ? " WHERE estado = :estado" : "");
            $countStmt = $db->prepare($countQuery);
            if ($estado) {
                $countStmt->bindParam(':estado', $estado);
            }
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            jsonResponse([
                'success' => true,
                'data' => $voluntarios,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Error en getVoluntarios: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Error al obtener voluntarios', 'error' => $e->getMessage()], 500);
    }
}

/**
 * Crear nuevo voluntario
 * Primero crea la persona, luego el voluntario
 */
function createVoluntario($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        jsonResponse(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    $missing = validateRequired($data, ['nombre_completo', 'email', 'telefono', 'ciudad', 'disponibilidad']);
    if (!empty($missing)) {
        jsonResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }

    try {
        $db->beginTransaction();

        // Primero crear la persona
        $queryPersona = "INSERT INTO personas 
                        (nombre_completo, email, telefono, ciudad, direccion, acepta_privacidad, recibe_newsletter) 
                        VALUES 
                        (:nombre, :email, :telefono, :ciudad, :direccion, :privacidad, :newsletter)";

        $stmtPersona = $db->prepare($queryPersona);
        
        $nombre = sanitizeInput($data['nombre_completo']);
        $email = sanitizeInput($data['email']);
        $telefono = sanitizeInput($data['telefono']);
        $ciudad = sanitizeInput($data['ciudad']);
        $direccion = isset($data['direccion']) ? sanitizeInput($data['direccion']) : null;
        $privacidad = isset($data['acepta_privacidad']) ? 1 : 0;
        $newsletter = isset($data['recibe_newsletter']) ? 1 : 0;

        $stmtPersona->bindParam(':nombre', $nombre);
        $stmtPersona->bindParam(':email', $email);
        $stmtPersona->bindParam(':telefono', $telefono);
        $stmtPersona->bindParam(':ciudad', $ciudad);
        $stmtPersona->bindParam(':direccion', $direccion);
        $stmtPersona->bindParam(':privacidad', $privacidad);
        $stmtPersona->bindParam(':newsletter', $newsletter);

        $stmtPersona->execute();
        $personaId = $db->lastInsertId();

        // Crear el voluntario
        $queryVoluntario = "INSERT INTO voluntarios 
                           (id_persona, disponibilidad, habilidades, como_conocio, fecha_inicio) 
                           VALUES 
                           (:id_persona, :disponibilidad, :habilidades, :conocio, CURDATE())";

        $stmtVoluntario = $db->prepare($queryVoluntario);
        
        $disponibilidad = sanitizeInput($data['disponibilidad']);
        $habilidades = isset($data['habilidades']) ? sanitizeInput($data['habilidades']) : null;
        $conocio = isset($data['como_conocio']) ? sanitizeInput($data['como_conocio']) : null;

        $stmtVoluntario->bindParam(':id_persona', $personaId);
        $stmtVoluntario->bindParam(':disponibilidad', $disponibilidad);
        $stmtVoluntario->bindParam(':habilidades', $habilidades);
        $stmtVoluntario->bindParam(':conocio', $conocio);
        $stmtVoluntario->execute();

        $voluntarioId = $db->lastInsertId();

        // Si marcó newsletter, agregar a suscriptores
        if ($newsletter) {
            $queryNews = "INSERT INTO suscriptores_newsletter (email, id_persona) 
                         VALUES (:email, :id_persona)
                         ON DUPLICATE KEY UPDATE id_persona = :id_persona";
            $stmtNews = $db->prepare($queryNews);
            $stmtNews->bindParam(':email', $email);
            $stmtNews->bindParam(':id_persona', $personaId);
            $stmtNews->execute();
        }

        $db->commit();

        jsonResponse([
            'success' => true,
            'message' => 'Voluntario registrado exitosamente',
            'id_persona' => $personaId,
            'id_voluntario' => $voluntarioId
        ], 201);

    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error en createVoluntario: " . $e->getMessage());
        
        // Verificar si es error de email duplicado
        if ($e->getCode() == 23000) {
            jsonResponse([
                'success' => false,
                'message' => 'El email ya está registrado'
            ], 409);
        }
        
        jsonResponse([
            'success' => false,
            'message' => 'Error al crear voluntario',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Actualizar voluntario
 */
function updateVoluntario($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    try {
        $id = intval($data['id']);
        $updates = [];
        $params = [':id' => $id];
        
        if (isset($data['disponibilidad'])) {
            $updates[] = "disponibilidad = :disponibilidad";
            $params[':disponibilidad'] = sanitizeInput($data['disponibilidad']);
        }
        if (isset($data['habilidades'])) {
            $updates[] = "habilidades = :habilidades";
            $params[':habilidades'] = sanitizeInput($data['habilidades']);
        }
        if (isset($data['estado'])) {
            $updates[] = "estado = :estado";
            $params[':estado'] = sanitizeInput($data['estado']);
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'No hay campos para actualizar'], 400);
        }
        
        $query = "UPDATE voluntarios SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        jsonResponse([
            'success' => true,
            'message' => 'Voluntario actualizado exitosamente'
        ]);

    } catch (PDOException $e) {
        error_log("Error en updateVoluntario: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al actualizar voluntario',
            'error' => $e->getMessage()
        ], 500);
    }
}