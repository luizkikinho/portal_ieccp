<?php
session_start();
// Importa a conexão e a função gerarSlug da raiz
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
// Importa as funções locais do painel (uploadOtimizado e enviarNotificacaoOneSignal)
require_once 'funcoes.php';

// SEGURANÇA
if (!isset($_COOKIE['admin_token'])) {
    header("Location: /gerenciador_ieccp/");
    exit;
}
$stmt = $pdo->prepare("SELECT id FROM admins WHERE session_token = ?");
$stmt->execute([$_COOKIE['admin_token']]);
if (!$stmt->fetch()) {
    setcookie('admin_token', '', time() - 3600, '/');
    header("Location: /gerenciador_ieccp/");
    exit;
}

$jsonFile = "../data/pastoral.json";
$imgFolder = "../img/pastoral/";

if (!is_dir(dirname($jsonFile))) mkdir(dirname($jsonFile), 0777, true);
if (!is_dir($imgFolder)) mkdir($imgFolder, 0777, true);

$msg = "";
$editData = null;

// ==========================================
// CONFIGURAÇÃO DE NOTIFICAÇÕES (Títulos Rotativos)
// ==========================================
$titulosAtrativos = [
    "Você tem 2 minutinhos? ⏱️",
    "Já parou para refletir hoje? 🤔",
    "Uma pausa necessária para o seu dia ❤️",
    "Edifique o seu coração com esta leitura ✨",
    "Temos uma nova mensagem para você! 📖",
    "Uma palavra rápida, mas profunda 🙏"
];

// CARREGAR DADOS PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("
        SELECT id, slug, titulo, texto, imagem, data_publicacao
        FROM pastoral
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => (int)$_GET['editar']
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $editData = [
            'id' => $row['id'],
            'titulo' => $row['titulo'],
            'texto' => $row['texto'],
            'img' => $row['imagem'],
            'data' => !empty($row['data_publicacao'])
                ? date('d/m/Y', strtotime($row['data_publicacao']))
                : ''
        ];
    }
}

