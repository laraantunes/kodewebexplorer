<?php
// api/base.php - Base comum e segurança para todos os endpoints REST do KodeWeb Explorer
header('Content-Type: application/json; charset=utf-8');

define('IS_API', true);
$rootDir = dirname(__DIR__);

require_once $rootDir . '/auth.php';
require_once $rootDir . '/config.php';
require_once $rootDir . '/encryption.php';

// Define a pasta raiz de exploração (por padrão a pasta mãe de onde está o app, ex: htdocs ou www)
$workspace_path = dirname($rootDir);
if (isset($env['WORKSPACE_PATH']) && trim($env['WORKSPACE_PATH']) !== '') {
    $workspace_path = trim($env['WORKSPACE_PATH']);
}
$resolved = realpath($workspace_path);
define('WORKSPACE_ROOT', $resolved !== false ? $resolved : str_replace('\\', '/', $workspace_path));

/**
 * Converte um caminho relativo do Explorer para o caminho absoluto seguro no servidor
 */
function get_absolute_path($relativePath) {
    $relativePath = str_replace(['../', '..\\'], '', $relativePath);
    $relativePath = trim($relativePath, '/\\');
    
    if (empty($relativePath)) {
        return WORKSPACE_ROOT;
    }
    
    $full = WORKSPACE_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    return str_replace('\\', '/', $full);
}

/**
 * Converte um caminho absoluto do servidor de volta para o caminho relativo da interface
 */
function get_relative_path($absPath) {
    $abs = str_replace('\\', '/', $absPath);
    $root = str_replace('\\', '/', WORKSPACE_ROOT);
    
    if ($abs === $root) {
        return '';
    }
    
    $rel = ltrim(substr($abs, strlen($root)), '/');
    return $rel;
}

/**
 * Formatação amigável do tamanho em Bytes para KB / MB / GB
 */
function format_size($bytes) {
    if ($bytes == 0) return "0 B";
    $units = ["B", "KB", "MB", "GB", "TB"];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . " " . $units[$i];
}

/**
 * Leitura de permissões Unix/Windows formadas em octal (ex: 0755, 0644)
 */
function get_file_permissions($filepath) {
    $perms = @fileperms($filepath);
    if ($perms === false) return "0644";
    return substr(sprintf('%o', $perms), -4);
}

// Ação requisitada (GET ou POST)
$action = $_POST['action'] ?? $_GET['action'] ?? '';
