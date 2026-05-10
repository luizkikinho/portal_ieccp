<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contribua — IECCP</title>
    <meta name="description" content="Saiba como contribuir com a obra de Deus em Cachoeira Paulista via Pix ou Transferência.">

    <link rel="icon" type="image/png" href="/img/favicon-96x96.webp" sizes="96x96" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="/styles/main.css?v=1.0.6" />

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
            line-height: 1.6;
        }

        .contrib-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 40px;
            align-items: start;
        }

        /* Card Bancário (Glassmorphism) */
        .bank-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(12px);
        }

        /* Alinhamento Forçado do QR Code */
        .qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .qr-frame {
            background: white;
            padding: 15px;
            border-radius: 18px;
            margin-top: 15px;
        }

        .qr-frame img {
            width: 180px;
            height: 180px;
            display: block;
        }

        .bank-info h3 {
            font-family: 'Oswald', sans-serif;
            color: var(--secondary);
            font-size: 1.4rem;
            margin-bottom: 5px;
        }

        .bank-info p {
            color: var(--muted);
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .pix-copy-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 204, 0, 0.4);
            border-radius: var(--radius-sm);
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }

        .pix-key {
            font-family: monospace;
            font-weight: 700;
            color: var(--secondary);
            font-size: 1.1rem;
        }

        .btn-copy {
            background: var(--secondary);
            color: #111;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
            font-size: 1rem;
        }

        .btn-copy:hover {
            transform: scale(1.05);
        }

        /* Lista de Códigos/Centavos */
        .info-section h2 {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8rem;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .cents-list {
            list-style: none;
            background: rgba(255, 255, 255, 0.02);
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .cents-item {
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
        }

        .cents-item:last-child {
            border: none;
        }

        .cents-value {
            color: var(--secondary);
            font-weight: 700;
            font-family: monospace;
            font-size: 1.05rem;
        }

        @media (max-width: 920px) {
            .contrib-grid {
                grid-template-columns: 1fr;
            }
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
            <a href="/presente-diario">Presente Diário</a>
            <a href="/missoes">Missões</a>
            <a class="nav-cta" href="/contribua" style="background: #fff; color: #111;">Contribua</a>
        </nav>
    </header>

    <main class="section-wrap">
        <div class="page-header">
            <div class="section-kicker">Fidelidade</div>
            <h1>Dízimos e Ofertas</h1>
            <p>A sua contribuição sustenta a obra missionária, a manutenção do templo e as ações sociais da nossa igreja.</p>
        </div>

        <div class="contrib-grid">
            <aside class="bank-card">
                <div class="qr-section">
                    <span class="eyebrow"><i class="fa-solid fa-qrcode"></i> Via PIX</span>
                    <div class="qr-frame">
                        <img src="/img/qrcode-pix.png" alt="QR Code Pix IECCP" onerror="this.style.display='none'">
                    </div>
                </div>

                <div class="bank-info">
                    <h3>SICREDI S.A.</h3>
                    <p>Agência: 0710 | Conta: 17218-5</p>
                    <p style="font-size: 0.8rem; opacity: 0.7; margin-top: 5px;">CNPJ: 45.890.977/0001-60</p>
                </div>

                <div class="pix-copy-box">
                    <span class="pix-key" id="texto-pix">45.890.977/0001-60</span>
                    <button class="btn-copy" onclick="copiarPix()" title="Copiar Chave">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>

                <div style="margin-top: 30px; text-align: center;">
                    <a href="https://wa.me/+551231012589" target="_blank" class="section-link" style="display: flex; justify-content: center; padding: 12px; border: 1px solid rgba(255,255,255,0.1); border-radius: 999px;">
                        <i class="fa-brands fa-whatsapp"></i> Seja um Mantenedor
                    </a>
                </div>
            </aside>

            <section class="info-section">
                <h2>Identificação de Ofertas</h2>
                <p style="margin-bottom: 25px; color: var(--muted); line-height: 1.6;">Para garantir que o seu recurso seja direcionado corretamente, adicione os seguintes centavos no final do valor da sua contribuição:</p>

                <div class="cents-list">
                    <div class="cents-item">
                        <span>Evangelismo e Missões</span>
                        <span class="cents-value">R$ XX,10</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários Alex & Bete</span>
                        <span class="cents-value">R$ XX,11</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários Aurino & Dani</span>
                        <span class="cents-value">R$ XX,12</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários Claudecy & Neria</span>
                        <span class="cents-value">R$ XX,13</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários John & Rebecca</span>
                        <span class="cents-value">R$ XX,14</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários Ricardo & Flavia</span>
                        <span class="cents-value">R$ XX,15</span>
                    </div>
                    <div class="cents-item">
                        <span>Missionários Richard & Yohana</span>
                        <span class="cents-value">R$ XX,16</span>
                    </div>
                    <div class="cents-item" style="border-top: 2px solid rgba(255,204,0,0.2); margin-top: 10px; padding-top: 15px;">
                        <strong>Ação Social</strong>
                        <span class="cents-value">R$ XX,30</span>
                    </div>
                    <div class="cents-item">
                        <strong>Construção / Reformas</strong>
                        <span class="cents-value">R$ XX,70</span>
                    </div>
                </div>
            </section>
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

<script>
    function copiarPix() {
        const textoPix = document.getElementById('texto-pix').innerText;
        navigator.clipboard.writeText(textoPix).then(() => {
            const btn = document.querySelector('.btn-copy');
            const icon = btn.querySelector('i');

            icon.classList.replace('fa-copy', 'fa-check');
            btn.style.background = "#28a745";
            btn.style.color = "#fff";

            setTimeout(() => {
                icon.classList.replace('fa-check', 'fa-copy');
                btn.style.background = "var(--secondary)";
                btn.style.color = "#111";
            }, 2000);
        });
    }
</script>

</html>