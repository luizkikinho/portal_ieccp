<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ajuste o caminho do db.php dependendo de onde você salvar este arquivo temporário
require_once __DIR__ . '/../includes/db.php';

$mensagem = "";
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_form = trim($_POST['usuario'] ?? '');
    $senha_form   = $_POST['senha'] ?? '';

    if (!empty($usuario_form) && !empty($senha_form)) {
        // 1. Verifica se o usuário já existe para não duplicar
        $stmt_check = $pdo->prepare("SELECT id FROM admins WHERE usuario = :u LIMIT 1");
        $stmt_check->execute(['u' => $usuario_form]);

        if ($stmt_check->fetch()) {
            $mensagem = "Este usuário já está cadastrado!";
        } else {
            // 2. Gera o Hash seguro da senha
            $hash_senha = password_hash($senha_form, PASSWORD_DEFAULT);

            // 3. Insere no banco de dados
            $sql = "INSERT INTO admins (usuario, senha) VALUES (:u, :s)";
            $stmt_insert = $pdo->prepare($sql);

            if ($stmt_insert->execute(['u' => $usuario_form, 's' => $hash_senha])) {
                $mensagem = "Administrador criado com sucesso! POR FAVOR, DELETE ESTE ARQUIVO AGORA.";
                $sucesso = true;
            } else {
                $mensagem = "Erro ao inserir no banco de dados.";
            }
        }
    } else {
        $mensagem = "Por favor, preencha usuário e senha.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Criar Admin Temporário</title>
    <link rel="icon" type="image/svg+xml" href="/../img/ico.svg" />

    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #2c3e50;
            margin: 0;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 300px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px;
            background: #27ae60;
            /* Verde para diferenciar da tela de login */
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }

        button:hover {
            background: #219a52;
        }

        .msg {
            text-align: center;
            font-size: 0.9em;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .erro {
            background: #fee;
            color: red;
            border: 1px solid #fcc;
        }

        .sucesso {
            background: #efe;
            color: green;
            border: 1px solid #cfc;
        }
    </style>
</head>

<body>
    <form action="" method="POST">
        <h2 style="text-align:center; color:#333; margin-top: 0;">Setup de Admin</h2>

        <p style="font-size: 0.8em; color: #7f8c8d; text-align: center; margin-bottom: 5px;">
            Script temporário para geração de hash
        </p>

        <?php if ($mensagem): ?>
            <div class="msg <?= $sucesso ? 'sucesso' : 'erro' ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
            <input type="text" name="usuario" placeholder="Novo Usuário" required>
            <input type="password" name="senha" placeholder="Nova Senha" required>
            <button type="submit">CRIAR ADMIN</button>
        <?php else: ?>
            <a href="index.php" style="text-align: center; color: #2980b9; text-decoration: none; font-weight: bold;">Ir para a tela de Login</a>
        <?php endif; ?>
    </form>
</body>

</html>