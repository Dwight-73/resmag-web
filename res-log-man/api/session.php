<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';
resmag_cors();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Vary: Cookie');
$logged = isset($_SESSION['usuario_id']);
if (!$logged) {
  echo json_encode([
    'ok' => false,
    'logged' => false,
    'status' => 'no_auth'
  ]);
  exit;
}
echo json_encode([
  'ok' => true,
  'logged' => true,
  'status' => 'ok',
  'id' => (int)$_SESSION['usuario_id'],
  'correo' => $_SESSION['correo'] ?? '',
  'email' => $_SESSION['correo'] ?? '',
  'nombre' => $_SESSION['nombre'] ?? '',
  'name' => $_SESSION['nombre'] ?? '',
  'rol' => $_SESSION['rol'] ?? '',
  'user' => [
    'email' => $_SESSION['correo'] ?? '',
    'name' => $_SESSION['nombre'] ?? '',
    'role' => $_SESSION['rol'] ?? ''
  ]
]);
