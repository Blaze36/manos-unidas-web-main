<?php
header("Content-Type: text/plain");
require_once 'config/database.php';

$db = (new Database())->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS voluntarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    ciudad VARCHAR(50),
    disponibilidad VARCHAR(50),
    habilidades TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tipos_donacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    unidad VARCHAR(20),
    es_monetaria BOOLEAN DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS donaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_persona INT,
    estado ENUM('pendiente', 'recibido', 'entregado') DEFAULT 'pendiente',
    observaciones TEXT,
    fecha_donacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_persona) REFERENCES personas(id)
);

CREATE TABLE IF NOT EXISTS detalle_donacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_donacion INT,
    id_tipo_donacion INT,
    cantidad DECIMAL(10,2),
    monto_lempiras DECIMAL(10,2),
    FOREIGN KEY (id_donacion) REFERENCES donaciones(id),
    FOREIGN KEY (id_tipo_donacion) REFERENCES tipos_donacion(id)
);
";

try {
    $db->exec($sql);
    echo "¡Tablas de Manos Unidas creadas exitosamente en Railway!\n";
    
    // Insertar tipos por defecto si la tabla está vacía
    $check = $db->query("SELECT COUNT(*) FROM tipos_donacion")->fetchColumn();
    if ($check == 0) {
        $db->exec("INSERT INTO tipos_donacion (nombre, unidad, es_monetaria) VALUES 
            ('Alimentos', 'Unidades', 0),
            ('Ropa', 'Prendas', 0),
            ('Efectivo', 'Lempiras', 1),
            ('Medicamentos', 'Cajas', 0)");
        echo "Tipos de donación iniciales agregados.";
    }
} catch (Exception $e) {
    echo "Error al crear las tablas: " . $e->getMessage();
}
?>