// SALVAR / ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imgPath = $_POST['imagem_atual'] ?? '';
    $erroUpload = false;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nomeArquivo = time() . ".webp";
        $caminhoRelativo = "img/pastoral/" . $nomeArquivo;
        $caminhoCompleto = "../" . $caminhoRelativo;

        if (function_exists('uploadOtimizado')) {
            if (uploadOtimizado($_FILES['imagem'], $caminhoCompleto)) {
                $imgPath = $caminhoRelativo;

                if (!empty($_POST['imagem_atual']) && file_exists("../" . $_POST['imagem_atual'])) {
                    @unlink("../" . $_POST['imagem_atual']);
                }
            } else {
                $msg = "<p class='warning'>Erro ao processar a imagem WebP.</p>";
                $erroUpload = true;
            }
        } else {
            $msg = "<p class='warning'>Erro: função uploadOtimizado() não encontrada.</p>";
            $erroUpload = true;
        }
    }

    if (!$erroUpload) {
        $titulo = trim($_POST['titulo'] ?? '');
        $texto = $_POST['texto'] ?? '';
        $id_editar = $_POST['id_editar'] ?? '';

        if ($titulo === '') {
            $msg = "<p class='warning'>O título é obrigatório.</p>";
        } else {
            try {
                $slug = gerarSlug($titulo);

                if (!empty($id_editar)) {
                    $stmt = $pdo->prepare("
                        UPDATE pastoral
                        SET
                            slug = :slug,
                            titulo = :titulo,
                            texto = :texto,
                            imagem = :imagem
                        WHERE id = :id
                    ");

                    $stmt->execute([
                        ':id' => (int)$id_editar,
                        ':slug' => $slug,
                        ':titulo' => $titulo,
                        ':texto' => $texto,
                        ':imagem' => $imgPath
                    ]);

                    $msg = "<p class='success'>✅ Atualizado com sucesso!</p>";
                } else {
                    $novoId = time();

                    $stmt = $pdo->prepare("
                        INSERT INTO pastoral (
                            id,
                            slug,
                            titulo,
                            texto,
                            imagem,
                            data_publicacao
                        ) VALUES (
                            :id,
                            :slug,
                            :titulo,
                            :texto,
                            :imagem,
                            :data_publicacao
                        )
                    ");

                    $stmt->execute([
                        ':id' => $novoId,
                        ':slug' => $slug,
                        ':titulo' => $titulo,
                        ':texto' => $texto,
                        ':imagem' => $imgPath,
                        ':data_publicacao' => date('Y-m-d')
                    ]);

                    if (function_exists('enviarNotificacaoOneSignal')) {
                        $tituloPush = $titulosAtrativos[array_rand($titulosAtrativos)];
                        $mensagemPush = "Leia a reflexão de hoje: " . $titulo . "...";
                        $urlPost = "https://ieccp.com.br/pastoral/" . $slug;

                        enviarNotificacaoOneSignal($tituloPush, $mensagemPush, $urlPost);
                    }

                    header("Location: painel_pastoral.php?ok=1");
                    exit;
                }
            } catch (PDOException $e) {
                $msg = "<p class='warning'>Erro ao salvar no banco: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}

// DELETAR
if (isset($_GET['deletar'])) {
    try {
        $id = (int)$_GET['deletar'];

        $stmt = $pdo->prepare("SELECT imagem FROM pastoral WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $img = $stmt->fetchColumn();

        if ($img && file_exists("../" . $img)) {
            @unlink("../" . $img);
        }

        $stmt = $pdo->prepare("DELETE FROM pastoral WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header("Location: painel_pastoral.php?del=1");
        exit;
    } catch (PDOException $e) {
        $msg = "<p class='warning'>Erro ao remover: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// NOTIFICAR MANUALMENTE
if (isset($_GET['notificar'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT titulo, slug
            FROM pastoral
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            ':id' => (int)$_GET['notificar']
        ]);

        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item && function_exists('enviarNotificacaoOneSignal')) {
            $tituloPush = $titulosAtrativos[array_rand($titulosAtrativos)];
            $mensagemPush = "Leia a reflexão de hoje: " . $item['titulo'] . "...";
            $slug = $item['slug'] ?: gerarSlug($item['titulo']);
            $urlPost = "https://ieccp.com.br/pastoral/" . $slug;

            enviarNotificacaoOneSignal($tituloPush, $mensagemPush, $urlPost);

            $msg = "<p class='success'>🔔 Notificação disparada! Título: <strong>" . htmlspecialchars($tituloPush) . "</strong></p>";
        } else {
            $msg = "<p class='warning'>Publicação não encontrada ou função de notificação ausente.</p>";
        }
    } catch (PDOException $e) {
        $msg = "<p class='warning'>Erro ao notificar: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// GARANTIA DE ARRAY PARA A LISTAGEM
$stmt = $pdo->query("
    SELECT id, slug, titulo, texto, imagem, data_publicacao
    FROM pastoral
    ORDER BY id DESC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$list = array_map(function ($row) {
    return [
        'id' => $row['id'],
        'slug' => $row['slug'],
        'titulo' => $row['titulo'],
        'texto' => $row['texto'],
        'img' => $row['imagem'],
        'data' => !empty($row['data_publicacao'])
            ? date('d/m/Y', strtotime($row['data_publicacao']))
            : ''
    ];
}, $rows);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pastoral - IECCP</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">

    <style>
        body {
            padding: 20px;
            background: #ecf0f1;
            font-family: 'Poppins', sans-serif;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        input,
        button {
            width: 100%;
            margin-bottom: 1rem;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            box-sizing: border-box;
        }

        button {
            background: #27ae60;
            color: white;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.2s;
        }

        button:hover {
            background: #219150;
        }

        .btn-cancel {
            background: #95a5a6;
            margin-top: 5px;
        }

        .success {
            color: #27ae60;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .warning {
            color: #d35400;
            background: #fdf3e7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .actions a {
            margin-left: 5px;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-del {
            background: #e74c3c;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-notify {
            background: #3498db;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-notify:hover {
            background: #2980b9;
        }

        .btn-edit:hover {
            background: #d68910;
        }

        .btn-del:hover {
            background: #c0392b;
        }

        .editor-toolbar {
            border-radius: 6px 6px 0 0;
            background: #fdfdfd;
            opacity: 1 !important;
        }

        .editor-toolbar button {
            background: transparent;
            border: none;
            width: 30px;
            height: 30px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .editor-toolbar button:hover {
            background: #f0f0f0;
            border-radius: 4px;
        }

        .CodeMirror {
            border-radius: 0 0 6px 6px;
            min-height: 350px;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            .item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .actions {
                width: 100%;
                display: flex;
                justify-content: space-between;
                gap: 5px;
            }

            .btn-edit,
            .btn-del,
            .btn-notify {
                text-align: center;
                flex: 1;
                padding: 8px 5px;
                font-size: 0.9rem;
                margin-left: 0;
            }

            .CodeMirror {
                min-height: 200px;
            }

            h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include 'menu_admin.php'; ?>
        <?= $msg ?>

        <h3><?= $editData ? '✏️ Editar Pastoral' : '➕ Novo Artigo Pastoral' ?></h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_editar" value="<?= $editData['id'] ?? '' ?>">
            <input type="hidden" name="imagem_atual" value="<?= $editData['img'] ?? '' ?>">
            <input type="hidden" name="data_original" value="<?= $editData['data'] ?? '' ?>">

            <label>Título:</label>
            <input type="text" name="titulo" value="<?= $editData['titulo'] ?? '' ?>" required>

            <label style="display:block; margin-top:15px; margin-bottom:5px;">Texto da Publicação:</label>
            <textarea name="texto" id="markdown-editor"><?= $editData['texto'] ?? '' ?></textarea>

            <label style="display:block; margin-top:15px; margin-bottom:5px;">Imagem:</label>
            <?php if ($editData): ?> <small style="color:#666">(Vazio para manter atual)</small> <?php endif; ?>
            <input type="file" name="imagem" accept="image/*" <?= $editData ? '' : 'required' ?>>

            <button type="submit" style="margin-top:15px;"><?= $editData ? 'SALVAR ALTERAÇÕES' : 'PUBLICAR AGORA' ?></button>
            <?php if ($editData): ?> <a href="painel_pastoral.php"><button type="button" class="btn-cancel">CANCELAR</button></a> <?php endif; ?>
        </form>

        <div style="margin-top:40px;">
            <h3>Publicados</h3>
            <?php foreach ($list as $i): ?>
                <div class="item">
                    <div><strong><?= $i['titulo'] ?></strong> <small style="color: #7f8c8d; display: block; margin-top: 5px;"><i class="fa-regular fa-calendar"></i> <?= $i['data'] ?></small></div>
                    <div class="actions">
                        <a href="?notificar=<?= $i['id'] ?>" class="btn-notify" onclick="return confirm('Deseja enviar uma notificação para todos os celulares avisando sobre esta publicação?');"><i class="fa-solid fa-bell"></i> Notificar</a>
                        <a href="?editar=<?= $i['id'] ?>" class="btn-edit"><i class="fa-solid fa-pen"></i> Editar</a>
                        <a href="?deletar=<?= $i['id'] ?>" class="btn-del" onclick="return confirm('Excluir definitivamente?');"><i class="fa-solid fa-trash"></i> Apagar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
    <script>
        const easyMDE = new EasyMDE({
            element: document.getElementById('markdown-editor'),
            spellChecker: false,
            placeholder: "Escreva sua pastoral aqui e use os botões acima para formatar...",
            toolbar: [{
                    name: "bold",
                    action: EasyMDE.toggleBold,
                    className: "fa-solid fa-bold",
                    title: "Negrito"
                },
                {
                    name: "italic",
                    action: EasyMDE.toggleItalic,
                    className: "fa-solid fa-italic",
                    title: "Itálico"
                },
                {
                    name: "heading",
                    action: EasyMDE.toggleHeadingSmaller,
                    className: "fa-solid fa-heading",
                    title: "Título"
                },
                "|",
                {
                    name: "quote",
                    action: EasyMDE.toggleBlockquote,
                    className: "fa-solid fa-quote-left",
                    title: "Citação"
                },
                {
                    name: "unordered-list",
                    action: EasyMDE.toggleUnorderedList,
                    className: "fa-solid fa-list-ul",
                    title: "Lista com Pontos"
                },
                {
                    name: "ordered-list",
                    action: EasyMDE.toggleOrderedList,
                    className: "fa-solid fa-list-ol",
                    title: "Lista Numerada"
                },
                "|",
                {
                    name: "link",
                    action: EasyMDE.drawLink,
                    className: "fa-solid fa-link",
                    title: "Criar Link"
                },
                {
                    name: "horizontal-rule",
                    action: EasyMDE.drawHorizontalRule,
                    className: "fa-solid fa-minus",
                    title: "Linha Divisória"
                },
                "|",
                {
                    name: "preview",
                    action: EasyMDE.togglePreview,
                    className: "fa-solid fa-eye no-disable",
                    title: "Prévia"
                },
                {
                    name: "side-by-side",
                    action: EasyMDE.toggleSideBySide,
                    className: "fa-solid fa-table-columns no-disable no-mobile",
                    title: "Lado a Lado"
                },
                {
                    name: "fullscreen",
                    action: EasyMDE.toggleFullScreen,
                    className: "fa-solid fa-expand no-disable no-mobile",
                    title: "Tela Cheia"
                },
                "|",
                {
                    name: "guide",
                    action: "https://www.markdownguide.org/basic-syntax/",
                    className: "fa-solid fa-circle-question",
                    title: "Ajuda"
                }
            ]
        });
    </script>
</body>

</html>