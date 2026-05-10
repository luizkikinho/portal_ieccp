const toggleBtn = document.getElementById("btn-tema");
const body = document.body;
const iconSun = '<i class="fa-solid fa-sun"></i>';
const iconMoon = '<i class="fa-solid fa-moon"></i>';

if (toggleBtn) {
  toggleBtn.innerHTML = body.classList.contains("light-mode") ? iconMoon : iconSun;

  toggleBtn.addEventListener("click", () => {
    body.classList.toggle("light-mode");
    const isLight = body.classList.contains("light-mode");
    localStorage.setItem("temaPreferido", isLight ? "light" : "dark");
    toggleBtn.innerHTML = isLight ? iconMoon : iconSun;
  });
}