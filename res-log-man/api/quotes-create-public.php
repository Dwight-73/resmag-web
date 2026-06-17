<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
resmag_cors();

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

$destinatario = 'logisticaymanteniendo@gmail.com';
$csvPath = __DIR__ . '/../cotizaciones_gmail.csv';

try {
  $isNewFile = !is_file($csvPath) || filesize($csvPath) === 0;
  $fp = fopen($csvPath, 'ab');
  if ($fp === false) {
    throw new RuntimeException('No se pudo abrir el archivo CSV');
  }
  if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    throw new RuntimeException('No se pudo bloquear el archivo CSV');
  }

  if ($isNewFile) {
    fwrite($fp, "sep=;\n");
    fputcsv($fp, ['id', 'created_at', 'nombre', 'empresa', 'email', 'telefono', 'servicio', 'mensaje', 'destinatario_email'], ';');
  }

  $id = (string)time() . '-' . bin2hex(random_bytes(3));
  $createdAt = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');

  fputcsv($fp, [$id, $createdAt, $nombre, $empresa, $email, $telefono, $servicio, $mensaje, $destinatario], ';');

  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);

  $dbOk = false;
  try {
    require_once __DIR__ . '/../db.php';
    $driver = resmag_db_driver();
    $createSql = ($driver === 'pgsql')
      ? "
        CREATE TABLE IF NOT EXISTS cotizaciones (
          id SERIAL PRIMARY KEY,
          nombre VARCHAR(255) NOT NULL,
          empresa VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          telefono VARCHAR(64) NOT NULL,
          servicio VARCHAR(255) NOT NULL,
          mensaje TEXT NOT NULL,
          estado VARCHAR(32) NOT NULL DEFAULT 'nuevo',
          created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        );
      "
      : "
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
      ";
    $pdo->exec($createSql);
    $stmt = $pdo->prepare('INSERT INTO cotizaciones (nombre, empresa, email, telefono, servicio, mensaje) VALUES (:n,:e,:m,:t,:s,:x)');
    $stmt->execute([
      ':n' => $nombre,
      ':e' => $empresa,
      ':m' => $email,
      ':t' => $telefono,
      ':s' => $servicio,
      ':x' => $mensaje,
    ]);
    $dbOk = true;
  } catch (Throwable $e) {
    $dbOk = false;
  }

  echo json_encode(['ok' => true, 'db_ok' => $dbOk]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error al registrar']);
}
