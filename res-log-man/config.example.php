<?php
declare(strict_types=1);

$envDbHost = getenv('DB_HOST');
$envDbDriver = getenv('DB_DRIVER');
$envDbPort = getenv('DB_PORT');
$envDbName = getenv('DB_NAME');
$envDbUser = getenv('DB_USER');
$envDbPass = getenv('DB_PASS');
$envDbSocket = getenv('DB_SOCKET');
$envDsn = getenv('DB_DSN');

if (!defined('DB_HOST')) define('DB_HOST', ($envDbHost !== false && $envDbHost !== '') ? $envDbHost : '127.0.0.1');
if (!defined('DB_DRIVER')) define('DB_DRIVER', ($envDbDriver !== false && $envDbDriver !== '') ? strtolower($envDbDriver) : 'mysql');
if (!defined('DB_PORT')) define('DB_PORT', ($envDbPort !== false && $envDbPort !== '') ? (int)$envDbPort : (DB_DRIVER === 'pgsql' ? 5432 : 3306));
if (!defined('DB_NAME')) define('DB_NAME', ($envDbName !== false && $envDbName !== '') ? $envDbName : 'res-log-man');
if (!defined('DB_USER')) define('DB_USER', ($envDbUser !== false && $envDbUser !== '') ? $envDbUser : 'root');
if (!defined('DB_PASS')) define('DB_PASS', ($envDbPass !== false) ? $envDbPass : '');
if (!defined('DB_SOCKET')) define('DB_SOCKET', ($envDbSocket !== false && $envDbSocket !== '') ? $envDbSocket : '');

if (!defined('DB_DSN')) {
  if ($envDsn !== false && $envDsn !== '') {
    define('DB_DSN', $envDsn);
  } elseif (DB_DRIVER === 'pgsql') {
    define('DB_DSN', 'pgsql:host=' . DB_HOST . ';port=' . (string)DB_PORT . ';dbname=' . DB_NAME);
  } elseif (DB_SOCKET !== '') {
    define('DB_DSN', 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4');
  } else {
    define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . (string)DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4');
  }
}

if (!function_exists('resmag_db_driver')) {
  function resmag_db_driver(): string
  {
    $dsn = defined('DB_DSN') ? (string)DB_DSN : '';
    $pos = strpos($dsn, ':');
    if ($pos === false) {
      return '';
    }
    return strtolower(substr($dsn, 0, $pos));
  }
}
