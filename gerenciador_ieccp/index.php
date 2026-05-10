<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../includes/db.php';

$erro_login = "";

if (isset($_COOKIE['admin_token'])) {
    header("Location: painel");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_form = $_POST['usuario'] ?? '';
    $senha_form   = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE usuario = :u LIMIT 1");
    $stmt->execute(['u' => $usuario_form]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha_form, $admin['senha'])) {
        $token = bin2hex(random_bytes(32));
        $sql = "UPDATE admins SET session_token = :t, ultimo_acesso = NOW() WHERE id = :id";
        $pdo->prepare($sql)->execute(['t' => $token, 'id' => $admin['id']]);
        setcookie('admin_token', $token, time() + 86400, '/', '', false, true);
        header("Location: painel");
        exit;
    } else {
        $erro_login = "Usuário ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Painel IECCP</title>
    <link rel="icon" type="image/svg+xml" href="/../img/ico.svg" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="painel_shared.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2c3e50;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(39, 174, 96, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(230, 126, 34, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 60% 80%, rgba(52, 152, 219, 0.08) 0%, transparent 40%);
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #27ae60, #219150);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(39, 174, 96, 0.35);
        }

        .login-header h1 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85rem;
            margin-top: 4px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.3);
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.9rem;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        .input-wrap input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }

        .input-wrap input:focus {
            border-color: #27ae60;
            background: rgba(39, 174, 96, 0.08);
        }

        .toggle-senha {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            padding: 4px;
            font-size: 0.85rem;
            width: auto;
            margin: 0;
            transition: color 0.2s;
        }

        .toggle-senha:hover {
            color: rgba(255, 255, 255, 0.7);
            background: none;
        }

        .erro-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 8px;
            padding: 10px 14px;
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            animation: shake 0.4s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-6px);
            }

            75% {
                transform: translateX(6px);
            }
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            margin-top: 0.5rem;
            letter-spacing: 0.03em;
            box-shadow: 0 4px 16px rgba(39, 174, 96, 0.35);
        }

        .btn-login:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
            margin-right: 6px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255, 255, 255, 0.3);
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="login-logo">⛪</div>
            <h1>Painel IECCP</h1>
            <p>Área restrita — acesso de administradores</p>
        </div>

        <div class="login-card">
            <?php if ($erro_login): ?>
                <div class="erro-box">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= htmlspecialchars($erro_login) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="login-form">
                <div class="field">
                    <label for="usuario">Usuário</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="usuario" name="usuario" placeholder="seu usuário" autocomplete="username" required>
                    </div>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="senha" name="senha" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="toggle-senha" onclick="toggleSenha(this)" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <span class="spinner" id="spinner"></span>
                    <span id="btn-text">ENTRAR</span>
                </button>
            </form>
        </div>

        <div class="login-footer">
            IECCP &copy; <?= date('Y') ?> — Sistema de Gerenciamento
        </div>
    </div>

    <script>
        function toggleSenha(btn) {
            const input = btn.closest('.input-wrap').querySelector('input');
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('login-form').addEventListener('submit', function() {
            document.getElementById('spinner').style.display = 'inline-block';
            document.getElementById('btn-text').textContent = 'Entrando...';
            document.getElementById('btn-submit').disabled = true;
        });
    </script>
</body>

</html>