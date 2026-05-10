document.addEventListener("DOMContentLoaded", function () {
  console.log("[IECCP PUSH] Script popup.js carregado com sucesso!");

  const STORAGE_KEY = "decisaoNotificacao_v4";
  const modal = document.getElementById("modal-notificacao");
  const btnAceitar = document.getElementById("btn-aceitar");
  const btnRecusar = document.getElementById("btn-agora-nao");

  if (!modal || !btnAceitar || !btnRecusar) {
      console.error("[IECCP PUSH] ERRO: Elementos HTML não encontrados!");
      return;
  }

  function gerenciarExibicaoModal() {
    const registro = localStorage.getItem(STORAGE_KEY);
    console.log("[IECCP PUSH] Valor salvo no LocalStorage:", registro);

    if (registro) {
      console.log("[IECCP PUSH] Abortando: O usuário já tomou uma decisão anteriormente.");
      // DICA: Se quiser forçar a aparecer em todo reload para testar, 
      // descomente a linha abaixo (remova as duas barras //)
      // localStorage.removeItem(STORAGE_KEY);
      return;
    }

    if (window.OneSignalDeferred) {
      window.OneSignalDeferred.push(function (OneSignal) {
        console.log("[IECCP PUSH] OneSignal detectado. Checando permissão do navegador...");
        
        const isOptedIn = OneSignal.User.PushSubscription.optedIn;
        const hasPermission = OneSignal.Notifications.permission;
        
        console.log("[IECCP PUSH] Status OptedIn:", isOptedIn, "| Status Permissão:", hasPermission);

        if (isOptedIn || hasPermission === true) {
          console.log("[IECCP PUSH] Usuário já tem permissão ativa. Escondendo modal e salvando.");
          salvarDecisao("aceitou");
          return;
        }
        
        console.log("[IECCP PUSH] Tudo limpo! Preparando para abrir o modal em 2 segundos...");
        mostrarModal();
      });
    } else {
      console.error("[IECCP PUSH] window.OneSignalDeferred não encontrado no index.php!");
      mostrarModal();
    }
  }

  function mostrarModal() {
    setTimeout(() => {
      console.log("[IECCP PUSH] Mandando exibir o modal agora! (display: flex)");
      modal.style.display = "flex";
    }, 2000);
  }

  function salvarDecisao(status) {
    const dados = { status: status, timestamp: Date.now() };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(dados));
  }

  btnAceitar.addEventListener("click", function () {
    console.log("[IECCP PUSH] Botão SIM clicado.");
    modal.style.display = "none";
    salvarDecisao("aceitou");

    window.OneSignalDeferred.push(async function (OneSignal) {
      try {
        console.log("[IECCP PUSH] Chamando a janelinha nativa de permissão do navegador...");
        await OneSignal.Notifications.requestPermission();
      } catch (err) {
        console.error("[IECCP PUSH] Erro na janelinha nativa:", err);
      }
    });
  });

  btnRecusar.addEventListener("click", function () {
    console.log("[IECCP PUSH] Botão AGORA NÃO clicado.");
    modal.style.display = "none";
    salvarDecisao("recusou");
  });
  
  gerenciarExibicaoModal();
});

// ==========================================
// BANNER DE COOKIES (Mantido)
// ==========================================
document.addEventListener("DOMContentLoaded", function () {
  const banner = document.getElementById("cookie-banner");
  const btnCookie = document.getElementById("btn-cookie-ok");
  
  if (!localStorage.getItem("aceitouCookies") && banner) {
    banner.style.display = "flex";
  }
  
  if (btnCookie) {
    btnCookie.addEventListener("click", function () {
      localStorage.setItem("aceitouCookies", "true");
      if (banner) banner.style.display = "none";
    });
  }
});