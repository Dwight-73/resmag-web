<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
resmag_cors();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Vary: Cookie');

$correo = trim((string)($_POST['correo'] ?? $_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($correo === '' || $password === '') {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
  exit;
}

function isAdminRole(string $role): bool
{
  $r = strtolower(trim($role));
  return in_array($r, ['admin', 'administrador', 'administrator', 'superadmin', 'root'], true);
}

try {
  $driver = resmag_db_driver();
  $createSql = ($driver === 'pgsql')
    ? "
      CREATE TABLE IF NOT EXISTS usuarios (
        id SERIAL PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        correo VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol VARCHAR(32) NOT NULL DEFAULT 'user',
        created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
      );
    "
    : "
      CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        correo VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol VARCHAR(32) NOT NULL DEFAULT 'user',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
  $pdo->exec($createSql);

  if ($driver === 'pgsql') {
    $colsStmt = $pdo->prepare("
      SELECT column_name
      FROM information_schema.columns
      WHERE table_schema = current_schema()
        AND table_name = 'usuarios'
        AND column_name IN ('password_hash','password','rol')
    ");
    $colsStmt->execute();
  } else {
    $colsStmt = $pdo->prepare("
      SELECT COLUMN_NAME
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = :db
        AND TABLE_NAME = 'usuarios'
        AND COLUMN_NAME IN ('password_hash','password','rol')
    ");
    $colsStmt->execute([':db' => DB_NAME]);
  }
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN);

  $passwordColumn = in_array('password_hash', $cols, true) ? 'password_hash' : (in_array('password', $cols, true) ? 'password' : null);
  if ($passwordColumn === null) {
    throw new RuntimeException('Tabla usuarios sin columna de contraseña');
  }
  $hasRol = in_array('rol', $cols, true);

  $adminEmail = defined('ADMIN_EMAIL') ? (string)ADMIN_EMAIL : 'admin@resmag.local';
  $adminPassword = defined('ADMIN_PASSWORD') ? (string)ADMIN_PASSWORD : 'Admin123!';
  $adminName = defined('ADMIN_NAME') ? (string)ADMIN_NAME : 'Administrador';

  if ($driver === 'pgsql') {
    $insertSql = $hasRol
      ? "INSERT INTO usuarios (nombre, correo, {$passwordColumn}, rol) VALUES (:n,:c,:p,:r)
         ON CONFLICT (correo) DO UPDATE SET nombre = EXCLUDED.nombre, {$passwordColumn} = EXCLUDED.{$passwordColumn}, rol = EXCLUDED.rol"
      : "INSERT INTO usuarios (nombre, correo, {$passwordColumn}) VALUES (:n,:c,:p)
         ON CONFLICT (correo) DO UPDATE SET nombre = EXCLUDED.nombre, {$passwordColumn} = EXCLUDED.{$passwordColumn}";
  } else {
    $insertSql = $hasRol
      ? "INSERT INTO usuarios (nombre, correo, {$passwordColumn}, rol) VALUES (:n,:c,:p,:r)
         ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), {$passwordColumn} = VALUES({$passwordColumn}), rol = VALUES(rol)"
      : "INSERT INTO usuarios (nombre, correo, {$passwordColumn}) VALUES (:n,:c,:p)
         ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), {$passwordColumn} = VALUES({$passwordColumn})";
  }

  $ins = $pdo->prepare($insertSql);
  $params = [
    ':n' => $adminName,
    ':c' => $adminEmail,
    ':p' => password_hash($adminPassword, PASSWORD_DEFAULT),
  ];
  if ($hasRol) {
    $params[':r'] = 'admin';
  }
  $ins->execute($params);

  $selectSql = $hasRol
    ? "SELECT id, nombre, correo, {$passwordColumn} AS password_hash, rol FROM usuarios WHERE correo = :correo LIMIT 1"
    : "SELECT id, nombre, correo, {$passwordColumn} AS password_hash, 'user' AS rol FROM usuarios WHERE correo = :correo LIMIT 1";
  $stmt = $pdo->prepare($selectSql);
  $stmt->execute([':correo' => $correo]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Credenciales inválidas']);
    exit;
  }

  $stored = (string)($user['password_hash'] ?? '');
  $ok = $stored !== '' && password_verify($password, $stored);
  if (!$ok && $stored !== '' && hash_equals($stored, $password)) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE usuarios SET {$passwordColumn} = :p WHERE id = :id");
    $upd->execute([':p' => $newHash, ':id' => (int)$user['id']]);
    $ok = true;
  }
  if (!$ok) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Credenciales inválidas']);
    exit;
  }

  $sessionRol = isAdminRole((string)($user['rol'] ?? '')) ? 'admin' : 'user';

  $_SESSION['usuario_id'] = (int)$user['id'];
  $_SESSION['correo'] = (string)$user['correo'];
  $_SESSION['nombre'] = (string)$user['nombre'];
  $_SESSION['rol'] = $sessionRol;

  echo json_encode([
    'ok' => true,
    'id' => (int)$user['id'],
    'correo' => (string)$user['correo'],
    'nombre' => (string)$user['nombre'],
    'rol' => $sessionRol,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error al iniciar sesión']);
}
