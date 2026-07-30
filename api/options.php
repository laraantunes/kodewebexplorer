<?php
// api/options.php - Gerenciamento do Workspace, Senhas e Auto-Atualização via GitHub
require_once __DIR__ . '/base.php';

try {
    switch ($action) {
        case 'get_settings':
            $current_workspace = WORKSPACE_ROOT;
            $is_local_checked = false;
            if (file_exists($rootDir . '/.env')) {
                $envData = @parse_ini_file($rootDir . '/.env');
                if (isset($envData['LOCAL_ENV']) && $envData['LOCAL_ENV'] == '1') {
                    $is_local_checked = true;
                }
            }
            echo json_encode([
                'success' => true,
                'workspace_path' => $current_workspace,
                'is_local' => $is_local_checked,
                'version' => $app_version
            ]);
            break;

        case 'save_settings':
            $new_path = trim($_POST['workspace_path'] ?? '');
            $is_local = isset($_POST['is_local']) && $_POST['is_local'] == '1' ? '1' : '0';
            
            if (empty($new_path)) throw new Exception("O caminho raiz da hospedagem não pode estar em branco.");
            if (!is_dir($new_path)) throw new Exception("A diretoria '$new_path' não existe no servidor.");
            
            $env_content = "LOCAL_ENV=" . $is_local . "\n";
            $env_content .= "WORKSPACE_PATH=\"" . str_replace('\\', '/', $new_path) . "\"\n";
            if (@file_put_contents($rootDir . '/.env', $env_content) !== false) {
                echo json_encode(['success' => true, 'message' => "Ambiente e Workspace atualizados com sucesso!"]);
            } else {
                throw new Exception("Falha ao salvar as configurações em .env.");
            }
            break;

        case 'update_credentials':
            $username = trim($_POST['username'] ?? '');
            $new_password = $_POST['new_password'] ?? '';
            if (empty($username) || empty($new_password)) {
                throw new Exception("Usuário e nova senha são obrigatórios.");
            }
            
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $auth_data = json_encode(['username' => $username, 'password' => $hash]);
            $encrypted = KodeWebEncryption::encrypt($auth_data);
            
            $auth_file = $rootDir . '/data/auth.enc';
            if (@file_put_contents($auth_file, $encrypted) !== false) {
                $_SESSION['username'] = $username;
                echo json_encode(['success' => true, 'message' => "Credenciais e senha criptografada atualizadas com sucesso!"]);
            } else {
                throw new Exception("Falha ao gravar arquivo de autenticação protejido.");
            }
            break;

        case 'update_app':
            // Mecanismo inovador de Auto-Atualização via GitHub
            if (!function_exists('curl_init')) {
                throw new Exception("A biblioteca cURL do PHP é necessária para buscar atualizações do GitHub.");
            }
            
            $repoUrl = 'https://api.github.com/repos/laraantunes/kodewebexplorer/releases/latest';
            $ch = curl_init($repoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'KodeWebExplorer-Updater/' . $app_version);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $apiResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if (!$apiResponse || $httpCode !== 200) {
                // Tenta fallback com mensagem descritiva caso não hajam releases publicadas ainda no GitHub
                throw new Exception("Não foram encontradas novas releases em github.com/laraantunes/kodewebexplorer (HTTP: $httpCode). Certifique-se de publicar um release oficial .zip no GitHub!");
            }
            
            $releaseData = json_decode($apiResponse, true);
            $assets = $releaseData['assets'] ?? [];
            
            $zipUrl = '';
            foreach ($assets as $asset) {
                if (strpos(strtolower($asset['name']), '.zip') !== false) {
                    $zipUrl = $asset['browser_download_url'];
                    break;
                }
            }
            
            if (empty($zipUrl)) {
                // Tenta o arquivo zipball (código fonte do release) caso não tenha asset manual
                $zipUrl = $releaseData['zipball_url'] ?? '';
            }
            if (empty($zipUrl)) {
                throw new Exception("Nenhum arquivo .zip disponível nesta release do GitHub.");
            }
            
            $tempZip = $rootDir . '/data/update-temp.zip';
            
            $ch = curl_init($zipUrl);
            $fp = fopen($tempZip, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'KodeWebExplorer-Updater');
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            fclose($fp);
            
            if ($curlError || filesize($tempZip) < 500) {
                if (file_exists($tempZip)) @unlink($tempZip);
                throw new Exception("Falha de download da release no servidor: $curlError");
            }
            
            $zip = new ZipArchive;
            if ($zip->open($tempZip) === TRUE) {
                $zip->extractTo($rootDir);
                $zip->close();
                @unlink($tempZip);
                echo json_encode([
                    'success' => true, 
                    'message' => 'KodeWeb Explorer foi auto-atualizado com sucesso para a versão ' . ($releaseData['tag_name'] ?? 'mais recente') . ' do GitHub!',
                    'new_version' => $releaseData['tag_name'] ?? ''
                ]);
            } else {
                if (file_exists($tempZip)) @unlink($tempZip);
                throw new Exception("Erro ao tentar descompactar a atualização no diretório do servidor.");
            }
            break;

        default:
            throw new Exception("Ação desconhecida em options.php");
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
