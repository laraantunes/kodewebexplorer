<?php
// api/share_link.php - Gera um hash único para um caminho e o salva no JSON
require_once('../auth.php'); // Requer autenticação
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$path = $input['path'] ?? '';

if ($path === null) {
    echo json_encode(['success' => false, 'message' => 'Caminho inválido']);
    exit;
}

// Limpa o path
$path = str_replace(['../', '..\\'], '', $path);
$path = trim($path, '/\\');

$shares_file = '../data/shares.json';
$shares = [];

if (file_exists($shares_file)) {
    $content = file_get_contents($shares_file);
    $shares = json_decode($content, true) ?: [];
}

// Verifica se já existe um hash para este path
$existing_hash = null;
foreach ($shares as $hash => $stored_path) {
    if ($stored_path === $path) {
        $existing_hash = $hash;
        break;
    }
}

if ($existing_hash) {
    echo json_encode(['success' => true, 'hash' => $existing_hash, 'path' => $path]);
    exit;
}

// Se não existir, gera um novo hash
$new_hash = substr(bin2hex(random_bytes(16)), 0, 16); // hash de 16 caracteres
$shares[$new_hash] = $path;

if (file_put_contents($shares_file, json_encode($shares, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true, 'hash' => $new_hash, 'path' => $path]);
} else {
    echo json_encode(['success' => false, 'message' => 'Falha ao salvar o link de compartilhamento. Verifique permissões da pasta data.']);
}
