<?php
// leitura.php — conteúdo via banco: PD, notícias, pastoral e agenda

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/repositorio_noticias.php';
require_once __DIR__ . '/includes/repositorio_pastoral.php';
require_once __DIR__ . '/includes/repositorio_agenda.php';

$id   = $_GET['id'] ?? null;
$slug = $_GET['slug'] ?? null;
$tipo = $_GET['tipo'] ?? 'noticia';

$page_title = "Leitura - IECCP";
$page_desc  = "Conteúdo da IECCP.";
$page_img   = "https://ieccp.com.br/img/logo.webp";
$page_url   = "https://ieccp.com.br/";
$dados      = null;

try {
  // --- PRESENTE DIÁRIO ---
  if ($tipo === 'pd') {
    if ($slug) {
      $stmt = $pdo->query("SELECT * FROM presente_diario ORDER BY data_publicacao DESC");
      $todos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($todos as $row) {
        if (gerarSlug($row['titulo']) === $slug) {
          $dados = $row;
          break;
        }
      }
    } elseif ($id) {
      $stmt = $pdo->prepare("SELECT * FROM presente_diario WHERE id = :id");
      $stmt->execute([':id' => $id]);
      $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($dados) {
      $page_title = $dados['titulo'];
      $page_desc  = resumirTexto($dados['conteudo'] ?? '');
      $page_img   = !empty($dados['imagem']) ? $dados['imagem'] : '/img/capa-padrao-pd.jpg';

      $dados['conteudo_html'] = $dados['conteudo'] ?? '';
      $dados['meta_data'] = !empty($dados['data_publicacao'])
        ? date('d/m/Y', strtotime($dados['data_publicacao']))
        : '';
      $dados['meta_autor'] = $dados['autor'] ?? 'Presente Diário';

      $page_url = "https://ieccp.com.br/pd/" . gerarSlug($dados['titulo']);
    }
  }

  // --- NOTÍCIA ---
  elseif ($tipo === 'noticia') {
    if ($slug) {
      $dados = buscarNoticiaPorSlug($pdo, $slug);
    } elseif ($id) {
      $dados = buscarNoticiaPorId($pdo, (int)$id);
    }

    if ($dados) {
      $page_title = $dados['titulo'];
      $textoFull  = $dados['texto'] ?? '';

      $page_desc = resumirTexto($textoFull);
      $page_img  = formatarImagem($dados['img'] ?? '');

      $dados['conteudo_html'] = htmlspecialchars($textoFull, ENT_NOQUOTES, 'UTF-8');
      $dados['meta_data'] = $dados['data'] ?? '';
      $dados['meta_autor'] = 'Notícia';

      $page_url = "https://ieccp.com.br/noticia/" . ($dados['slug'] ?? gerarSlug($dados['titulo']));
    }
  }

  // --- PASTORAL ---
  elseif ($tipo === 'pastoral') {
    if ($slug) {
      $dados = buscarPastoralPorSlug($pdo, $slug);
    } elseif ($id) {
      $dados = buscarPastoralPorId($pdo, (int)$id);
    }

    if ($dados) {
      $page_title = $dados['titulo'];
      $textoFull  = $dados['texto'] ?? '';

      $page_desc = resumirTexto($textoFull);
      $page_img  = formatarImagem($dados['img'] ?? '');

      $dados['conteudo_html'] = htmlspecialchars($textoFull, ENT_NOQUOTES, 'UTF-8');
      $dados['meta_data'] = $dados['data'] ?? '';
      $dados['meta_autor'] = 'Pastoral';

      $page_url = "https://ieccp.com.br/pastoral/" . ($dados['slug'] ?? gerarSlug($dados['titulo']));
    }
  }

  // --- AGENDA / EVENTO ---
  elseif ($tipo === 'agenda') {
    if ($slug) {
      $dados = buscarEventoPorSlug($pdo, $slug);
    } elseif ($id) {
      $dados = buscarEventoPorId($pdo, (int)$id);
    }

    if ($dados) {
      $page_title = $dados['titulo'];
      $textoFull  = $dados['texto'] ?? '';

      $page_desc = resumirTexto($textoFull);
      $page_img  = formatarImagem($dados['img'] ?? '');

      $dados['conteudo_html'] = htmlspecialchars($textoFull, ENT_NOQUOTES, 'UTF-8');
      $dados['meta_data'] = function_exists('formatarDataAgenda')
        ? formatarDataAgenda($dados)
        : ($dados['data'] ?? '');
      $dados['meta_autor'] = 'Agenda';

      $page_url = "https://ieccp.com.br/evento/" . ($dados['slug'] ?? gerarSlug($dados['titulo']));
    }
  }
} catch (Throwable $e) {
  error_log("Erro no leitura.php: " . $e->getMessage());
  $dados = null;
}
?>

<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/svg+xml" href="/img/favicon2.webp" />

  <title><?php echo htmlspecialchars($page_title); ?></title>

  <meta property="og:type" content="article" />
  <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>" />
  <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>" />
  <meta property="og:image" content="<?php echo htmlspecialchars($page_img); ?>" />
  <meta property="og:url" content="<?php echo htmlspecialchars($page_url); ?>" />
  <meta property="og:site_name" content="IECCP" />

  <link rel="canonical" href="<?php echo htmlspecialchars($page_url); ?>" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="/styles/main.css?v=1.0.6" />

  <script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js" defer></script>

  <style>
    /* ========================================================
       BLINDAGEM TOTAL DO LAYOUT DE LEITURA
       ======================================================== */
    .article-wrapper {
      max-width: 860px;
      margin: 0 auto;
      padding: 60px 20px 100px;
    }

    .article-nav {
      display: block;
      width: 100%;
      margin-bottom: 30px;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: #ffcc00;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      transition: opacity 0.2s ease;
    }

    .back-link:hover {
      opacity: 0.8;
    }

    /* Aqui substituímos a tag <header> por uma div limpa */
    .article-title-section {
      display: block;
      width: 100%;
      margin-bottom: 40px;
      background: transparent;
      /* Remove qualquer fundo indesejado */
      border: none;
      padding: 0;
    }

    .article-title {
      display: block;
      width: 100%;
      font-family: 'Oswald', sans-serif;
      font-size: clamp(2.5rem, 6vw, 4rem);
      /* Aumentei um pouco o impacto do título */
      line-height: 1.1;
      text-transform: uppercase;
      color: #ffffff;
      margin: 0 0 20px 0;
      background: transparent;
    }

    .article-meta {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 15px;
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.95rem;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      width: 100%;
    }

    .article-meta i {
      color: #ffcc00;
    }

    .btn-share {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #fff;
      padding: 6px 14px;
      border-radius: 999px;
      font-family: 'Poppins', sans-serif;
      font-size: 0.85rem;
      cursor: pointer;
      transition: background 0.3s;
      margin-left: auto;
    }

    .btn-share:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .article-cover {
      width: 100%;
      border-radius: 22px;
      overflow: hidden;
      margin-bottom: 40px;
      background: #111;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      display: block;
    }

    .article-cover img {
      width: 100%;
      max-height: 500px;
      object-fit: cover;
      display: block;
    }

    /* Estilização do Markdown */
    .markdown-body {
      color: rgba(255, 255, 255, 0.85);
      font-size: 1.1rem;
      line-height: 1.8;
      font-family: 'Poppins', sans-serif;
    }

    .markdown-body p {
      margin-bottom: 1.5em;
    }

    .markdown-body strong {
      color: #ffffff;
    }

    .markdown-body h2,
    .markdown-body h3 {
      font-family: 'Oswald', sans-serif;
      color: #ffffff;
      text-transform: uppercase;
      margin-top: 2em;
      margin-bottom: 0.8em;
      line-height: 1.3;
    }

    .markdown-body h2 {
      font-size: 2rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      padding-bottom: 10px;
    }

    .markdown-body h3 {
      font-size: 1.5rem;
      color: #ffcc00;
    }

    .markdown-body blockquote {
      border-left: 4px solid #ffcc00;
      padding-left: 20px;
      margin: 2em 0;
      font-style: italic;
      color: rgba(255, 255, 255, 0.7);
      background: rgba(255, 255, 255, 0.03);
      padding: 20px;
      border-radius: 0 12px 12px 0;
    }

    .markdown-body ul,
    .markdown-body ol {
      margin-bottom: 1.5em;
      padding-left: 20px;
    }

    .markdown-body li {
      margin-bottom: 0.5em;
    }

    .markdown-body a {
      color: #ffcc00;
      text-decoration: underline;
    }

    /* Componentes Extra (Áudio e Versículo) */
    .audio-box {
      background: rgba(31, 37, 48, 0.88);
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 18px;
      padding: 20px;
      margin-bottom: 40px;
      display: block;
    }

    .audio-header {
      font-family: 'Oswald', sans-serif;
      text-transform: uppercase;
      color: #ffcc00;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    audio {
      width: 100%;
      margin-bottom: 15px;
    }

    .download-link {
      color: rgba(255, 255, 255, 0.6);
      font-size: 0.85rem;
      text-decoration: none;
    }

    .download-link:hover {
      color: #fff;
    }

    .versiculo-chave {
      background: linear-gradient(135deg, rgba(41, 62, 92, 0.4), rgba(31, 37, 48, 0.88));
      border-left: 4px solid #ffcc00;
      padding: 25px;
      border-radius: 12px;
      margin-bottom: 40px;
      font-size: 1.2rem;
      line-height: 1.6;
      color: #ffffff;
      font-style: italic;
      display: block;
    }

    .versiculo-ref {
      display: block;
      margin-top: 15px;
      font-style: normal;
      color: #ffcc00;
      font-weight: 600;
      font-size: 0.95rem;
    }

    .youversion-box {
      margin-top: 40px;
      padding-top: 30px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      display: block;
    }

    .youversion-link {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, 0.1);
      padding: 12px 24px;
      border-radius: 999px;
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      transition: background 0.3s;
    }

    .youversion-link:hover {
      background: #ffcc00;
      color: #111;
    }
  </style>
