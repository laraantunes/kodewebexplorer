<?php
// public.php - Página pública para visualização e download de arquivos compartilhados
require_once('config.php');

$workspace_path = dirname(__DIR__);
if (isset($env['WORKSPACE_PATH']) && trim($env['WORKSPACE_PATH']) !== '') {
    $workspace_path = trim($env['WORKSPACE_PATH']);
}
$resolved_ws = realpath($workspace_path) ?: $workspace_path;
$workspace_root = str_replace('\\', '/', $resolved_ws);

$code = $_GET['code'] ?? '';
if (empty($code)) {
    die("Link inválido ou expirado (Código ausente).");
}

$shares_file = 'data/shares.json';
$shares = [];
if (file_exists($shares_file)) {
    $shares = json_decode(file_get_contents($shares_file), true) ?: [];
}

if (!isset($shares[$code])) {
    die("Link inválido ou expirado.");
}

$dir = $shares[$code];
$sub = $_GET['sub'] ?? '';
$sub = str_replace(['../', '..\\'], '', $sub);
$sub = trim($sub, '/\\');

$full_rel = $dir . ($dir === '' || $sub === '' ? '' : '/') . $sub;
$full_rel = trim($full_rel, '/');

$targetPath = $workspace_root . ($full_rel === '' ? '' : '/' . $full_rel);
if (!file_exists($targetPath)) {
    die("O arquivo ou pasta não foi encontrado.");
}

// Security: Check if resolved path is inside workspace
$realTarget = realpath($targetPath);
if ($realTarget && strpos(str_replace('\\', '/', $realTarget), $workspace_root) !== 0) {
    die("Acesso negado.");
}
$targetPath = str_replace('\\', '/', $realTarget ?: $targetPath);

$isDir = is_dir($targetPath);

// Handle direct download of a single file
if (isset($_GET['download']) && !$isDir) {
    $mime = mime_content_type($targetPath) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($targetPath) . '"');
    header('Content-Length: ' . filesize($targetPath));
    readfile($targetPath);
    exit;
}

$files = [];
if ($isDir) {
    $items = scandir($targetPath);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $targetPath . '/' . $item;
        $files[] = [
            'name' => $item,
            'is_dir' => is_dir($full),
            'size' => is_file($full) ? filesize($full) : 0,
            'sub_path' => ($sub === '' ? '' : $sub . '/') . $item
        ];
    }
    // Ordenar: pastas primeiro
    usort($files, function($a, $b) {
        if ($a['is_dir'] == $b['is_dir']) {
            return strcasecmp($a['name'], $b['name']);
        }
        return $a['is_dir'] ? -1 : 1;
    });
}

function format_size($bytes) {
    if ($bytes == 0) return "0 B";
    $units = ["B", "KB", "MB", "GB", "TB"];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . " " . $units[$i];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartilhamento - KodeWeb</title>
    <link rel="icon" type="image/svg+xml" href="logo.svg">
    <link rel="apple-touch-icon" href="logo.svg">
    <style>
        :root {
            --bg-primary: #0b0114;
            --bg-secondary: #140523;
            --bg-card: #1c0931;
            --bg-hover: #320f4e;
            --text-primary: #f0f0f0;
            --text-muted: #9a92a6;
            --accent: #bd00ff;
            --border-color: rgba(255, 255, 255, 0.12);
        }
        body {
            font-family: 'Outfit', sans-serif, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            margin: 0; padding: 20px;
        }
        .header {
            text-align: center; margin-bottom: 30px;
        }
        .header h1 {
            color: #fff; margin: 0; font-size: 24px;
        }
        .header p { color: var(--text-muted); margin-top: 5px; word-break: break-all; }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .file-list {
            list-style: none; padding: 0; margin: 0;
        }
        .file-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 16px; border-bottom: 1px solid var(--border-color);
            transition: 0.2s;
        }
        .file-item:last-child { border-bottom: none; }
        .file-item:hover { background: var(--bg-hover); }
        
        .file-info { display: flex; align-items: center; gap: 12px; overflow: hidden; }
        .file-icon { font-size: 24px; flex-shrink: 0; }
        .file-name { font-weight: 500; word-break: break-all; }
        
        .btn-download {
            background: rgba(189, 0, 255, 0.15);
            color: var(--accent);
            border: 1px solid var(--accent);
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .btn-download:hover {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 10px rgba(189,0,255,0.4);
        }
        
        @media (max-width: 600px) {
            .file-item { flex-direction: column; align-items: flex-start; gap: 10px; }
            .btn-download { align-self: stretch; text-align: center; margin-top: 5px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?= $isDir ? '📁 Pasta Compartilhada' : '📄 Arquivo Compartilhado' ?></h1>
        <p><?= htmlspecialchars($full_rel === '' ? 'Raiz' : basename($full_rel)) ?></p>
    </div>
    
    <div class="container">
        <?php if (!$isDir): ?>
            <div style="text-align: center; padding: 30px;">
                <div style="font-size: 64px; margin-bottom: 10px;">📄</div>
                <div style="margin-bottom: 25px; font-size: 18px; font-weight: 500;"><?= htmlspecialchars(basename($targetPath)) ?></div>
                <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($sub) ?>&download=1" class="btn-download" style="padding: 12px 24px; font-size: 16px;">Baixar Arquivo (<?= format_size(filesize($targetPath)) ?>)</a>
            </div>
        <?php elseif (empty($files)): ?>
            <div style="text-align: center; color: var(--text-muted); padding: 30px;">Esta pasta está vazia.</div>
        <?php else: ?>
            <ul class="file-list">
                <?php foreach($files as $f): ?>
                <li class="file-item">
                    <div class="file-info">
                        <span class="file-icon"><?= $f['is_dir'] ? '📂' : '📄' ?></span>
                        <span class="file-name"><?= htmlspecialchars($f['name']) ?></span>
                    </div>
                    <?php if (!$f['is_dir']): ?>
                        <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($f['sub_path']) ?>&download=1" class="btn-download">Baixar (<?= format_size($f['size']) ?>)</a>
                    <?php else: ?>
                        <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($f['sub_path']) ?>" class="btn-download" style="border-color: var(--text-muted); color: var(--text-primary); background: transparent;">Abrir Pasta</a>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
