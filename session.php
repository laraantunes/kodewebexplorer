<?php
// session.php - Gerenciador de sessões e armazenamento seguro do KodeWeb Explorer

$session_path = __DIR__ . '/data/sessions';
if (!is_dir($session_path)) {
    @mkdir($session_path, 0755, true);
    if (!file_exists(__DIR__ . '/data/.htaccess')) {
        @file_put_contents(__DIR__ . '/data/.htaccess', "Require all denied\nDeny from all");
    }
}
session_save_path($session_path);

$lifetime = 2592000; // 30 dias de permanência
ini_set('session.gc_maxlifetime', $lifetime);

require_once __DIR__ . '/config.php';
$is_localhost = (isset($local) && $local) || in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']) || in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1', '::1']);

if ($is_localhost) {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
