const btnMobile = document.getElementById("btn-mobile");
const menu = document.getElementById("menu");

function toggleMenu(event) {
  if (event && event.type === "touchstart") event.preventDefault();

  // Troca o estado (Abre/Fecha)
  menu.classList.toggle("active");
  btnMobile.classList.toggle("active");

  // Acessibilidade: avisa o navegador se está aberto ou não
  const active = menu.classList.contains("active");
  btnMobile.setAttribute("aria-expanded", active);

  // Trava a rolagem da página quando o menu está aberto (opcional, mas recomendado)
  if (active) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "";
  }
}

// Eventos de clique no botão hambúrguer
btnMobile.addEventListener("click", toggleMenu);
btnMobile.addEventListener("touchstart", toggleMenu);

// --- NOVA FUNCIONALIDADE: FECHAR SOZINHO ---

// 1. Fechar ao clicar em qualquer Link do menu
// (Isso é essencial para quando a pessoa clica em "Agenda", o menu sair da frente)
const links = document.querySelectorAll("#menu a");
links.forEach((link) => {
  link.addEventListener("click", () => {
    closeMenu();
  });
});

// 2. Fechar ao clicar na "Parte Vazia" (Overlay)
// Como seu menu ocupa 100% da tela, a "parte vazia" é o próprio elemento <ul>
document.addEventListener("click", (event) => {
  // Se o menu estiver aberto...
  if (menu.classList.contains("active")) {
    // E o clique NÃO foi dentro do botão de abrir...
    if (!btnMobile.contains(event.target)) {
      // Verifica se clicou direto no fundo do menu (o espaço vazio entre os links)
      if (event.target === menu) {
        closeMenu();
      }
    }
  }
});

// Função auxiliar para fechar tudo limpo
function closeMenu() {
  menu.classList.remove("active");
  btnMobile.classList.remove("active");
  document.body.style.overflow = ""; // Destrava a rolagem
}
