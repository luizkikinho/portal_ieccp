<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/repositorio_agenda.php';

// Helper de escape para segurança
function e($value): string
{
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$agenda = [];
try {
  $agenda = listarEventos($pdo);
} catch (Throwable $e) {
  error_log('Erro ao carregar agenda: ' . $e->getMessage());
}
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Agenda — IECCP</title>

  <link rel="icon" type="image/svg+xml" href="/img/favicon2.png" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

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
  </style>
</head>

<body>
  <header>
    <a href="/" class="brand">
      <img src="/img/logo.webp" alt="IECCP" onerror="this.src='/img/logo.png'">
      IECCP
    </a>
    <nav aria-label="Navegação principal">
      <a href="/agenda" class="active" style="color: var(--secondary);">Agenda</a>
      <a href="/pastoral">Pastoral</a>
      <a href="/noticias">Notícias</a>
      <a href="/presente-diario">Presente Diário</a>
      <a href="/missoes">Missões</a>
      <a class="nav-cta" href="/contribua">Contribua</a>
    </nav>
  </header>

  <main class="section-wrap">
    <div class="page-header">
      <div class="section-kicker">Programação</div>
      <h1>Agenda e Eventos</h1>
      <p>Fique por dentro de tudo o que acontece na nossa comunidade.</p>
    </div>

    <div class="grid-container">
      <?php if (empty($agenda)): ?>
        <p style="grid-column: 1/-1; text-align: center; color: var(--muted);">Nenhum evento agendado no momento.</p>
      <?php else: ?>
        <?php foreach ($agenda as $ev):
          $link = "/evento/" . (!empty($ev['slug']) ? $ev['slug'] : gerarSlug($ev['titulo']));
        ?>
          <article class="card">
            <a href="<?= e($link) ?>" class="card-link">
              <div class="card-media">
                <img src="<?= e(formatarImagem($ev['img'] ?? '')) ?>" alt="<?= e($ev['titulo']) ?>" loading="lazy" onerror="this.src='/img/logo.webp';">
                <span class="badge"><?= e(formatarDataAgenda($ev)) ?></span>
              </div>
              <div class="card-body">
                <h3 class="card-title"><?= e($ev['titulo']) ?></h3>

                <div class="card-meta" style="margin-top: auto;">
                  <i class="fa-solid fa-location-dot"></i>
                  <span><?= e($ev['local'] ?? 'IECCP') ?></span>
                </div>
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

</html>