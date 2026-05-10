<?php
session_start();
require_once 'funcoes.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

verificiarEventosExpirados('../data/agenda.json');

if (!isset($_COOKIE['admin_token'])) {
    header("Location: index.php");
    exit;
}
$stmt = $pdo->prepare("SELECT id FROM admins WHERE session_token = ?");
$stmt->execute([$_COOKIE['admin_token']]);
if (!$stmt->fetch()) {
    setcookie('admin_token', '', time() - 3600, '/');
    header("Location: index.php");
    exit;
}

$jsonFile = "../data/agenda.json";
$msg      = "";
$msgType  = "";
$editData = null;

$titulosAtrativos = [
    "Sua semana na IECCP 🙏",
    "Confira a programação da semana!",
    "Programe-se para os próximos dias",
    "Novos eventos na nossa agenda!"
];

function formatarDataParaSalvar($dataYMD)
{
    if (!$dataYMD) return "";
    $d = DateTime::createFromFormat('Y-m-d', $dataYMD);
    return $d ? $d->format('d/m/Y') : "";
}

// CARREGAR PARA EDIÇÃO
if (isset($_GET['editar'])) {
    $conteudo = file_exists($jsonFile) ? file_get_contents($jsonFile) : '[]';
    $data     = json_decode($conteudo, true);
    if (is_array($data)) {
        foreach ($data as $item) {
            if ($item['id'] == $_GET['editar']) {
                $editData = $item;
                break;
            }
        }
    }
}

// SALVAR / ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id_editar']) ? (int)$_POST['id_editar'] : time();

    $titulo = $_POST['titulo'] ?? '';
    $local  = $_POST['local'] ?? '';
    $texto  = $_POST['texto'] ?? '';

    $data_inicio = $_POST['data_inicio'] ?? null;
    $hora_inicio = $_POST['hora_inicio'] ?? null;
    $data_fim    = $_POST['data_fim'] ?? null;
    $hora_fim    = $_POST['hora_fim'] ?? null;

    $imgPath = $_POST['imagem_atual'] ?? '';

    // Upload imagem (mantido)
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $nomeArquivo = time() . ".webp";
        $caminhoRelativo = "img/agenda/" . $nomeArquivo;
        $caminhoCompleto = "../" . $caminhoRelativo;

        if (uploadOtimizado($_FILES['imagem'], $caminhoCompleto)) {
            $imgPath = $caminhoRelativo;

            if (!empty($_POST['imagem_atual']) && file_exists("../" . $_POST['imagem_atual'])) {
                @unlink("../" . $_POST['imagem_atual']);
            }
        }
    }

    $slug = gerarSlug($titulo);

    $stmt = $pdo->prepare("
        INSERT INTO agenda (
            id, slug, titulo, local, texto, imagem,
            data_inicio, hora_inicio, data_fim, hora_fim
        ) VALUES (
            :id, :slug, :titulo, :local, :texto, :imagem,
            :data_inicio, :hora_inicio, :data_fim, :hora_fim
        )
        ON CONFLICT (id)
        DO UPDATE SET
            slug = EXCLUDED.slug,
            titulo = EXCLUDED.titulo,
            local = EXCLUDED.local,
            texto = EXCLUDED.texto,
            imagem = EXCLUDED.imagem,
            data_inicio = EXCLUDED.data_inicio,
            hora_inicio = EXCLUDED.hora_inicio,
            data_fim = EXCLUDED.data_fim,
            hora_fim = EXCLUDED.hora_fim
    ");

    $stmt->execute([
        ':id' => $id,
        ':slug' => $slug,
        ':titulo' => $titulo,
        ':local' => $local,
        ':texto' => $texto,
        ':imagem' => $imgPath,
        ':data_inicio' => $data_inicio ?: null,
        ':hora_inicio' => $hora_inicio ?: null,
        ':data_fim' => $data_fim ?: null,
        ':hora_fim' => $hora_fim ?: null
    ]);

    header("Location: painel_agenda?ok=1");
    exit;
}

