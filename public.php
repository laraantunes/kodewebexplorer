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

// Handle direct download of a single file or a folder as ZIP
if (isset($_GET['download'])) {
    if (!$isDir) {
        $mime = mime_content_type($targetPath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($targetPath) . '"');
        header('Content-Length: ' . filesize($targetPath));
        readfile($targetPath);
        exit;
    } else {
        if (!class_exists('ZipArchive')) {
            die("A extensão ZipArchive do PHP não está ativada no seu servidor.");
        }
        $zipName = (basename($targetPath) ? basename($targetPath) : 'raiz') . '_' . date('Y-m-d_His') . '.zip';
        $tmpZip = sys_get_temp_dir() . '/' . uniqid('zip_') . '_' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Falha ao criar o arquivo ZIP temporário.");
        }
        
        if (!function_exists('add_folder_to_zip_public')) {
            function add_folder_to_zip_public($zip, $absFolder, $zipFolder) {
                $zip->addEmptyDir($zipFolder);
                $files = @scandir($absFolder) ?: [];
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $full = "$absFolder/$file";
                    $zipRel = "$zipFolder/$file";
                    if (is_dir($full)) {
                        add_folder_to_zip_public($zip, $full, $zipRel);
                    } else {
                        $zip->addFile($full, $zipRel);
                    }
                }
            }
        }
        
        add_folder_to_zip_public($zip, $targetPath, basename($targetPath) ?: 'raiz');
        $zip->close();
        
        if (file_exists($tmpZip)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipName . '"');
            header('Content-Length: ' . filesize($tmpZip));
            readfile($tmpZip);
            @unlink($tmpZip);
            exit;
        } else {
            die("Erro: o arquivo zip gerado estava vazio.");
        }
    }
}

if (isset($_GET['serve']) && !$isDir) {
    $mime = mime_content_type($targetPath) ?: 'application/octet-stream';
    if (strpos($mime, 'text/') === 0 || in_array(pathinfo($targetPath, PATHINFO_EXTENSION), ['js', 'json', 'xml', 'md', 'env', 'css', 'php', 'sql', 'py'])) {
        $mime = 'text/plain; charset=utf-8';
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($targetPath));
    readfile($targetPath);
    exit;
}

