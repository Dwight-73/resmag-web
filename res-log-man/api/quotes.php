<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
resmag_cors();
session_start();

function isAdminRole(string $role): bool
{
  $r = strtolower(trim($role));
  return in_array($r, ['admin', 'administrador', 'administrator', 'superadmin', 'root'], true);
}

if (!isAdminRole((string)($_SESSION['rol'] ?? ''))) {
  http_response_code(401);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => 'No autorizado']);
  exit;
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

$estado = isset($_GET['estado']) ? trim((string)$_GET['estado']) : '';

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

  if ($estado !== '') {
    $stmt = $pdo->prepare('SELECT * FROM cotizaciones WHERE estado = :estado ORDER BY id DESC');
    $stmt->execute([':estado' => $estado]);
  } else {
    $stmt = $pdo->query('SELECT * FROM cotizaciones ORDER BY id DESC');
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode(['ok' => true, 'cotizaciones' => $rows]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error al listar cotizaciones']);
}
