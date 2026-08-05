<?php
// install.php - Wizard de Instalação e Configuração do KodeWeb Explorer

require_once __DIR__ . '/session.php';
$auth_file = __DIR__ . '/data/auth.enc';
$is_installed = file_exists($auth_file);
$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'encryption.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $workspace = trim($_POST['workspace'] ?? '');
    $is_local = isset($_POST['is_local']) ? '1' : '0';

    if (empty($username) || empty($password)) {
        $message = 'Usuário e senha são obrigatórios.';
        $message_type = 'error';
    } else {
        $data_dir = __DIR__ . '/data';
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }

        // Proteger a pasta data com .htaccess
        @file_put_contents($data_dir . '/.htaccess', "Require all denied\nDeny from all");

        // Hash da senha com BCRYPT/ARGON2
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $auth_data = json_encode(['username' => $username, 'password' => $hash]);
        
        // Criptografar via AES-256-CBC
        $encrypted = KodeWebEncryption::encrypt($auth_data);
        if (file_put_contents($auth_file, $encrypted) !== false) {
            // Salvar configurações em .env
            if (empty($workspace)) {
                // Por padrão explora a raiz do servidor web (ex: htdocs ou www)
                $workspace = dirname(__DIR__); 
            }
            $env_content = "LOCAL_ENV=" . $is_local . "\n";
            $env_content .= "WORKSPACE_PATH=\"" . str_replace('\\', '/', $workspace) . "\"\n";
            @file_put_contents(__DIR__ . '/.env', $env_content);
            
            // Auto-logar após instalação e redirecionar
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $message = 'Erro ao salvar o arquivo de autenticação. Verifique as permissões de gravação (chmod).';
            $message_type = 'error';
        }
    }
}

// Ler valor anterior de WORKSPACE_PATH do .env se existir
$current_workspace = dirname(__DIR__);
if (file_exists(__DIR__ . '/.env')) {
    $env = @parse_ini_file(__DIR__ . '/.env');
    if (!empty($env['WORKSPACE_PATH'])) {
        $current_workspace = $env['WORKSPACE_PATH'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KodeWeb Explorer - Instalação</title>
    <link rel="icon" type="image/svg+xml" href="logo.svg">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: var(--bg-primary, #0b0114);
            padding: 15px;
            font-family: 'Outfit', sans-serif;
            color: var(--text-primary, #f0f0f0);
            margin: 0;
        }
        .install-card {
            background-color: var(--bg-secondary, #140523);
            border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
            border-radius: 12px;
            padding: 35px 30px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            text-align: center;
        }
        .install-card img {
            width: 72px;
            height: 72px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 10px rgba(189, 0, 255, 0.4));
        }
        .install-card h2 {
            margin-bottom: 8px;
            color: #ffffff;
            font-size: 22px;
            font-weight: 600;
        }
        .install-card p {
            font-size: 13px;
            color: var(--text-muted, #a0a0a0);
            margin-bottom: 25px;
            line-height: 1.5;
        }
        .form-group {
            text-align: left;
            margin-bottom: 18px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary, #f0f0f0);
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background-color: #1d0c2c;
            color: #ffffff;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: #bd00ff;
            box-shadow: 0 0 8px rgba(189, 0, 255, 0.3);
        }
        .alert-error {
            color: #ff0055;
            background-color: rgba(255, 0, 85, 0.12);
            border: 1px solid rgba(255, 0, 85, 0.25);
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .alert-warning {
            color: #ffc107;
            background-color: rgba(255, 193, 7, 0.12);
            border: 1px solid rgba(255, 193, 7, 0.25);
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
            line-height: 1.4;
        }
        .btn-primary {
            background: linear-gradient(135deg, #bd00ff, #8b00dd);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.15s, box-shadow 0.2s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(189, 0, 255, 0.4);
        }
        .btn-primary:hover {
            box-shadow: 0 4px 20px rgba(189, 0, 255, 0.7);
            transform: translateY(-1px);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #bd00ff;
            cursor: pointer;
        }
        .checkbox-group label {
            cursor: pointer;
            font-size: 13px;
            color: var(--text-muted, #a0a0a0);
        }
        .field-hint {
            font-size: 11px;
            color: #00ff88;
            margin-top: 4px;
            display: block;
        }
    </style>
</head>
<body>

    <div class="install-card">
        <img src="logo.svg" alt="KodeWeb Explorer Logo">
        <h2>KodeWeb Explorer</h2>
        <p>Bem-vindo ao instalador oficial! Crie sua credencial de administrador e configure o escopo de navegação na hospedagem.</p>
        
        <?php if ($message && $message_type === 'error'): ?>
            <div class="alert-error"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($is_installed): ?>
            <div class="alert-warning">
                <strong>⚠️ Sistema Instalado:</strong> O KodeWeb Explorer já está instalado neste servidor. 
                Salvar novamente redefinirá o usuário, senha e diretório de exploração.
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">Usuário Administrador</label>
                <input type="text" class="form-input" id="username" name="username" placeholder="ex: lara" required <?= !$is_installed ? 'autofocus' : '' ?>>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Senha de Acesso Criptografada</label>
                <input type="password" class="form-input" id="password" name="password" placeholder="Digite uma senha segura" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="workspace">Pasta Raiz do Explorer (Hospedagem)</label>
                <input type="text" class="form-input" id="workspace" name="workspace" value="<?= htmlspecialchars($current_workspace) ?>" required>
                <span class="field-hint">💡 Por padrão aponta para o servidor raiz para navegação livre entre todos os seus arquivos.</span>
            </div>
            
            <div class="form-group checkbox-group">
                <?php
                $is_local_checked = false;
                if (file_exists(__DIR__ . '/.env')) {
                    $env = @parse_ini_file(__DIR__ . '/.env');
                    if (isset($env['LOCAL_ENV']) && $env['LOCAL_ENV'] == '1') {
                        $is_local_checked = true;
                    }
                }
                ?>
                <input type="checkbox" id="is_local" name="is_local" value="1" <?= $is_local_checked ? 'checked' : '' ?>>
                <label for="is_local">Ambiente Local / Dev (Ocultar avisos SSL de produção)</label>
            </div>
            
            <button type="submit" class="btn-primary">
                <?= $is_installed ? 'Atualizar Credenciais' : 'Instalar e Iniciar Explorer' ?>
            </button>
            
            <?php if ($is_installed): ?>
                <div style="margin-top: 18px;">
                    <a href="login.php" style="color: var(--text-muted, #a0a0a0); font-size: 13px; text-decoration: none;">← Voltar para o Login</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

</body>
</html>
