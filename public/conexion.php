<?php
$host = 'centerbeam.proxy.rlwy.net';
$port = '45148';
$user = 'root';
$pass = 'LxJDEFdcejMqwJaLhUCmqsJkOzxKqbfC';
$dbname = 'railway';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>