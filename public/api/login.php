<?php
/**
 * API de Login/Autenticación
 * Endpoint: /api/login.php
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        login($db);
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

/**
 * Función de login con verificación de password
 */
function login($db) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        jsonResponse(['success' => false, 'message' => 'Datos inválidos'], 400);
    }

    $missing = validateRequired($data, ['email', 'password']);
    if (!empty($missing)) {
        jsonResponse([
            'success' => false,
            'message' => 'Campos requeridos faltantes: ' . implode(', ', $missing)
        ], 400);
    }

    $email = sanitizeInput($data['email']);
    $password = $data['password'];

    try {
        // Buscar persona por email
        $query = "SELECT 
                    p.id,
                    p.nombre_completo,
                    p.email,
                    p.password,
                    p.rol,
                    p.telefono,
                    p.ciudad,
                    p.activo,
                    p.fecha_registro,
                    CASE 
                        WHEN v.id IS NOT NULL THEN 'voluntario'
                        ELSE 'donador'
                    END as tipo_usuario,
                    v.id as id_voluntario,
                    v.disponibilidad,
                    v.estado as estado_voluntario
                FROM personas p
                LEFT JOIN voluntarios v ON v.id_persona = p.id
                WHERE p.email = :email AND p.activo = 1
                LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user) {
            // Verificar password
            if (password_verify($password, $user['password'])) {
                // Password correcto - crear sesión
                jsonResponse([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => [
                        'id' => $user['id'],
                        'nombre' => $user['nombre_completo'],
                        'email' => $user['email'],
                        'rol' => $user['rol'],
                        'telefono' => $user['telefono'],
                        'ciudad' => $user['ciudad'],
                        'tipo' => $user['tipo_usuario'],
                        'id_voluntario' => $user['id_voluntario'],
                        'disponibilidad' => $user['disponibilidad'],
                        'fecha_registro' => $user['fecha_registro']
                    ]
                ]);
            } else {
                // No revelar si el usuario existe o si la contraseña es incorrecta
                jsonResponse([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }
        } else {
            // Mismo mensaje para no revelar si el usuario existe
            jsonResponse([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error al procesar login',
            'error' => $e->getMessage()
        ], 500);
    }
}