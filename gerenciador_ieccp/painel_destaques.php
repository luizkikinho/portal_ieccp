<?php
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once 'funcoes.php';

// --- SEGURANÇA ---
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

// --- CONFIGURAÇÃO SUPABASE (USANDO SEU .ENV) ---
// Como o db.php já rodou, a variável $env já existe!
$supabaseUrl = $env['SUPABASE_URL'] ?? '';
$supabaseKey = $env['SUPABASE_KEY'] ?? '';
$apiUrl      = $supabaseUrl . '/rest/v1/destaques_video';

$msg     = "";
$msgType = "";
$editData = null;

// --- FUNÇÃO DE COMUNICAÇÃO COM BYPASS DE SSL ---
function callSupabase($url, $method, $key, $data = null)
{
    $ch      = curl_init($url);
    $headers = [
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $erroCurl = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'curl_error' => $erroCurl,
        'raw_response' => $response,
        'body' => json_decode($response, true)
    ];
}

// --- CARREGAR PARA EDIÇÃO ---
if (isset($_GET['editar'])) {
    $res = callSupabase($apiUrl . '?youtube_id=eq.' . urlencode($_GET['editar']), 'GET', $supabaseKey);
    if ($res['http_code'] === 200 && !empty($res['body'])) {
        $editData = $res['body'][0];
    }
}

// --- SALVAR / ATUALIZAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $yid    = trim($_POST['youtube_id'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $tipo   = $_POST['tipo'] ?? 'video';
    $ordem  = (int)($_POST['ordem'] ?? 1);
    $ativo  = isset($_POST['ativo']);
    $custom_thumb = trim($_POST['thumbnail_custom'] ?? '');

    // Força o tipo para playlist se o ID começar com PL
    if (str_starts_with($yid, 'PL')) {
        $tipo = 'playlist';
    }

    // LÓGICA DE LINK E THUMBNAIL DA BASE
    if ($tipo === 'playlist') {
        $link = "https://www.youtube.com/playlist?list={$yid}";
        // Se não forneceu uma capa, usa um placeholder genérico (para não ficar invisível no portal)
        $thumb = !empty($custom_thumb) ? $custom_thumb : "https://placehold.co/600x338/2c3e50/FFFFFF?text=Acessar+Playlist";
    } else {
        $link = "https://www.youtube.com/watch?v={$yid}";
        $thumb = "https://i.ytimg.com/vi/{$yid}/maxresdefault.jpg";
    }

    $payload = [
        'youtube_id' => $yid,
        'titulo'     => $titulo,
        'tipo'       => $tipo,
        'thumbnail'  => $thumb,
        'link'       => $link,
        'isLive'     => false,
        'ordem'      => $ordem,
        'ativo'      => $ativo
    ];

    if (!empty($_POST['id_original'])) {
        $resultado = callSupabase($apiUrl . '?youtube_id=eq.' . urlencode($_POST['id_original']), 'PATCH', $supabaseKey, $payload);
        $redirectUrl = "painel_destaques.php?ok=2";
    } else {
        $resultado = callSupabase($apiUrl, 'POST', $supabaseKey, $payload);
        $redirectUrl = "painel_destaques.php?ok=1";
    }

    // DEBUG DE ERRO
    if ($resultado['http_code'] >= 300 || $resultado['http_code'] == 0) {
        die("<div style='background:#f8d7da; color:#721c24; padding:20px; font-family:sans-serif; border-radius: 8px; margin: 20px;'>
                <h2>🚨 Ocorreu um erro ao salvar</h2>
                <p><b>Código HTTP:</b> " . $resultado['http_code'] . "</p>
                <p><b>Erro Interno (cURL):</b> " . ($resultado['curl_error'] ?: 'Nenhum') . "</p>
                <p><b>Resposta do Supabase:</b> " . htmlspecialchars($resultado['raw_response']) . "</p>
                <button onclick='window.history.back()' style='padding: 10px; cursor: pointer;'>Voltar para tentar de novo</button>
             </div>");
    }

    header("Location: " . $redirectUrl);
    exit;
}

// --- DELETAR ---
if (isset($_GET['deletar'])) {
    $resultado = callSupabase($apiUrl . '?youtube_id=eq.' . urlencode($_GET['deletar']), 'DELETE', $supabaseKey);
    header("Location: painel_destaques.php?del=1");
    exit;
}

// --- FEEDBACK ---
if (isset($_GET['ok'])) {
    $msg = $_GET['ok'] == 1 ? "Novo destaque publicado!" : "Destaque atualizado com sucesso!";
    $msgType = "success";
}
if (isset($_GET['del'])) {
    $msg = "Destaque removido.";
    $msgType = "warning";
}

