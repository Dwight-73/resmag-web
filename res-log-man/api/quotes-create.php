<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_id'])) {
  http_response_code(401);
  exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$nombre   = trim($_POST['nombre']   ?? '');
$empresa  = trim($_POST['empresa']  ?? '');
$email    = trim($_POST['email']    ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$servicio = trim($_POST['servicio'] ?? '');
$mensaje  = trim($_POST['mensaje']  ?? '');

if ($nombre === '' || $empresa === '' || $email === '' || $telefono === '' || $servicio === '' || $mensaje === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Correo no válido']);
  exit;
}

try {
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS cotizaciones (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nombre VARCHAR(255) NOT NULL,
      empresa VARCHAR(255) NOT NULL,
      email VARCHAR(255) NOT NULL,
      telefono VARCHAR(64) NOT NULL,
      servicio VARCHAR(255) NOT NULL,
      mensaje TEXT NOT NULL,
      estado VARCHAR(32) NOT NULL DEFAULT 'nuevo',
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  $stmt = $pdo->prepare('INSERT INTO cotizaciones (nombre, empresa, email, telefono, servicio, mensaje) VALUES (:n,:e,:m,:t,:s,:x)');
  $stmt->execute([
    ':n' => $nombre,
    ':e' => $empresa,
    ':m' => $email,
    ':t' => $telefono,
    ':s' => $servicio,
    ':x' => $mensaje,
  ]);

  echo json_encode(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error al registrar']);
}
