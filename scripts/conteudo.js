document.addEventListener("DOMContentLoaded", () => {
  initNoticias();
  initMissionarios();
  initAgenda();
  initPastoral();

  if (document.getElementById("feed-container")) {
    carregarFeedNoticias();
  }
});

// --- FUNÇÃO DE BUSCA ---
async function fetchData(url) {
  try {
    const urlComCache = `${url}?v=${Date.now()}`;
    const res = await fetch(urlComCache);
    if (!res.ok) throw new Error(`Erro ao carregar ${url}`);
    return await res.json();
  } catch (err) {
    console.warn(`Erro ou lista vazia em ${url}:`, err);
    return [];
  }
}

// --- AGENDA (ATUALIZADA COM HORA E DATA FIM) ---
async function initAgenda() {
  const dados = await fetchData("data/agenda.json");
  const container = document.getElementById("container-agenda");
  const template = document.getElementById("template-agenda");

  if (!container || !template) return;
  container.innerHTML = "";

  if (!dados || dados.length === 0) {
    container.innerHTML =
      "<p class='text-center w-100'>Nenhum evento agendado.</p>";
    return;
  }

  // Pega os próximos 6 eventos
  dados.slice(0, 6).forEach((ev) => {
    const clone = template.content.cloneNode(true);

    const img = clone.querySelector("img");
    if (img) {
      img.src = formatarImagem(ev.img);
      img.alt = ev.titulo;
    }

    // --- NOVA LÓGICA DE DATA E HORA ---
    const textoData = formatarDataEvento(ev);
    setText(clone, ".date-badge", textoData);

    // Título e Local
    setText(clone, ".desc", ev.titulo);
    setText(clone, ".texto-local", ev.local);

    // Remove descrição curta da home (mantém limpo)
    const descEl = clone.querySelector(".short-desc");
    if (descEl) descEl.style.display = "none";

    // Link
    const linkWrap = clone.querySelector(".card-link-wrapper");
    if (linkWrap && ev.id)
      linkWrap.href = `leitura.php?id=${ev.id}&tipo=agenda`;

    container.appendChild(clone);
  });
}

// --- FUNÇÃO INTELIGENTE PARA FORMATAR DATA/HORA ---
function formatarDataEvento(ev) {
  // Pega os dados novos OU o antigo (fallback)
  const inicio = ev.data_inicio || ev.data;
  const fim = ev.data_fim;
  const hora = ev.hora_inicio;

  if (!inicio) return "";

  // Remove o ano (ex: 2026) para economizar espaço no card, se quiser
  // Aqui vamos pegar só os 5 primeiros chars (dd/mm)
  const dataCurta = inicio.substring(0, 5);

  // CENÁRIO 1: Evento de vários dias (Retiro/Acampamento)
  if (fim && fim !== inicio) {
    const fimCurto = fim.substring(0, 5);
    return `${dataCurta} a ${fimCurto}`;
  }

  // CENÁRIO 2: Evento com Hora (Culto/Reunião)
  if (hora) {
    return `${dataCurta} • ${hora}`;
  }

  // CENÁRIO 3: Só data simples
  return dataCurta;
}

// --- PASTORAL ---
async function initPastoral() {
  const dados = await fetchData("data/pastoral.json");
  if (dados && dados.length > 0) {
    renderizarCards(dados, "container-pastoral", "template-pastoral", 4);
    renderizarCards(dados, "container-todos-pastoral", "template-pastoral");
  }
}

// --- NOTÍCIAS ---
async function initNoticias() {
  const dados = await fetchData("api/noticias.php");
  if (!dados) return;
  renderizarCards(dados, "container-noticias", "template-padrao", 4);
  renderizarCards(dados, "container-todas-noticias", "template-padrao");
}

async function initMissionarios() {
  const dados = await fetchData("data/missionarios.json");
  if (dados)
    renderizarCards(dados, "container-missionarios", "template-missionarios");
}

// --- RENDERIZADOR UNIVERSAL ---
function renderizarCards(lista, idContainer, idTemplate, maxItems = null) {
  const container = document.getElementById(idContainer);
  const template = document.getElementById(idTemplate);
  if (!container || !template) return;

  container.innerHTML = "";
  const safeList = Array.isArray(lista) ? lista : [];
  const itens = maxItems ? safeList.slice(0, maxItems) : safeList;

  itens.forEach((item) => {
    const clone = template.content.cloneNode(true);

    const img = clone.querySelector("img");
    if (img) {
      img.src = formatarImagem(item.img);
      img.alt = item.titulo || "Imagem";
      img.onerror = function () {
        this.src = "img/logo.png";
      };
    }

    const linkWrap = clone.querySelector(".card-link-wrapper");
    if (linkWrap) {
      let tipo = "noticia";
      if (idContainer.includes("pastoral")) tipo = "pastoral";
      if (idContainer.includes("agenda")) tipo = "agenda";

      if (tipo === "noticia" && item.slug) {
        linkWrap.href = `/noticia/${item.slug}`;
      } else if (item.id) {
        linkWrap.href = `leitura.php?id=${item.id}&tipo=${tipo}`;
      } else {
        linkWrap.href = item.link || "#";
      }

      linkWrap.setAttribute("aria-label", `Ler: ${item.titulo || "conteúdo"}`);
    }

    setText(clone, ".desc", item.titulo);

    const descEl = clone.querySelector(".short-desc");
    if (descEl) descEl.remove();

    container.appendChild(clone);
  });
}

// --- FEED DE NOTÍCIAS COMPLETO ---
async function carregarFeedNoticias() {
  const container = document.getElementById("feed-container");
  const template = document.getElementById("template-noticia");
  if (!container || !template) return;

  const dados = await fetchData("api/noticias.php");
  container.innerHTML = "";
  if (!dados || !dados.length) {
    container.innerHTML = "<p>Nenhuma notícia.</p>";
    return;
  }

  dados.forEach((item) => {
    const clone = template.content.cloneNode(true);

    const link = clone.querySelector(".noticia-link");
    if (link) link.href = `leitura.php?id=${item.id}&tipo=noticia`;

    setText(clone, "h2", item.titulo);
    setText(clone, ".data-badge", item.data);

    const img = clone.querySelector("img");
    if (item.img && img) img.src = formatarImagem(item.img);

    const divTexto = clone.querySelector(".texto-dinamico");
    const texto = item.texto || item.descricao || "";
    if (divTexto) divTexto.innerText = texto.substring(0, 200) + "...";

    container.appendChild(clone);
  });
}

// --- HELPERS ---
function setText(el, selector, text) {
  const target = el.querySelector(selector);
  if (target) target.innerText = text || "";
}

function formatarImagem(src) {
  if (!src) return "img/logo.png";
  return src.replace("../", "").replace(/^https?:\/\/ieccp\.com\.br\//, "");
}
