<?php
// index.php
// O arquivo agora é 100% visual no carregamento inicial.
// Todas as chamadas para o banco de dados foram isoladas nas APIs.
?>
<!doctype html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IECCP — Uma família de fé</title>
  <meta name="description" content="Igreja Evangélica Congregacional de Cachoeira Paulista.">

  <link rel="icon" type="image/png" href="/img/favicon-96x96.webp" sizes="96x96" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <link rel="stylesheet" href="/styles/main.css?v=1.0.6" />
  <link rel="stylesheet" href="styles/home.css?v=1.0.1">
  <link rel="stylesheet" href="styles/components/card.css?v=1.0.1">
  <link rel="stylesheet" href="styles/components/skeleton.css?v=1.0.0">
</head>

<body>

  <header>
    <a href="/" class="brand">
      <img src="/img/logo.webp" alt="IECCP" onerror="this.src='/img/logo.png'">
      IECCP
    </a>

    <nav aria-label="Navegação principal" class="desktop-nav">
      <a href="/agenda">Agenda</a>
      <a href="/pastoral">Pastoral</a>
      <a href="/noticias">Notícias</a>
      <a href="https://youtube.com/@ieccp" target="_blank">YouTube</a>
      <a href="/missoes">Missões</a>
      <a class="nav-cta" href="/contribua">Contribua</a>
    </nav>
  </header>

  <section class="hero" id="inicio">
    <div class="hero-grid">
      <div class="hero-copy">
        <span class="eyebrow"><i class="fa-solid fa-church"></i> Uma família de fé</span>
        <h1>Glorificar,<br><span>Edificar</span><br>& Proclamar</h1>
        <p>Uma comunidade viva em Cachoeira Paulista, pronta para te receber de braços abertos.</p>
        <div class="hero-actions">
          <a class="btn btn-primary" href="#agenda-eventos"><i class="fa-regular fa-calendar"></i> Ver programação</a>
          <a class="btn btn-ghost" href="#youtube"><i class="fa-brands fa-youtube"></i> Assistir mensagens</a>
        </div>
      </div>

      <aside class="hero-side">
        <div class="info-card">
          <h2>Nossos Horários</h2>
          <div class="time-row"><i class="fa-solid fa-bible"></i><span>Dom 09h — EBD</span></div>
          <div class="time-row"><i class="fa-solid fa-bible"></i><span>Dom 10h20 — Culto da Manhã</span></div>
          <div class="time-row"><i class="fa-solid fa-church"></i><span>Dom 18h30 — Culto da Noite</span></div>
          <div class="time-row"><i class="fa-solid fa-hands-praying"></i><span>Qua 07h30 — Reunião de Oração</span></div>
        </div>

        <div id="pd-container">
          <div class="info-card verse-card skeleton-card" style="padding-bottom: 24px;">
            <h3>Presente Diário</h3>
            <div class="skeleton-box skeleton-text"></div>
            <div class="skeleton-box skeleton-text short"></div>
          </div>
        </div>
      </aside>
    </div>
  </section>

  <main>
    <section class="section-wrap" id="visitar">
      <div class="feature-grid">
        <div class="feature-card">
          <div>
            <span class="eyebrow">Bem-vindo</span>
            <h2>Existe lugar para você aqui.</h2>
            <p>Junte-se a nós para adorar a Deus, aprender da Sua Palavra e viver em comunhão.</p>
          </div>
        </div>
        <div class="quick-stack">
          <a href="https://maps.app.goo.gl/pz23waNTkSb8d4Ru7" target="_blank" class="quick-card">
            <i class="fa-solid fa-location-dot"></i>
            <h3>Onde estamos</h3>
            <p>R. Conselheiro Rodrigues Alves, 358 — Cachoeira Paulista/SP.</p>
          </a>
          <a href="/contribua" class="quick-card">
            <i class="fa-solid fa-heart"></i>
            <h3>Contribua</h3>
            <p>Veja como ofertar via Pix, transferência ou ser um mantenedor.</p>
          </a>
        </div>
      </div>
    </section>

    <section class="section-wrap" id="youtube">
      <div class="section-head">
        <div>
          <div class="section-kicker">Mídia</div>
          <h2 class="section-title">Destaques do YouTube</h2>
        </div>
        <a href="https://youtube.com/@ieccp" class="section-link" target="_blank">Ver canal <i class="fa-solid fa-arrow-right-long"></i></a>
      </div>
      <div class="rail" id="youtube-rail">
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
      </div>
    </section>

    <section class="section-wrap" id="agenda-eventos">
      <div class="section-head">
        <div>
          <div class="section-kicker">Próximos passos</div>
          <h2 class="section-title">Agenda e Eventos</h2>
        </div>
        <a href="/agenda" class="section-link">Ver agenda <i class="fa-solid fa-arrow-right-long"></i></a>
      </div>
      <div class="rail" id="agenda-rail">
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
      </div>
    </section>

    <section class="section-wrap" id="pastoral-secao">
      <div class="section-head">
        <div>
          <div class="section-kicker">Edificação</div>
          <h2 class="section-title">Palavra Pastoral</h2>
        </div>
        <a href="/pastoral" class="section-link">Ler mais <i class="fa-solid fa-arrow-right-long"></i></a>
      </div>
      <div class="rail" id="pastoral-rail">
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
      </div>
    </section>

    <section class="section-wrap" id="noticias-secao">
      <div class="section-head">
        <div>
          <div class="section-kicker">Comunidade</div>
          <h2 class="section-title">Últimas Notícias</h2>
        </div>
        <a href="/noticias" class="section-link">Ver todas <i class="fa-solid fa-arrow-right-long"></i></a>
      </div>
      <div class="rail" id="noticias-rail">
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
        <article class="card skeleton-card"><div class="card-media skeleton-box skeleton-media"></div><div class="card-body"><div class="skeleton-box skeleton-text"></div><div class="skeleton-box skeleton-text short"></div><div class="skeleton-box skeleton-btn"></div></div></article>
      </div>
    </section>
  </main>

  <nav class="bottom-nav">
    <a href="/" class="nav-item active"><i class="fa-solid fa-house"></i><span>Início</span></a>
    <a href="/presente-diario" class="nav-item"><i class="fa-solid fa-book-bible"></i><span>Devocional</span></a>
    <a href="/pastoral" class="nav-item"><i class="fa-solid fa-pen-nib"></i><span>Pastoral</span></a>
    <a href="/noticias" class="nav-item"><i class="fa-regular fa-newspaper"></i><span>Notícias</span></a>
    <button class="nav-item btn-more-menu"><i class="fa-solid fa-bars"></i><span>Mais</span></button>
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
      <a href="/contribua" class="sheet-cta"><i class="fa-solid fa-heart"></i> Dízimos e Ofertas</a>
    </div>
  </div>

  <footer>
    <p>Criado por Matheus Andrade e Luiz Charleaux © 2026 – IECCP</p>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. MENU MOBILE
      const btnMore = document.querySelector('.btn-more-menu');
      const btnClose = document.querySelector('.sheet-close');
      const sheet = document.querySelector('.bottom-sheet');
      const overlay = document.querySelector('.bottom-sheet-overlay');

      function toggleSheet() {
        if(sheet && overlay) {
          sheet.classList.toggle('active');
          overlay.classList.toggle('active');
        }
      }

      if (btnMore && btnClose && overlay) {
        btnMore.addEventListener('click', toggleSheet);
        btnClose.addEventListener('click', toggleSheet);
        overlay.addEventListener('click', toggleSheet);
      }

      // 2. FUNÇÕES ÚTEIS
      const escapeHTML = (str) => {
        if (!str) return '';
        return str.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
      };

      const gerarSlug = (str) => {
        if (!str) return '';
        return str.toString().toLowerCase().trim()
          .replace(/[\s\W-]+/g, '-')
          .replace(/^-+|-+$/g, '');
      };

      const formatarImagem = (img) => {
        return img ? escapeHTML(img) : '/img/logo.webp';
      };

      // Função genérica para carregar as rails (Notícias, Agenda, Pastoral, YouTube)
      const carregarRail = (url, containerId, renderCallback) => {
        fetch(url)
          .then(res => res.json())
          .then(json => {
            const container = document.getElementById(containerId);
            container.innerHTML = ''; // Limpa os skeletons

            // Lida com retornos diferentes (Array direto ou json.data)
            let dataArray = [];
            if (Array.isArray(json)) {
              dataArray = json;
            } else if (json.data && Array.isArray(json.data)) {
              dataArray = json.data;
            }

            if (dataArray.length === 0) {
              container.innerHTML = '<p style="color: var(--muted);">Nenhum conteúdo publicado no momento.</p>';
              return;
            }

            // Injeta os cards gerados pelo callback
            dataArray.forEach(item => {
              container.innerHTML += renderCallback(item);
            });
          })
          .catch(err => {
            console.error(`Erro ao carregar ${containerId}:`, err);
            document.getElementById(containerId).innerHTML = '<p style="color: var(--muted);">Erro ao carregar os dados.</p>';
          });
      };

      // 3. FETCH PRESENTE DIÁRIO (Específico pois não é uma rail)
      fetch('/api/presente_diario.php')
        .then(res => res.json())
        .then(json => {
          const container = document.getElementById('pd-container');
          if (json.status === 'success' && json.data) {
            const pd = json.data;
            const linkHref = pd.slug === 'presente-diario' ? '/presente-diario' : `/pd/${escapeHTML(pd.slug)}`;
            container.innerHTML = `
              <a class="info-card verse-card" href="${linkHref}" style="display: block; text-decoration: none;">
                <h3>Presente Diário</h3>
                <p>“${escapeHTML(pd.texto)}”</p>
                <span class="verse-ref">${escapeHTML(pd.ref)} &rarr;</span>
              </a>
            `;
          } else {
            throw new Error("Formato inválido ou vazio");
          }
        })
        .catch(err => {
          console.error('Falha ao carregar Presente Diário:', err);
          document.getElementById('pd-container').innerHTML = `
            <a class="info-card verse-card" href="/presente-diario">
              <h3>Presente Diário</h3>
              <p>Acesse nossa página para ler a reflexão de hoje.</p>
            </a>
          `;
        });

      // 4. FETCH YOUTUBE
      carregarRail('/api/destaques_youtube.php', 'youtube-rail', (vid) => {
        const link = escapeHTML(vid.link || '#');
        const badgeHTML = vid.tipo === 'playlist' ? '<span class="badge">Playlist</span>' : '';
        return `
          <article class="card">
            <a href="${link}" target="_blank" class="card-link">
              <div class="card-media">
                <img src="${formatarImagem(vid.thumbnail)}" alt="${escapeHTML(vid.titulo)}">
                ${badgeHTML}
              </div>
              <div class="card-body">
                <h3 class="card-title">${escapeHTML(vid.titulo || 'Vídeo IECCP')}</h3>
                <span class="read-more">Assistir agora <i class="fa-solid fa-play"></i></span>
              </div>
            </a>
          </article>
        `;
      });

      // 5. FETCH AGENDA
      carregarRail('/api/agenda.php', 'agenda-rail', (ev) => {
        const slug = ev.slug ? escapeHTML(ev.slug) : gerarSlug(ev.titulo);
        // Fallback de formatação de data caso venha cru do banco
        let dataExibicao = ev.data_inicio_formatada || escapeHTML(ev.data); 
        return `
          <article class="card">
            <a href="/evento/${slug}" class="card-link">
              <div class="card-media">
                <img src="${formatarImagem(ev.img)}" alt="${escapeHTML(ev.titulo)}">
                <span class="badge">${dataExibicao}</span>
              </div>
              <div class="card-body">
                <h3 class="card-title">${escapeHTML(ev.titulo)}</h3>
                <div class="card-meta"><i class="fa-solid fa-location-dot"></i> ${escapeHTML(ev.local || 'IECCP')}</div>
              </div>
            </a>
          </article>
        `;
      });

      // 6. FETCH PASTORAL
      carregarRail('/api/pastoral.php', 'pastoral-rail', (item) => {
        const slug = item.slug ? escapeHTML(item.slug) : gerarSlug(item.titulo);
        return `
          <article class="card">
            <a href="/pastoral/${slug}" class="card-link">
              <div class="card-media">
                <img src="${formatarImagem(item.img)}" alt="${escapeHTML(item.titulo)}">
                <span class="badge">${escapeHTML(item.data || '')}</span>
              </div>
              <div class="card-body">
                <h3 class="card-title">${escapeHTML(item.titulo)}</h3>
                <span class="read-more">Ler reflexão <i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </a>
          </article>
        `;
      });

      // 7. FETCH NOTÍCIAS
      carregarRail('/api/noticias.php', 'noticias-rail', (item) => {
        const slug = item.slug ? escapeHTML(item.slug) : gerarSlug(item.titulo);
        return `
          <article class="card">
            <a href="/noticia/${slug}" class="card-link">
              <div class="card-media">
                <img src="${formatarImagem(item.img)}" alt="${escapeHTML(item.titulo)}">
                <span class="badge">${escapeHTML(item.data || '')}</span>
              </div>
              <div class="card-body">
                <h3 class="card-title">${escapeHTML(item.titulo)}</h3>
                <span class="read-more">Ler mais <i class="fa-solid fa-arrow-right"></i></span>
              </div>
            </a>
          </article>
        `;
      });

    });
  </script>
</body>
</html>