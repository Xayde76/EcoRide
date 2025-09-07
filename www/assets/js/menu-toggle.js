document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.querySelector(".menu-toggle");
  const navLinks = document.querySelector(".nav-links");

  if (toggleButton && navLinks) {
    toggleButton.addEventListener("click", () => {
      navLinks.classList.toggle("show");
    });
  }
});