</head>

<body>
  <header>
    <a href="/" class="brand">
      <img src="/img/logo.webp" alt="IECCP" onerror="this.src='/img/logo.png'">
      IECCP
    </a>
    <nav aria-label="Navegação principal">
      <a href="/agenda">Agenda</a>
      <a href="/pastoral">Pastoral</a>
      <a href="/noticias">Notícias</a>
      <a href="https://youtube.com/@ieccp" target="_blank">YouTube</a>
      <a href="/missoes">Missões</a>
      <a class="nav-cta" href="/contribua">Contribua</a>
    </nav>
  </header>

  <main class="article-wrapper">
    <nav aria-label="Navegação" class="article-nav">
      <?php if ($tipo === 'pd'): ?>
        <a href="/presente-diario" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para Presente Diário</a>
      <?php elseif ($tipo === 'agenda'): ?>
        <a href="/agenda" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para Agenda</a>
      <?php elseif ($tipo === 'pastoral'): ?>
        <a href="/pastoral" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para Pastoral</a>
      <?php else: ?>
        <a href="/noticias" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para Notícias</a>
      <?php endif; ?>
    </nav>

    <?php if ($dados): ?>

      <article>
        <div class="article-title-section">
          <h1 class="article-title"><?php echo htmlspecialchars($dados['titulo']); ?></h1>

          <div class="article-meta">
            <time>
              <i class="fa-regular fa-calendar"></i>
              <?php echo htmlspecialchars($dados['meta_data'] ?? ''); ?>
            </time>
            <span>• <?php echo htmlspecialchars($dados['meta_autor'] ?? 'IECCP'); ?></span>

            <button onclick="adicionarCompartilhamento()" class="btn-share" title="Partilhar">
              <i class="fa-solid fa-share-nodes"></i> Partilhar
            </button>
          </div>
        </div>

        <?php if (!empty($page_img) && strpos($page_img, 'logo.webp') === false && strpos($page_img, 'capa-padrao-pd.jpg') === false): ?>
          <figure class="article-cover">
            <?php
            $caminho_imagem = (strpos($page_img, 'http') === 0) ? $page_img : '/' . ltrim($page_img, '/');
            ?>
            <img src="<?php echo htmlspecialchars($caminho_imagem); ?>" alt="<?php echo htmlspecialchars($dados['titulo']); ?>" />
          </figure>
        <?php endif; ?>

        <?php if (!empty($dados['audio'])): ?>
          <div class="audio-box">
            <div class="audio-header">
              <i class="fa-solid fa-headphones"></i> Ouça a mensagem
            </div>
            <audio controls>
              <source src="<?php echo htmlspecialchars($dados['audio']); ?>" type="audio/mpeg">
              O seu navegador não suporta áudio.
            </audio>
            <div style="text-align: right;">
              <a href="<?php echo htmlspecialchars($dados['audio']); ?>" target="_blank" download class="download-link">
                <i class="fa-solid fa-download"></i> Baixar MP3
              </a>
            </div>
          </div>
        <?php endif; ?>

        <div class="article-content">
          <?php if (!empty($dados['versiculo_chave'])): ?>
            <div class="versiculo-chave">
              "<?php echo htmlspecialchars($dados['versiculo_chave']); ?>"
              <span class="versiculo-ref">
                📖 <?php echo htmlspecialchars($dados['referencia_biblica'] ?? $dados['referencia_bilbica'] ?? ''); ?>
              </span>
            </div>
          <?php endif; ?>

          <textarea id="markdown-raw" style="display: none;"><?php echo $dados['conteudo_html']; ?></textarea>

          <div id="texto-principal" class="markdown-body"></div>

          <?php if (!empty($dados['youversionLink'])): ?>
            <div class="youversion-box">
              <a href="<?php echo htmlspecialchars($dados['youversionLink']); ?>" target="_blank" class="youversion-link">
                Ler no YouVersion <i class="fa-solid fa-external-link-alt"></i>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </article>

      <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
      <script>
        const rawBox = document.getElementById('markdown-raw');
        const finalBox = document.getElementById('texto-principal');

        if (rawBox && finalBox) {
          const regrasPersonalizadas = {
            code(texto) {
              return '<p>' + texto + '</p>\n';
            },
            codespan(texto) {
              return texto;
            }
          };
          marked.use({
            renderer: regrasPersonalizadas
          });
          finalBox.innerHTML = marked.parse(rawBox.value);
        }
      </script>

    <?php else: ?>

      <div style="text-align: center; padding: 60px 20px; background: rgba(255,255,255,0.02); border-radius: 22px; border: 1px dashed rgba(255,255,255,0.1);">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #ffcc00; margin-bottom: 20px;"></i>
        <h1 class="article-title" style="font-size: 2rem; margin-bottom: 10px;">Conteúdo não encontrado</h1>
        <p style="color: rgba(255,255,255,0.6); margin-bottom: 30px;">O link a que tentou aceder não existe ou foi removido.</p>
        <a href="/" class="btn-share" style="text-decoration:none; display: inline-block; padding: 12px 24px; background: #ffcc00; color: #111; font-weight: bold;">Voltar ao Início</a>
      </div>

    <?php endif; ?>

    <div class="sharethis-share-buttons" data-type="sticky-share-buttons" data-alignment="left" data-labels="none" data-show_total="false" style="margin-top: 40px;"></div>

  </main>

  <nav class="bottom-nav">
    <a href="/" class="nav-item">
      <i class="fa-solid fa-house"></i>
      <span>Início</span>
    </a>
    <a href="/presente-diario" class="nav-item">
      <i class="fa-solid fa-book-bible"></i>
      <span>Devocional</span>
    </a>
    <a href="/pastoral" class="nav-item">
      <i class="fa-solid fa-pen-nib"></i>
      <span>Pastoral</span>
    </a>
    <a href="/noticias" class="nav-item">
      <i class="fa-regular fa-newspaper"></i>
      <span>Notícias</span>
    </a>
    <button class="nav-item btn-more-menu">
      <i class="fa-solid fa-bars"></i>
      <span>Mais</span>
    </button>
  </nav>

  <div class="bottom-sheet-overlay"></div>
  <div class="bottom-sheet">
    <div class="sheet-header">
      <span class="sheet-title">Mais Opções</span>
      <button class="sheet-close"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sheet-content">
      <a href="/agenda"><i class="fa-regular fa-calendar"></i> Agenda e Eventos</a>
      <a href="/missoes"><i class="fa-solid fa-globe"></i> Nossos Missionários</a>
      <a href="https://youtube.com/@ieccp" target="_blank"><i class="fa-brands fa-youtube"></i> Canal do YouTube</a>

      <a href="/contribua" class="sheet-cta">
        <i class="fa-solid fa-heart"></i> Dízimos e Ofertas
      </a>
    </div>
  </div>

  <footer>
    <p>Criado por Matheus Andrade e Luiz Charleaux © 2026 – IECCP</p>
  </footer>
</body>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const btnMore = document.querySelector('.btn-more-menu');
    const btnClose = document.querySelector('.sheet-close');
    const sheet = document.querySelector('.bottom-sheet');
    const overlay = document.querySelector('.bottom-sheet-overlay');

    function toggleSheet() {
      sheet.classList.toggle('active');
      overlay.classList.toggle('active');
    }

    if (btnMore && btnClose && sheet && overlay) {
      btnMore.addEventListener('click', toggleSheet);
      btnClose.addEventListener('click', toggleSheet);
      overlay.addEventListener('click', toggleSheet);
    }
  });
</script>

<script>
  function adicionarCompartilhamento() {
    const linkCurto = window.location.href;
    const titulo = document.title;

    if (navigator.share) {
      navigator.share({
        title: titulo,
        text: 'Confira esta mensagem da IECCP',
        url: linkCurto
      }).catch(console.error);
    } else {
      navigator.clipboard.writeText(linkCurto).then(() => {
        alert("Link copiado com sucesso!");
      }).catch(() => {
        prompt("Copie o link: ", linkCurto);
      });
    }
  }
</script>

</html>