<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once 'funcoes.php';

if (!isset($_COOKIE['admin_token'])) {
    header("Location: /gerenciador_ieccp/");
    exit;
}
$stmt = $pdo->prepare("SELECT id, usuario FROM admins WHERE session_token = ?");
$stmt->execute([$_COOKIE['admin_token']]);
$adminRow = $stmt->fetch();
if (!$adminRow) {
    setcookie('admin_token', '', time() - 3600, '/');
    header("Location: /gerenciador_ieccp/");
    exit;
}

$jsonFile  = "../data/noticias.json";
$imgFolder = "../img/noticias/";

$msg      = "";
$msgType  = "";
$editData = null;

$titulosNoticias = [
    "Fique por dentro do que está acontecendo! 📢",
    "Notícia fresquinha para a nossa comunidade 🗞️",
    "Temos novidades importantes para você! 👀",
    "Atualizações da IECCP: confira o que mudou ⛪",
    "Um recado rápido para a nossa família 👨‍👩‍👧‍👦",
    "Você já viu as últimas novidades? 📰",
    "Informação no seu radar: veja o que rolou 📡",
    "Aconteceu na IECCP! Vem conferir 📸"
];

// CARREGAR PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id = ?");
    $stmt->execute([(int)$_GET['editar']]);
    $row = $stmt->fetch();

    if ($row) {
        $editData = [
            'id'     => $row['id'],
            'titulo' => $row['titulo'],
            'texto'  => $row['texto'],
            'img'    => $row['imagem'],
            'data'   => !empty($row['data_publicacao'])
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
        $nomeArquivo     = time() . ".webp";
        $caminhoRelativo = "img/noticias/" . $nomeArquivo;
        $caminhoCompleto = "../" . $caminhoRelativo;

        if (function_exists('uploadOtimizado')) {
            if (uploadOtimizado($_FILES['imagem'], $caminhoCompleto)) {
                $imgPath = $caminhoRelativo;

                if (!empty($_POST['imagem_atual']) && file_exists("../" . $_POST['imagem_atual'])) {
                    @unlink("../" . $_POST['imagem_atual']);
                }
            } else {
                $msg = "Erro ao processar a imagem WebP.";
                $msgType = "error";
                $erroUpload = true;
            }
        } else {
            $msg = "Função uploadOtimizado() não encontrada.";
            $msgType = "error";
            $erroUpload = true;
        }
    }

    if (!$erroUpload) {
        $titulo    = trim($_POST['titulo'] ?? '');
        $texto     = $_POST['texto'] ?? '';
        $id_editar = $_POST['id_editar'] ?? '';

        try {
            if (!empty($id_editar)) {
                $stmt = $pdo->prepare("
                    UPDATE noticias
                    SET titulo = :titulo,
                        texto =  :texto,
                        imagem = :imagem,
                        slug =   :slug
                    WHERE id = :id
                ");

                $stmt->execute([
                    ':id'     => (int)$id_editar,
                    ':slug'   => $slug,
                    ':titulo' => $titulo,
                    ':texto'  => $texto,
                    ':imagem' => $imgPath
                ]);

                $msg = "Notícia atualizada com sucesso!";
                $msgType = "success";
            } else {
                $novoId = time();

                $stmt = $pdo->prepare("
                    INSERT INTO noticias (
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
                    ':id'              => $novoId,
                    ':slug'            => $slug,
                    ':titulo'          => $titulo,
                    ':texto'           => $texto,
                    ':imagem'          => $imgPath,
                    ':data_publicacao' => date('Y-m-d')
                ]);

                if (function_exists('enviarNotificacaoOneSignal') && function_exists('gerarSlug')) {
                    $tituloPush   = $titulosNoticias[array_rand($titulosNoticias)];
                    $mensagemPush = "Saiba mais: " . $titulo . "...";
                    $slug         = gerarSlug($titulo);
                    $urlPost      = "https://ieccp.com.br/noticias/" . $slug;

                    enviarNotificacaoOneSignal($tituloPush, $mensagemPush, $urlPost);
                }

                header("Location: painel?ok=1");
                exit;
            }
        } catch (PDOException $e) {
            $msg = "Erro ao salvar no banco: " . $e->getMessage();
            $msgType = "error";
        }
    }
}

// DELETAR
if (isset($_GET['deletar'])) {
    try {
        $stmt = $pdo->prepare("SELECT imagem FROM noticias WHERE id = ?");
        $stmt->execute([(int)$_GET['deletar']]);
        $row = $stmt->fetch();

        if ($row && !empty($row['imagem']) && file_exists("../" . $row['imagem'])) {
            @unlink("../" . $row['imagem']);
        }

        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
        $stmt->execute([(int)$_GET['deletar']]);

        header("Location: painel?del=1");
        exit;
    } catch (PDOException $e) {
        $msg = "Erro ao remover notícia: " . $e->getMessage();
        $msgType = "error";
    }
}

if (isset($_GET['ok'])) {
    $msg = "Notícia publicada com sucesso!";
    $msgType = "success";
}
if (isset($_GET['del'])) {
    $msg = "Notícia removida.";
    $msgType = "warning";
}

$stmt = $pdo->query("
    SELECT id, slug, titulo, texto, imagem, data_publicacao
    FROM noticias
    ORDER BY id DESC
");

$rows = $stmt->fetchAll();

$list = array_map(function ($row) {
    return [
        'id'     => $row['id'],
        'titulo' => $row['titulo'],
        'texto'  => $row['texto'],
        'img'    => $row['imagem'],
        'data'   => !empty($row['data_publicacao'])
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
    <title>Notícias — Painel IECCP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <link rel="stylesheet" href="painel_shared.css">
    <style>
        .img-current {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 7px;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-size: 0.82rem;
            color: #666;
        }

        .img-current img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php include 'menu_admin.php'; ?>

        <?php if ($msg): ?>
            <div class="msg <?= $msgType ?>">
                <i class="fa-solid fa-<?= $msgType === 'success' ? 'circle-check' : ($msgType === 'warning' ? 'triangle-exclamation' : 'circle-xmark') ?>"></i>
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>
                <i class="fa-solid fa-<?= $editData ? 'pen-to-square' : 'plus-circle' ?>"></i>
                <?= $editData ? 'Editar Notícia' : 'Nova Notícia' ?>
            </h3>

            <form method="POST" enctype="multipart/form-data" id="main-form">
                <input type="hidden" name="id_editar" value="<?= $editData['id'] ?? '' ?>">
                <input type="hidden" name="imagem_atual" value="<?= $editData['img'] ?? '' ?>">
                <input type="hidden" name="data_original" value="<?= $editData['data'] ?? '' ?>">

                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($editData['titulo'] ?? '') ?>" placeholder="Título da notícia..." required>

                <label>Texto da Publicação</label>
                <textarea name="texto" id="markdown-editor"><?= $editData['texto'] ?? '' ?></textarea>

                <label style="margin-top: 8px;">Imagem <small style="font-weight:400;text-transform:none;letter-spacing:0;color:#999;">(otimizada automaticamente para WebP)</small></label>

                <?php if ($editData && !empty($editData['img'])): ?>
                    <div class="img-current">
                        <img src="../<?= $editData['img'] ?>">
                        <span>Imagem atual — envie outra para substituir</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*" <?= $editData ? '' : 'required' ?>>

                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <span class="spinner-btn" id="spinner" style="display:none; width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                    <i class="fa-solid fa-<?= $editData ? 'floppy-disk' : 'paper-plane' ?>" id="btn-icon"></i>
                    <span id="btn-text"><?= $editData ? 'SALVAR ALTERAÇÕES' : 'PUBLICAR NOTÍCIA' ?></span>
                </button>

                <?php if ($editData): ?>
                    <a href="painel"><button type="button" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancelar</button></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-divider">
            <h3>
                <i class="fa-solid fa-list"></i> Publicadas
                <span class="count-badge"><?= count($list) ?></span>
            </h3>
        </div>

        <div class="items-list">
            <?php if (empty($list)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-newspaper"></i>
                    <p>Nenhuma notícia publicada ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($list as $i): ?>
                    <div class="item">
                        <div class="item-info">
                            <?php if (!empty($i['img'])): ?>
                                <img src="../<?= $i['img'] ?>" alt="">
                            <?php endif; ?>
                            <div>
                                <div class="item-title"><?= htmlspecialchars($i['titulo']) ?></div>
                                <div class="item-meta">
                                    <i class="fa-regular fa-calendar"></i> <?= $i['data'] ?>
                                </div>
                            </div>
                        </div>
                        <div class="actions">
                            <a href="?editar=<?= $i['id'] ?>" class="btn btn-edit"><i class="fa-solid fa-pen"></i> Editar</a>
                            <a href="?deletar=<?= $i['id'] ?>" class="btn btn-del" onclick="return confirm('Apagar esta notícia definitivamente?')"><i class="fa-solid fa-trash"></i> Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
    <script>
        const easyMDE = new EasyMDE({
            element: document.getElementById('markdown-editor'),
            spellChecker: false,
            placeholder: "Escreva sua publicação aqui e use os botões acima para formatar...",
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

        document.getElementById('main-form').addEventListener('submit', function() {
            document.getElementById('spinner').style.display = 'inline-block';
            document.getElementById('btn-icon').style.display = 'none';
            document.getElementById('btn-text').textContent = 'Salvando...';
            document.getElementById('btn-submit').disabled = true;
        });
    </script>
    <style>
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</body>

</html>