<?php
// login.php - Tela de login responsiva do KodeWeb Explorer

require_once __DIR__ . '/session.php';
$auth_file = __DIR__ . '/data/auth.enc';

if (!file_exists($auth_file)) {
    header("Location: install.php");
    exit;
}

if (!empty($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'encryption.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $encData = @file_get_contents($auth_file);
        $decData = KodeWebEncryption::decrypt($encData);
        if ($decData) {
            $authData = json_decode($decData, true);
            if ($authData && $username === $authData['username'] && password_verify($password, $authData['password'])) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $authData['username'];
                header("Location: index.php");
                exit;
            } else {
                $error = 'Usuário ou senha incorretos.';
            }
        } else {
            $error = 'Erro ao ler a credencial criptografada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KodeWeb Explorer - Acesso</title>
    <link rel="icon" type="image/svg+xml" href="logo.svg">
    <link rel="stylesheet" href="style.css">
    
    <!-- PWA / Mobile Capable Configuration -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#140523">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="logo.svg">
    
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
        .login-card {
            background-color: var(--bg-secondary, #140523);
            border: 1px solid var(--border-color, rgba(255, 255, 255, 0.1));
            border-radius: 12px;
            padding: 35px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, #bd00ff, #00ff88);
            border-radius: 3px;
        }
        .login-card img {
            width: 76px;
            height: 76px;
            margin-bottom: 14px;
            filter: drop-shadow(0 0 12px rgba(0, 255, 136, 0.35));
        }
        .login-card h2 {
            margin-bottom: 6px;
            color: #ffffff;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .login-card p.subtitle {
            font-size: 13px;
            color: var(--text-muted, #a0a0a0);
            margin-bottom: 24px;
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
            padding: 12px 14px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background-color: #1d0c2c;
            color: #ffffff;
            font-size: 15px;
            font-family: 'Outfit', sans-serif;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: #00ff88;
            box-shadow: 0 0 8px rgba(0, 255, 136, 0.3);
        }
        .error-message {
            color: #ffffff;
            background-color: rgba(255, 0, 85, 0.2);
            border: 1px solid #ff0055;
            font-size: 13px;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #bd00ff, #8b00dd);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 13px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(189, 0, 255, 0.4);
            margin-top: 5px;
        }
        .btn-primary:hover {
            box-shadow: 0 4px 20px rgba(189, 0, 255, 0.7);
            transform: translateY(-1px);
        }
        .footer-info {
            margin-top: 25px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="logo.svg" alt="KodeWeb Explorer Logo">
        <h2>KodeWeb Explorer</h2>
        <p class="subtitle">Gerenciador de Arquivos Cloud e Hospedagem</p>
        
        <?php if ($error): ?>
            <div class="error-message">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">Usuário</label>
                <input type="text" class="form-input" id="username" name="username" placeholder="Seu usuário" required autofocus autocomplete="username">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Senha</label>
                <input type="password" class="form-input" id="password" name="password" placeholder="Sua senha" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-primary">🔐 Entrar no Explorer</button>
        </form>
        
        <div class="footer-info">
            Família KodeWeb &copy; 2026 Laralabs
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                    .then(reg => console.log('PWA Service Worker ativo na tela de login:', reg.scope))
                    .catch(err => console.log('Serviço SW offline:', err));
            });
        }
    </script>
</body>
</html>
