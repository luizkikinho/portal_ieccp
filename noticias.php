<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/repositorio_noticias.php';

// Helper de escape para segurança
function e($value): string
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$noticias = [];

try {
  $noticias = listarNoticias($pdo);
} catch (Throwable $e) {
  error_log('Erro ao carregar notícias: ' . $e->getMessage());
}

// Limpeza de Markdown para o resumo
function resumoNoticia(string $texto, int $limite = 150): string
{
  $texto = trim(strip_tags($texto));
  $texto = str_replace(['**', '*', '__', '_'], '', $texto);

  if (mb_strlen($texto, 'UTF-8') <= $limite) {
    return $texto;
  }
  return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
}
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Notícias — IECCP</title>
  <meta name="description" content="Notícias, avisos e atividades da Igreja Evangélica Congregacional de Cachoeira Paulista." />

  <link rel="icon" type="image/png" href="/img/favicon-96x96.webp" sizes="96x96" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <link rel="stylesheet" href="/styles/main.css?v=1.0.6" />
  <link rel="stylesheet" href="/styles/components/card.css?v=1.0.1" />

  <style>
    .page-header {
      text-align: center;
      padding: 40px 0 30px;
    }

    .page-header h1 {
      font-family: 'Oswald', sans-serif;
      font-size: clamp(2rem, 4vw, 3rem);
      text-transform: uppercase;
      margin-bottom: 10px;
    }

    .page-header p {
      color: var(--muted);
      max-width: 600px;
      margin: 0 auto;
    }

    .grid-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
    }

    .grid-container .card {
      scroll-snap-align: none;
    }

    .card-summary {
      color: var(--muted);
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 15px;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>

<body>
  <header>
    <a href="/" class="brand">
      <img src="/img/logo.webp" alt="IECCP" onerror="this.src='/img/logo.png';" />
      IECCP
    </a>

    <nav aria-label="Navegação principal">
      <a href="/agenda">Agenda</a>
      <a href="/pastoral">Pastoral</a>
      <a href="/noticias" class="active" style="color: var(--secondary);">Notícias</a>
      <a href="/presente-diario">Presente Diário</a>
      <a href="/missoes">Missões</a>
      <a class="nav-cta" href="/contribua">Contribua</a>
    </nav>
  </header>

  <main class="section-wrap">
    <div class="page-header">
      <div class="section-kicker">Comunidade</div>
      <h1>Notícias e Atividades</h1>
      <p>Acompanhe os avisos, registros e novidades da caminhada da IECCP.</p>
    </div>

    <div class="grid-container">
      <?php if (empty($noticias)): ?>
        <p style="grid-column: 1/-1; text-align: center; color: var(--muted);">Nenhuma notícia encontrada no momento.</p>
      <?php else: ?>
        <?php foreach ($noticias as $item): ?>
          <?php
          $titulo = trim((string)($item['titulo'] ?? 'Aviso Importante'));
          $slug = trim((string)($item['slug'] ?? ''));
          if ($slug === '') {
            $slug = gerarSlug($titulo);
          }
          $link = '/noticia/' . $slug;
          $imagem = formatarImagem($item['img'] ?? '');
          $resumo = resumoNoticia((string)($item['texto'] ?? ''), 140);
          $data = (string)($item['data'] ?? '');
          ?>
          <article class="card">
            <a href="<?= e($link) ?>" class="card-link">
              <div class="card-media">
                <img src="<?= e($imagem) ?>" alt="<?= e($titulo) ?>" loading="lazy" onerror="this.src='/img/logo.webp';" />
                <?php if ($data !== ''): ?>
                  <span class="badge"><?= e($data) ?></span>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <h3 class="card-title"><?= e($titulo) ?></h3>
                <?php if ($resumo !== ''): ?>
                  <p class="card-summary"><?= e($resumo) ?></p>
                <?php endif; ?>
                <span class="read-more" style="margin-top: auto;">
                  Ler mais <i class="fa-solid fa-arrow-right-long"></i>
                </span>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
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
    <a href="/noticias" class="nav-item active">
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

</html>