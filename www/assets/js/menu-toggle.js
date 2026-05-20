document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.querySelector(".menu-toggle");
  const navLinks = document.querySelector(".nav-links");

  if (!toggleButton || !navLinks) return;

  toggleButton.addEventListener("click", (e) => {
    e.stopPropagation();
    navLinks.classList.toggle("show");
  });

  document.addEventListener("click", (e) => {
    if (navLinks.classList.contains("show") && !navLinks.contains(e.target)) {
      navLinks.classList.remove("show");
    }
  });
});
