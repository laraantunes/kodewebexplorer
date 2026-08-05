<?php
// share_target.php - Recebe arquivos enviados pelo "Compartilhar" nativo via PWA
require_once('auth.php');
require_once('config.php'); 

$workspace_path = dirname(__DIR__);
if (isset($env['WORKSPACE_PATH']) && trim($env['WORKSPACE_PATH']) !== '') {
    $workspace_path = trim($env['WORKSPACE_PATH']);
}
$resolved_ws = realpath($workspace_path) ?: $workspace_path;
$targetDir = str_replace('\\', '/', $resolved_ws);

$message = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['shared_files'])) {
    if (!is_dir($targetDir)) {
        @mkdir($targetDir, 0755, true);
    }

    $files = $_FILES['shared_files'];
    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'type' => [$files['type']],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']]
        ];
    }
    
    $successCount = 0;
    $errors = [];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = basename($files['name'][$i]);
            $tmpPath = $files['tmp_name'][$i];
            $targetFile = $targetDir . '/' . $fileName;
            
            if (@move_uploaded_file($tmpPath, $targetFile)) {
                $successCount++;
            } else {
                $errors[] = "Falha ao gravar '$fileName'.";
            }
        } else if ($files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "Erro código " . $files['error'][$i] . " no arquivo " . basename($files['name'][$i]);
        }
    }
    
    if ($successCount > 0) {
        $status = "success";
        $message = "$successCount arquivo(s) recebido(s) com sucesso na raiz do Workspace!";
    } else {
        $status = "error";
        $message = "Falha ao receber os arquivos. " . implode(" ", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require 'templates/head.php'; ?>
<style>
body { 
    display: flex; align-items: center; justify-content: center; 
    height: 100dvh; background: var(--bg-primary); 
}
.share-container { 
    text-align: center; background: var(--bg-secondary); padding: 40px; 
    border-radius: 12px; border: 1px solid var(--border-color); 
    width: 90%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.share-icon { font-size: 64px; margin-bottom: 20px; }
.share-title { font-size: 20px; font-weight: 600; margin-bottom: 10px; color: var(--text-highlight); }
.share-msg { color: var(--text-muted); margin-bottom: 30px; font-size: 14px; }
.btn-back { 
    background: var(--accent); color: white; border: none; padding: 12px 24px; 
    border-radius: 8px; font-size: 14px; cursor: pointer; text-decoration: none; 
    display: inline-block; font-weight: 500; transition: 0.2s;
}
.btn-back:hover { box-shadow: 0 0 15px var(--accent-glow); }
</style>
<body>
<div class="share-container">
    <?php if($status === 'success'): ?>
        <div class="share-icon">✅</div>
        <div class="share-title">Upload Concluído!</div>
        <div class="share-msg"><?= htmlspecialchars($message) ?></div>
    <?php elseif($status === 'error'): ?>
        <div class="share-icon">❌</div>
        <div class="share-title">Erro no Upload</div>
        <div class="share-msg"><?= htmlspecialchars($message) ?></div>
    <?php else: ?>
        <div class="share-icon">⚠️</div>
        <div class="share-title">Nenhum Arquivo</div>
        <div class="share-msg">Nenhum arquivo válido foi recebido na requisição.</div>
    <?php endif; ?>
    <a href="index.php" class="btn-back">Voltar ao Explorer</a>
</div>
</body>
</html>