// DELETAR
if (isset($_GET['deletar'])) {
    $id = (int)$_GET['deletar'];

    $stmt = $pdo->prepare("SELECT imagem FROM agenda WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $img = $stmt->fetchColumn();

    if ($img && file_exists("../" . $img)) {
        @unlink("../" . $img);
    }

    $pdo->prepare("DELETE FROM agenda WHERE id = :id")
        ->execute([':id' => $id]);

    header("Location: painel_agenda?del=1");
    exit;
}

if (isset($_GET['ok'])) {
    $msg = "Evento salvo com sucesso!";
    $msgType = "success";
}
if (isset($_GET['del'])) {
    $msg = "Evento removido.";
    $msgType = "warning";
}

$stmt = $pdo->query("SELECT * FROM agenda ORDER BY data_inicio ASC, hora_inicio ASC");
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar campos de data para edição
$val_data_inicio = "";
$val_data_fim    = "";
if ($editData) {
    if (!empty($editData['data_inicio'])) {
        $d = DateTime::createFromFormat('d/m/Y', $editData['data_inicio']);
        if ($d) $val_data_inicio = $d->format('Y-m-d');
    } elseif (!empty($editData['data'])) {
        $d = DateTime::createFromFormat('d/m/Y', $editData['data']);
        if ($d) $val_data_inicio = $d->format('Y-m-d');
    }
    if (!empty($editData['data_fim'])) {
        $d = DateTime::createFromFormat('d/m/Y', $editData['data_fim']);
        if ($d) $val_data_fim = $d->format('Y-m-d');
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda — Painel IECCP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <link rel="stylesheet" href="painel_shared.css">
    <style>
        .date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 1rem;
        }

        .date-block {
            background: #fff;
            border: 1px solid #dde1e7;
            border-radius: 8px;
            padding: 14px;
        }

        .date-block-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 10px;
        }

        .date-block-label.inicio {
            color: #27ae60;
        }

        .date-block-label.fim {
            color: #e74c3c;
        }

        .date-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .date-row input {
            margin-bottom: 0;
        }

        .date-row label {
            font-size: 0.72rem;
            color: #999;
            margin-bottom: 4px;
            text-transform: none;
            letter-spacing: 0;
        }

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

        .event-date-tag {
            background: #eaf6ef;
            color: #27ae60;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        @media (max-width: 600px) {
            .date-grid {
                grid-template-columns: 1fr;
            }

            .date-row {
                grid-template-columns: 1fr 1fr;
            }
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
                <i class="fa-solid fa-<?= $editData ? 'pen-to-square' : 'calendar-plus' ?>"></i>
                <?= $editData ? 'Editar Evento' : 'Novo Evento' ?>
            </h3>

            <form method="POST" enctype="multipart/form-data" id="main-form">
                <input type="hidden" name="id_editar" value="<?= $editData['id'] ?? '' ?>">
                <input type="hidden" name="imagem_atual" value="<?= $editData['img'] ?? '' ?>">
                <input type="hidden" name="data_antiga" value="<?= $editData['data'] ?? '' ?>">

                <label for="titulo">Título do Evento</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($editData['titulo'] ?? '') ?>" placeholder="Nome do evento..." required>

                <div class="date-grid">
                    <div class="date-block">
                        <div class="date-block-label inicio"><i class="fa-solid fa-play"></i> Início</div>
                        <div class="date-row">
                            <div>
                                <label>Data</label>
                                <input type="date" name="data_inicio" value="<?= $val_data_inicio ?>" required>
                            </div>
                            <div>
                                <label>Horário</label>
                                <input type="time" name="hora_inicio" value="<?= $editData['hora_inicio'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="date-block">
                        <div class="date-block-label fim"><i class="fa-solid fa-stop"></i> Fim</div>
                        <div class="date-row">
                            <div>
                                <label>Data</label>
                                <input type="date" name="data_fim" value="<?= $val_data_fim ?>">
                            </div>
                            <div>
                                <label>Horário</label>
                                <input type="time" name="hora_fim" value="<?= $editData['hora_fim'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <label for="local">Local</label>
                <input type="text" id="local" name="local" value="<?= htmlspecialchars($editData['local'] ?? '') ?>" placeholder="Ex: Salão Principal, Auditório..." required>

                <label>Descrição</label>
                <textarea name="texto" id="markdown-editor"><?= $editData['texto'] ?? '' ?></textarea>

                <label style="margin-top:8px;">Imagem <small style="font-weight:400;text-transform:none;letter-spacing:0;color:#999;">(otimizada automaticamente para WebP)</small></label>
                <?php if ($editData && !empty($editData['img'])): ?>
                    <div class="img-current">
                        <img src="../<?= $editData['img'] ?>">
                        <span>Imagem atual — envie outra para substituir</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagem" accept="image/*">

                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <span id="spinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                    <i class="fa-solid fa-<?= $editData ? 'floppy-disk' : 'calendar-check' ?>" id="btn-icon"></i>
                    <span id="btn-text"><?= $editData ? 'SALVAR ALTERAÇÕES' : 'PUBLICAR EVENTO' ?></span>
                </button>

                <?php if ($editData): ?>
                    <a href="painel_agenda"><button type="button" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancelar</button></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-divider">
            <h3><i class="fa-solid fa-calendar-days"></i> Eventos <span class="count-badge"><?= count($list) ?></span></h3>
        </div>

        <div class="items-list">
            <?php if (empty($list)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-days"></i>
                    <p>Nenhum evento na agenda.</p>
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
                                    <?php
                                    $dataExib = $i['data_inicio'] ?? $i['data'] ?? '';
                                    $horaExib = !empty($i['hora_inicio']) ? $i['hora_inicio'] : '';
                                    ?>
                                    <?php if ($dataExib): ?>
                                        <span class="event-date-tag">
                                            <i class="fa-regular fa-calendar"></i>
                                            <?= $dataExib ?>
                                            <?= $horaExib ? '• ' . $horaExib : '' ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($i['local'])): ?>
                                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($i['local']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="actions">
                            <a href="?editar=<?= $i['id'] ?>" class="btn btn-edit"><i class="fa-solid fa-pen"></i> Editar</a>
                            <a href="?deletar=<?= $i['id'] ?>" class="btn btn-del" onclick="return confirm('Apagar este evento definitivamente?')"><i class="fa-solid fa-trash"></i> Excluir</a>
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
            placeholder: "Detalhes do evento...",
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

        // Validação: data fim não pode ser antes do início
        document.getElementById('main-form').addEventListener('submit', function(e) {
            const inicio = document.querySelector('[name="data_inicio"]').value;
            const fim = document.querySelector('[name="data_fim"]').value;
            if (inicio && fim && fim < inicio) {
                e.preventDefault();
                alert('A data de fim não pode ser anterior à data de início.');
            }
        }, true);
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