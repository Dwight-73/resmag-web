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

$csvPath = __DIR__ . '/../cotizaciones_gmail.csv';
try {
  if (!is_file($csvPath) || filesize($csvPath) === 0) {
    $fp = fopen($csvPath, 'wb');
    if ($fp === false) {
      throw new RuntimeException('No se pudo crear el archivo CSV');
    }
    if (!flock($fp, LOCK_EX)) {
      fclose($fp);
      throw new RuntimeException('No se pudo bloquear el archivo CSV');
    }
    fwrite($fp, "sep=;\n");
    fputcsv($fp, ['id', 'created_at', 'nombre', 'empresa', 'email', 'telefono', 'servicio', 'mensaje', 'destinatario_email'], ';');
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
  }
} catch (Throwable $e) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => 'No se pudo preparar el CSV']);
  exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="cotizaciones_gmail.csv"');
header('X-Content-Type-Options: nosniff');

readfile($csvPath);