// --- LISTAGEM FINAL ---
$resList = callSupabase($apiUrl . '?order=ordem.asc', 'GET', $supabaseKey);
$list = ($resList['http_code'] === 200 && is_array($resList['body'])) ? $resList['body'] : [];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destaques — Painel IECCP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="painel_shared.css">
    <style>
        .yt-thumb-preview {
            width: 100%;
            max-width: 280px;
            border-radius: 7px;
            border: 2px solid #eee;
            display: none;
            margin-top: 8px;
        }

        .yt-thumb-preview.visible {
            display: block;
        }

        .tipo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .tipo-video {
            background: #fef3e2;
            color: #e67e22;
        }

        .tipo-playlist {
            background: #e8f4fd;
            color: #2980b9;
        }

        .ativo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 20px;
        }

        .ativo-sim {
            background: #eaf6ef;
            color: #27ae60;
        }

        .ativo-nao {
            background: #f5f5f5;
            color: #aaa;
        }

        .ordem-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            background: #2c3e50;
            color: #fff;
            border-radius: 50%;
            font-size: 0.72rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .checkbox-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: #f9f9f9;
            border: 1px solid #dde1e7;
            border-radius: 7px;
            cursor: pointer;
            margin-bottom: 1rem;
            user-select: none;
        }

        .checkbox-toggle input {
            width: auto;
            margin: 0;
        }

        .checkbox-toggle span {
            font-size: 0.9rem;
            color: #444;
            font-weight: 500;
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
                <i class="fa-solid fa-<?= $editData ? 'pen-to-square' : 'film' ?>"></i>
                <?= $editData ? 'Editar Destaque' : 'Novo Destaque do YouTube' ?>
            </h3>

            <form method="POST" id="main-form">
                <input type="hidden" name="id_original" value="<?= htmlspecialchars($editData['youtube_id'] ?? '') ?>">

                <label for="youtube_id">ID do YouTube (Vídeo ou Playlist)</label>
                <input type="text" id="youtube_id" name="youtube_id"
                    value="<?= htmlspecialchars($editData['youtube_id'] ?? '') ?>"
                    placeholder="Ex: a_O4rVvQ1hA ou PLxyz123..."
                    oninput="atualizarPreview(this.value)"
                    required>

                <img id="yt-thumb" class="yt-thumb-preview <?= !empty($editData['youtube_id']) ? 'visible' : '' ?>"
                    src="<?= !empty($editData['thumbnail']) ? htmlspecialchars($editData['thumbnail']) : '' ?>"
                    alt="Thumbnail">

                <label for="titulo" style="margin-top:12px;">Título do Card</label>
                <input type="text" id="titulo" name="titulo"
                    value="<?= htmlspecialchars($editData['titulo'] ?? '') ?>"
                    placeholder="Título que aparecerá no carrossel..." required>

                <div class="grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top:15px;">
                    <div>
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo" onchange="toggleCustomThumb()">
                            <option value="video" <?= (isset($editData['tipo']) && $editData['tipo'] == 'video')    ? 'selected' : '' ?>>🎬 Vídeo Único</option>
                            <option value="playlist" <?= (isset($editData['tipo']) && $editData['tipo'] == 'playlist') ? 'selected' : '' ?>>📋 Playlist / Série</option>
                        </select>
                    </div>
                    <div>
                        <label for="ordem">Ordem de Exibição</label>
                        <input type="number" id="ordem" name="ordem" value="<?= $editData['ordem'] ?? 1 ?>" min="1">
                    </div>
                </div>

                <div id="box-custom-thumb" style="display: <?= (isset($editData['tipo']) && $editData['tipo'] == 'playlist') ? 'block' : 'none' ?>; background: #fdfdfd; border: 1px dashed #ccc; padding: 15px; border-radius: 8px; margin-top: 15px;">
                    <label for="thumbnail_custom" style="color: #2980b9;"><i class="fa-solid fa-image"></i> Link da Capa da Playlist (Opcional)</label>
                    <p style="font-size: 0.75rem; color: #666; margin-bottom: 8px;">Playlists não puxam a capa automaticamente. Cole o link de uma imagem aqui, ou deixe vazio para usar o padrão escuro.</p>
                    <input type="url" id="thumbnail_custom" name="thumbnail_custom" placeholder="Ex: https://ieccp.com.br/img/capa.jpg">
                </div>

                <label class="checkbox-toggle" for="ativo" style="margin-top:15px;">
                    <input type="checkbox" id="ativo" name="ativo" <?= (!isset($editData['ativo']) || $editData['ativo']) ? 'checked' : '' ?>>
                    <span><i class="fa-solid fa-eye"></i> Exibir este conteúdo no site</span>
                </label>

                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <span id="spinner" style="display:none;width:14px;height:14px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;"></span>
                    <i class="fa-solid fa-<?= $editData ? 'floppy-disk' : 'plus' ?>" id="btn-icon"></i>
                    <span id="btn-text"><?= $editData ? 'SALVAR ALTERAÇÕES' : 'ADICIONAR AO CARROSSEL' ?></span>
                </button>

                <?php if ($editData): ?>
                    <a href="painel_destaques.php"><button type="button" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancelar</button></a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-divider" style="margin-top: 40px; margin-bottom: 20px;">
            <h3><i class="fa-solid fa-film"></i> Carrossel <span class="count-badge" style="background: #2c3e50; color: white; padding: 2px 8px; border-radius: 20px; font-size: 0.8rem;"><?= count($list) ?></span></h3>
        </div>

        <div class="items-list">
            <?php if (empty($list)): ?>
                <div class="empty-state" style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px;">
                    <i class="fa-brands fa-youtube" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                    <p style="color: #666;">Nenhum destaque cadastrado ainda.</p>
                </div>
            <?php else: ?>
                <?php foreach ($list as $i): ?>
                    <div class="item" style="display: flex; justify-content: space-between; padding: 15px; border-bottom: 1px solid #eee; align-items: center;">
                        <div class="item-info" style="display: flex; gap: 15px; align-items: center;">
                            <span class="ordem-badge"><?= $i['ordem'] ?></span>
                            <img src="<?= htmlspecialchars($i['thumbnail']) ?>" alt="" style="width:72px;height:44px;object-fit:cover;border-radius:5px; background:#000;">
                            <div>
                                <div class="item-title" style="font-weight: 600;"><?= htmlspecialchars($i['titulo']) ?></div>
                                <div class="item-meta" style="display: flex; gap:6px; margin-top:4px;">
                                    <span class="tipo-badge tipo-<?= $i['tipo'] ?>">
                                        <i class="fa-solid fa-<?= $i['tipo'] === 'playlist' ? 'list' : 'play' ?>"></i>
                                        <?= $i['tipo'] === 'playlist' ? 'Playlist' : 'Vídeo' ?>
                                    </span>
                                    <span class="ativo-badge <?= $i['ativo'] ? 'ativo-sim' : 'ativo-nao' ?>">
                                        <i class="fa-solid fa-<?= $i['ativo'] ? 'eye' : 'eye-slash' ?>"></i>
                                        <?= $i['ativo'] ? 'Visível' : 'Oculto' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="actions" style="display: flex; gap: 10px;">
                            <a href="?editar=<?= urlencode($i['youtube_id']) ?>" class="btn btn-edit" style="background: #f39c12; color:white; padding: 8px 15px; text-decoration:none; border-radius:4px;"><i class="fa-solid fa-pen"></i> Editar</a>
                            <a href="?deletar=<?= urlencode($i['youtube_id']) ?>" class="btn btn-del" onclick="return confirm('Apagar este destaque?')" style="background: #e74c3c; color:white; padding: 8px 15px; text-decoration:none; border-radius:4px;"><i class="fa-solid fa-trash"></i> Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let thumbTimeout;

        function toggleCustomThumb() {
            const tipo = document.getElementById('tipo').value;
            const box = document.getElementById('box-custom-thumb');
            if (tipo === 'playlist') {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }

        function atualizarPreview(yid) {
            clearTimeout(thumbTimeout);
            const thumb = document.getElementById('yt-thumb');
            const clean = yid.trim();
            const selectTipo = document.getElementById('tipo');

            if (!clean) {
                thumb.classList.remove('visible');
                return;
            }

            // Auto-detecta Playlist se o ID começar com PL
            if (clean.startsWith('PL')) {
                selectTipo.value = 'playlist';
                toggleCustomThumb();
            } else {
                selectTipo.value = 'video';
                toggleCustomThumb();
            }

            thumbTimeout = setTimeout(() => {
                if (clean.startsWith('PL')) {
                    // Placeholder visual para Playlist
                    thumb.src = 'https://placehold.co/600x338/2c3e50/FFFFFF?text=PLAYLIST';
                } else {
                    // Preview real para Vídeos
                    thumb.src = 'https://i.ytimg.com/vi/' + clean + '/maxresdefault.jpg';
                }
                thumb.classList.add('visible');
            }, 500);
        }

        document.getElementById('main-form').addEventListener('submit', function() {
            document.getElementById('spinner').style.display = 'inline-block';
            document.getElementById('btn-icon').style.display = 'none';
            document.getElementById('btn-text').textContent = 'Salvando...';
            document.getElementById('btn-submit').disabled = true;
        });

        window.addEventListener('DOMContentLoaded', function() {
            const yid = document.getElementById('youtube_id').value;
            // Apenas executa se não estivermos no meio de uma edição para não sobrescrever a thumb carregada
            if (yid && !document.querySelector('input[name="id_original"]').value) {
                atualizarPreview(yid);
            }
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