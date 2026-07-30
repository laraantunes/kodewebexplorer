<?php
// api/upload.php - Módulo de upload para arquivos únicos, múltiplos ou pastas completas
require_once __DIR__ . '/base.php';

try {
    $parent = $_POST['target_path'] ?? $_GET['target_path'] ?? '';
    $absParent = get_absolute_path($parent);
    
    if (!is_dir($absParent)) {
        throw new Exception("A pasta de destino selecionada para o upload não existe no servidor.");
    }
    
    if (empty($_FILES['files']) && empty($_FILES['file'])) {
        throw new Exception("Nenhum arquivo recebido pelo servidor. Verifique o tamanho limite no php.ini (post_max_size/upload_max_filesize).");
    }
    
    $files = $_FILES['files'] ?? $_FILES['file'];
    $paths = $_POST['relative_paths'] ?? []; // Para manter hierarquia ao fazer upload de pasta entera!
    if (is_string($paths)) {
        $paths = json_decode($paths, true) ?: [];
    }
    
    // Normaliza o array caso seja upload simples
    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'type' => [$files['type']],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']]
        ];
    }
    
    $count = count($files['name']);
    $successCount = 0;
    $errors = [];
    
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = basename($files['name'][$i]);
            $tmpPath = $files['tmp_name'][$i];
            
            // Se houver caminho relativo de subpasta (ex: via webkitdirectory ou drag drop de pasta)
            $subFolder = '';
            if (!empty($paths[$i])) {
                $rel = str_replace('\\', '/', $paths[$i]);
                // Remove o nome do arquivo no final para obter a pasta relativa
                $dirPart = dirname($rel);
                if ($dirPart && $dirPart !== '.' && $dirPart !== '/') {
                    $subFolder = '/' . trim($dirPart, '/');
                }
            }
            
            $targetDir = $absParent . $subFolder;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            
            $targetFile = $targetDir . '/' . $fileName;
            
            // Evitar sobrescrever acidentalmente se configurado, ou substituir limpo
            if (@move_uploaded_file($tmpPath, $targetFile)) {
                $successCount++;
            } else {
                $errors[] = "Falha ao gravar '$fileName' no disco do servidor.";
            }
        } else {
            $errors[] = "Erro no arquivo '{$files['name'][$i]}': código erro {$files['error'][$i]}";
        }
    }
    
    if ($successCount > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "$successCount arquivo(s) enviado(s) com sucesso!",
            'errors' => $errors
        ]);
    } else {
        throw new Exception("Falha em todos os arquivos: " . implode(" | ", $errors));
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