if (isset($_GET['read_file']) && !$isDir) {
    header('Content-Type: application/json; charset=utf-8');
    $content = file_get_contents($targetPath);
    echo json_encode(['success' => true, 'content' => $content]);
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
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <!-- CDNs: Ace Editor, SheetJS, Mammoth.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.8.0/mammoth.browser.min.js" referrerpolicy="no-referrer"></script>
    <script>
        const PUBLIC_SHARE_CODE = <?= json_encode($code) ?>;
        const APP_VERSION = "public-mode";
        function basename(str) { return str.split('/').pop(); }
        function strToExt(ext) { return (ext || '').toString().toLowerCase(); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    </script>
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
            overflow: auto !important;
            height: auto !important;
            display: block !important;
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
            box-sizing: border-box;
            text-align: center;
        }
        .file-item .btn-download {
            width: 155px;
        }
        .btn-download:hover {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 0 10px rgba(189,0,255,0.4);
        }
        
        .btn-wrapper {
            display: flex;
            gap: 8px;
        }
        @media (max-width: 600px) {
            .file-item { flex-direction: column; align-items: flex-start; gap: 10px; }
            .btn-wrapper { width: 100%; flex-direction: column; gap: 6px; margin-top: 5px; }
            .btn-download { text-align: center; margin-top: 0; }
            .file-item .btn-download { width: 100%; }
        }
        
        /* Focus state for keyboard navigation */
        .file-item.kb-focused {
            background: var(--bg-hover);
            outline: 1px solid var(--accent);
            border-radius: 4px;
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
                <div class="btn-wrapper" style="justify-content: center; margin-top: 15px; flex-wrap: wrap;">
                    <button onclick="openFileViewer('<?= addslashes($sub) ?>', '<?= addslashes(basename($targetPath)) ?>')" class="btn-download" style="padding: 12px 24px; font-size: 16px; background:transparent; color:var(--text-primary); border-color:var(--border-color); cursor:pointer;">Visualizar</button>
                    <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($sub) ?>&download=1" class="btn-download" style="padding: 12px 24px; font-size: 16px;">Baixar Arquivo (<?= format_size(filesize($targetPath)) ?>)</a>
                </div>
            </div>
        <?php elseif (empty($files)): ?>
            <div style="text-align: center; color: var(--text-muted); padding: 30px;">Esta pasta está vazia.</div>
        <?php else: ?>
            <ul class="file-list">
                <?php if ($sub !== ''): 
                    $sub_normalized = str_replace('\\', '/', $sub);
                    $parent_sub = dirname($sub_normalized);
                    if ($parent_sub === '.' || $parent_sub === '/' || $parent_sub === '\\') {
                        $parent_sub = '';
                    }
                ?>
                <li class="file-item" style="padding: 0;">
                    <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($parent_sub) ?>" style="display: flex; align-items: center; gap: 12px; width: 100%; padding: 12px 16px; text-decoration: none; color: inherit;">
                        <span class="file-icon">⬅️</span>
                        <span class="file-name" style="font-weight: 600;">Voltar</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php foreach($files as $f): ?>
                <li class="file-item">
                    <?php if ($f['is_dir']): ?>
                        <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($f['sub_path']) ?>" style="display: flex; align-items: center; gap: 12px; flex: 1; text-decoration: none; color: inherit;">
                            <span class="file-icon">📂</span>
                            <span class="file-name"><?= htmlspecialchars($f['name']) ?></span>
                        </a>
                        <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($f['sub_path']) ?>&download=1" class="btn-download" style="border-color: var(--text-muted); color: var(--text-primary); background: transparent;">Baixar ZIP</a>
                    <?php else: ?>
                        <a href="javascript:void(0)" onclick="openFileViewer('<?= addslashes($f['sub_path']) ?>', '<?= addslashes($f['name']) ?>')" style="display: flex; align-items: center; gap: 12px; flex: 1; text-decoration: none; color: inherit;">
                            <span class="file-icon">📄</span>
                            <span class="file-name"><?= htmlspecialchars($f['name']) ?></span>
                        </a>
                        <div class="btn-wrapper">
                            <button onclick="openFileViewer('<?= addslashes($f['sub_path']) ?>', '<?= addslashes($f['name']) ?>')" class="btn-download" style="background:transparent; color:var(--text-primary); border-color:var(--border-color); cursor:pointer;">Visualizar</button>
                            <a href="public.php?code=<?= urlencode($code) ?>&sub=<?= urlencode($f['sub_path']) ?>&download=1" class="btn-download">Baixar (<?= format_size($f['size']) ?>)</a>
                        </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Modais e Toast -->
    <div id="toast-container" style="position: fixed; bottom: 70px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px; pointer-events: none;"></div>
    <?php require 'templates/modals.php'; ?>
    
    <script src="app/state.js"></script>
    <script src="app/viewer.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = Array.from(document.querySelectorAll('.file-item'));
            if (items.length === 0) return;
            
            let currentIndex = -1;

            document.addEventListener('keydown', (e) => {
                // Se a modal estiver aberta, não intercepte a navegação da lista
                const modal = document.getElementById('modal-viewer');
                if (modal && modal.classList.contains('active')) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (currentIndex < items.length - 1) {
                        if (currentIndex >= 0) items[currentIndex].classList.remove('kb-focused');
                        currentIndex++;
                        items[currentIndex].classList.add('kb-focused');
                        items[currentIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (currentIndex > 0) {
                        items[currentIndex].classList.remove('kb-focused');
                        currentIndex--;
                        items[currentIndex].classList.add('kb-focused');
                        items[currentIndex].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                    }
                } else if (e.key === 'Enter') {
                    if (currentIndex >= 0 && currentIndex < items.length) {
                        e.preventDefault();
                        // Simula o clique no primeiro elemento clicável (.file-info a, ou a)
                        const link = items[currentIndex].querySelector('a');
                        if (link) {
                            link.click();
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
