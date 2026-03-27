<?php
/**
 * API de Dashboard
 * Obtiene estadísticas y datos para el dashboard
 */

require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
}

try {
    // Calcular estadísticas generales directamente
    $estadisticas = [];
    
    // Total de personas
    $stmt = $db->query("SELECT COUNT(*) as total FROM personas WHERE activo = 1");
    $estadisticas['total_personas'] = (int)$stmt->fetch()['total'];
    
    // Total de voluntarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM voluntarios WHERE estado = 'activo'");
    $estadisticas['total_voluntarios'] = (int)$stmt->fetch()['total'];
    
    // Total de donaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM donaciones");
    $estadisticas['total_donaciones'] = (int)$stmt->fetch()['total'];
    
    // Total de lempiras donados
    $stmt = $db->query("SELECT COALESCE(SUM(monto_lempiras), 0) as total FROM detalle_donacion");
    $estadisticas['total_lempiras_donados'] = (float)$stmt->fetch()['total'];
    
    // Total de beneficiarios (personas que recibieron algo)
    $stmt = $db->query("SELECT COUNT(DISTINCT id_persona) as total FROM donaciones WHERE estado IN ('confirmada', 'entregada')");
    $estadisticas['total_beneficiarios'] = (int)$stmt->fetch()['total'];
    
    // Actividades activas (asumiendo que hay una tabla actividades, si no poner 0)
    $stmt = $db->query("SHOW TABLES LIKE 'actividades'");
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM actividades WHERE activo = 1");
        $estadisticas['actividades_activas'] = (int)$stmt->fetch()['total'];
    } else {
        $estadisticas['actividades_activas'] = 0;
    }
    
    // Donaciones pendientes
    $stmt = $db->query("SELECT COUNT(*) as total FROM donaciones WHERE estado = 'pendiente'");
    $estadisticas['donaciones_pendientes'] = (int)$stmt->fetch()['total'];
    
    // Suscriptores newsletter
    $stmt = $db->query("SHOW TABLES LIKE 'suscriptores_newsletter'");
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("SELECT COUNT(*) as total FROM suscriptores_newsletter WHERE activo = 1");
        $estadisticas['total_suscriptores'] = (int)$stmt->fetch()['total'];
    } else {
        $estadisticas['total_suscriptores'] = 0;
    }

    // Últimas donaciones
    $donacionesQuery = "SELECT 
                          d.id,
                          p.nombre_completo,
                          d.estado,
                          d.fecha_donacion,
                          GROUP_CONCAT(td.nombre SEPARATOR ', ') as tipos,
                          SUM(dd.cantidad) as total_items
                        FROM donaciones d
                        JOIN personas p ON p.id = d.id_persona
                        JOIN detalle_donacion dd ON dd.id_donacion = d.id
                        JOIN tipos_donacion td ON td.id = dd.id_tipo_donacion
                        GROUP BY d.id, p.nombre_completo, d.estado, d.fecha_donacion
                        ORDER BY d.fecha_donacion DESC
                        LIMIT 10";
    $donacionesStmt = $db->query($donacionesQuery);
    $ultimasDonaciones = $donacionesStmt->fetchAll();

    // Últimas personas registradas
    $personasQuery = "SELECT 
                        p.id,
                        p.nombre_completo,
                        p.ciudad,
                        p.fecha_registro,
                        CASE 
                            WHEN v.id IS NOT NULL THEN 'Voluntario'
                            ELSE 'Donador'
                        END as tipo
                      FROM personas p
                      LEFT JOIN voluntarios v ON v.id_persona = p.id
                      WHERE p.activo = 1
                      ORDER BY p.fecha_registro DESC
                      LIMIT 10";
    $personasStmt = $db->query($personasQuery);
    $ultimasPersonas = $personasStmt->fetchAll();

    // Donaciones por tipo
    $tiposQuery = "SELECT 
                     td.nombre,
                     td.codigo,
                     COUNT(dd.id) as total_donaciones,
                     SUM(dd.cantidad) as total_cantidad
                   FROM tipos_donacion td
                   LEFT JOIN detalle_donacion dd ON dd.id_tipo_donacion = td.id
                   WHERE td.activo = 1
                   GROUP BY td.id, td.nombre, td.codigo
                   ORDER BY total_donaciones DESC";
    $tiposStmt = $db->query($tiposQuery);
    $donacionesPorTipo = $tiposStmt->fetchAll();

    // Donaciones por estado
    $estadosQuery = "SELECT 
                       estado,
                       COUNT(*) as cantidad
                     FROM donaciones
                     GROUP BY estado";
    $estadosStmt = $db->query($estadosQuery);
    $donacionesPorEstado = $estadosStmt->fetchAll();

    // Voluntarios por disponibilidad
    $disponibilidadQuery = "SELECT 
                              disponibilidad,
                              COUNT(*) as cantidad
                            FROM voluntarios
                            WHERE estado = 'activo'
                            GROUP BY disponibilidad";
    $disponibilidadStmt = $db->query($disponibilidadQuery);
    $voluntariosPorDisponibilidad = $disponibilidadStmt->fetchAll();

    // Últimos suscriptores newsletter
    $newsletterStmt = $db->query("SHOW TABLES LIKE 'suscriptores_newsletter'");
    if ($newsletterStmt->rowCount() > 0) {
        $newsletterQuery = "SELECT 
                              sn.email,
                              sn.fecha_suscripcion,
                              CASE 
                                  WHEN sn.id_persona IS NOT NULL THEN 'Sí'
                                  ELSE 'No'
                              END as vinculado
                            FROM suscriptores_newsletter sn
                            WHERE sn.activo = 1
                            ORDER BY sn.fecha_suscripcion DESC
                            LIMIT 10";
        $newsletterStmt = $db->query($newsletterQuery);
        $ultimosNewsletter = $newsletterStmt->fetchAll();
    } else {
        $ultimosNewsletter = [];
    }

    // Respuesta completa
    jsonResponse([
        'success' => true,
        'data' => [
            'estadisticas' => $estadisticas,
            'ultimas_donaciones' => $ultimasDonaciones,
            'ultimas_personas' => $ultimasPersonas,
            'donaciones_por_tipo' => $donacionesPorTipo,
            'donaciones_por_estado' => $donacionesPorEstado,
            'voluntarios_por_disponibilidad' => $voluntariosPorDisponibilidad,
            'ultimos_newsletter' => $ultimosNewsletter,
            'ultima_actualizacion' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Error al obtener datos del dashboard',
        'error' => $e->getMessage()
    ], 500);
}