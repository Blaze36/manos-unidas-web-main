<?php
/**
 * API de Personas
 * Endpoints CRUD para la tabla personas
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getPersonas($db);
        break;
    case 'POST':
        createPersona($db);
        break;
    case 'PUT':
        updatePersona($db);
        break;
    case 'DELETE':
        deletePersona($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Obtener todas las personas o una específica por ID
 */
function getPersonas($db) {
    try {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($id) {
            // Obtener una persona específica
            $query = "SELECT * FROM personas WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $persona = $stmt->fetch();
            
            if ($persona) {
                jsonResponse(['success' => true, 'data' => $persona]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Persona no encontrada'], 404);
            }
        } else {
            // Obtener todas las personas
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            $query = "SELECT * FROM personas ORDER BY fecha_registro DESC LIMIT :limit OFFSET :offset";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $personas = $stmt->fetchAll();
            
            // Contar total
            $countQuery = "SELECT COUNT(*) as total FROM personas";
            $countStmt = $db->query($countQuery);
            $total = $countStmt->fetch()['total'];
            
            jsonResponse([
                'success' => true,
                'data' => $personas,
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Error en getPersonas: " . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Error al obtener personas', 'error' => $e->getMessage()], 500);
    }
}

/**
 * Crear una nueva persona
 */
function createPersona($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        jsonResponse(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    $missing = validateRequired($data, ['nombre_completo', 'email']);
    if (!empty($missing)) {
        jsonResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }

    try {
        $query = "INSERT INTO personas 
                  (nombre_completo, email, telefono, ciudad, direccion, acepta_privacidad, recibe_newsletter) 
                  VALUES 
                  (:nombre, :email, :telefono, :ciudad, :direccion, :privacidad, :newsletter)";

        $stmt = $db->prepare($query);
        
        $nombre = sanitizeInput($data['nombre_completo']);
        $email = sanitizeInput($data['email']);
        $telefono = isset($data['telefono']) ? sanitizeInput($data['telefono']) : null;
        $ciudad = isset($data['ciudad']) ? sanitizeInput($data['ciudad']) : null;
        $direccion = isset($data['direccion']) ? sanitizeInput($data['direccion']) : null;
        $privacidad = isset($data['acepta_privacidad']) ? 1 : 0;
        $newsletter = isset($data['recibe_newsletter']) ? 1 : 0;

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':ciudad', $ciudad);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':privacidad', $privacidad);
        $stmt->bindParam(':newsletter', $newsletter);

        $stmt->execute();
        
        $newId = $db->lastInsertId();

        jsonResponse([
            'success' => true,
            'message' => 'Persona registrada exitosamente',
            'id' => $newId
        ], 201);

    } catch (PDOException $e) {
        error_log("Error en createPersona: " . $e->getMessage());
        
        // Verificar si es error de email duplicado
        if ($e->getCode() == 23000) {
            jsonResponse([
                'success' => false,
                'message' => 'El email ya está registrado'
            ], 409);
        }
        
        jsonResponse([
            'success' => false,
            'message' => 'Error al crear persona',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Actualizar una persona
 */
function updatePersona($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || !isset($data['id'])) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    try {
        $id = intval($data['id']);
        
        // Construir query dinámicamente basado en campos presentes
        $updates = [];
        $params = [':id' => $id];
        
        if (isset($data['nombre_completo'])) {
            $updates[] = "nombre_completo = :nombre";
            $params[':nombre'] = sanitizeInput($data['nombre_completo']);
        }
        if (isset($data['telefono'])) {
            $updates[] = "telefono = :telefono";
            $params[':telefono'] = sanitizeInput($data['telefono']);
        }
        if (isset($data['ciudad'])) {
            $updates[] = "ciudad = :ciudad";
            $params[':ciudad'] = sanitizeInput($data['ciudad']);
        }
        if (isset($data['direccion'])) {
            $updates[] = "direccion = :direccion";
            $params[':direccion'] = sanitizeInput($data['direccion']);
        }
        if (isset($data['activo'])) {
            $updates[] = "activo = :activo";
            $params[':activo'] = $data['activo'] ? 1 : 0;
        }
        
        if (empty($updates)) {
            jsonResponse(['success' => false, 'message' => 'No hay campos para actualizar'], 400);
        }
        
        $query = "UPDATE personas SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        jsonResponse([
            'success' => true,
            'message' => 'Persona actualizada exitosamente'
        ]);

    } catch (PDOException $e) {
        error_log("Error en updatePersona: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al actualizar persona',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Eliminar una persona (soft delete)
 */
function deletePersona($db) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
    }

    try {
        // Soft delete - marcar como inactivo
        $query = "UPDATE personas SET activo = 0 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            jsonResponse([
                'success' => true,
                'message' => 'Persona desactivada exitosamente'
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Persona no encontrada'
            ], 404);
        }

    } catch (PDOException $e) {
        error_log("Error en deletePersona: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al eliminar persona',
            'error' => $e->getMessage()
        ], 500);
    }
}