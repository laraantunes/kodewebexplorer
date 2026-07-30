<?php
// api/download.php - Stream de download para arquivo único ou geração dinâmica de .zip
require_once __DIR__ . '/base.php';

try {
    $items = $_GET['items'] ?? $_POST['items'] ?? [];
    if (is_string($items)) {
        $items = json_decode($items, true) ?: [$items];
    }
    
    if (empty($items)) {
        die("Nenhum arquivo ou pasta especificado para download.");
    }

    // Caso 1: Se for APENAS 1 item e for um ARQUIVO simples -> Download Direto sem ZIP
    if (count($items) === 1) {
        $singlePath = get_absolute_path($items[0]);
        if (file_exists($singlePath) && !is_dir($singlePath)) {
            $fileName = basename($singlePath);
            $mime = @mime_content_type($singlePath) ?: 'application/octet-stream';
            
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($singlePath));
            readfile($singlePath);
            exit;
        }
    }

    // Caso 2: Múltiplos arquivos ou Pastas -> Compactar em arquivo .zip temporário
    if (!class_exists('ZipArchive')) {
        die("A extensão ZipArchive do PHP não está ativada no seu servidor.");
    }

    $zipName = 'kodeweb_files_' . date('Y-m-d_His') . '.zip';
    $tmpZip = $rootDir . '/data/' . $zipName;
    
    $zip = new ZipArchive();
    if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Falha ao criar o arquivo ZIP temporário no servidor.");
    }
    
    foreach ($items as $relPath) {
        $absPath = get_absolute_path($relPath);
        if (!file_exists($absPath)) continue;
        
        if (is_dir($absPath)) {
            add_folder_to_zip($zip, $absPath, basename($absPath));
        } else {
            $zip->addFile($absPath, basename($absPath));
        }
    }
    
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

} catch (Exception $e) {
    die("Erro no download: " . htmlspecialchars($e->getMessage()));
}

function add_folder_to_zip($zip, $absFolder, $zipFolder) {
    $zip->addEmptyDir($zipFolder);
    $files = @scandir($absFolder) ?: [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $full = "$absFolder/$file";
        $zipRel = "$zipFolder/$file";
        if (is_dir($full)) {
            add_folder_to_zip($zip, $full, $zipRel);
        } else {
            $zip->addFile($full, $zipRel);
        }
    }
}
