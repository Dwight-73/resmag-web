<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
session_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
require_once __DIR__ . '/config.php';
header('Location: ' . FRONTEND_BASE . 'index.html', true, 302);
exit;
