<?php
// auth.php - Controlador de Sessão e Verificação de Segurança

require_once __DIR__ . '/session.php';

$is_api = defined('IS_API') && IS_API;
$auth_file = __DIR__ . '/data/auth.enc';

// Verifica se a instalação já foi realizada
if (!file_exists($auth_file)) {
    if ($is_api) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not installed', 'redirect' => 'install.php']);
        exit;
    } else {
        header("Location: install.php");
        exit;
    }
}

// Verifica se o usuário está devidamente autenticado na sessão
if (empty($_SESSION['logged_in'])) {
    if ($is_api) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Unauthorized', 'redirect' => 'login.php']);
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}
