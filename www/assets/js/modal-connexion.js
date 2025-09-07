class ModalConnexion {
  constructor(url = `${BASE_URL}/partials/modal-connexion.html`) {
    this.url = url;
    this.container = null;
    this.modal = null;
    this.formSlider = null;
    this.loginForm = null;
    this.registerForm = null;
    this.loginError = null;
    this.registerError = null;

    this.loadModal();
  }

  loadModal() {
    fetch(this.url)
      .then((res) => res.text())
      .then((html) => {
        this.container = document.createElement("div");
        this.container.innerHTML = html;
        document.body.appendChild(this.container);
        this.initElements();
        this.addEventListeners();
      });
  }

  initElements() {
    this.modal = this.container.querySelector("#modal-connexion");
    this.formSlider = this.container.querySelector(".form-slider");
    this.loginForm = this.container.querySelector("#form-login form");
    this.registerForm = this.container.querySelector("#form-register form");

    if (this.loginForm) {
      this.loginError = document.createElement("div");
      this.loginError.id = "login-error";
      Object.assign(this.loginError.style, { color: "red", marginTop: "0.5rem" });
      this.loginForm.appendChild(this.loginError);
    }

    if (this.registerForm) {
      this.registerError = document.createElement("div");
      this.registerError.id = "register-error";
      Object.assign(this.registerError.style, { color: "red", marginTop: "0.5rem" });
      this.registerForm.appendChild(this.registerError);
    }
  }

  addEventListeners() {
    const loginBtn = document.querySelector(".btn-login");
    const closeBtn = this.modal?.querySelector(".close-btn");
    const showRegister = this.container.querySelector("#show-register");
    const showLogin = this.container.querySelector("#show-login");

    if (loginBtn && this.modal && this.formSlider) {
      loginBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.modal.style.display = "flex";
        this.formSlider.style.transform = "translateX(0%)";
        document.querySelector(".nav-links")?.classList.remove("show");
      });
    }

    if (closeBtn && this.modal) {
      closeBtn.addEventListener("click", () => {
        this.modal.style.display = "none";
        this.resetForms();
      });
    }

    if (this.modal) {
      window.addEventListener("click", (e) => {
        if (e.target === this.modal) {
          this.modal.style.display = "none";
          this.resetForms();
        }
      });
    }

    if (showRegister && this.formSlider && this.loginError) {
      showRegister.addEventListener("click", () => {
        this.formSlider.style.transform = "translateX(-50%)";
        this.loginError.textContent = "";
      });
    }

    if (showLogin && this.formSlider && this.registerError) {
      showLogin.addEventListener("click", () => {
        this.formSlider.style.transform = "translateX(0%)";
        this.registerError.textContent = "";
      });
    }

    this.container.querySelectorAll(".toggle-password").forEach((btn) => {
      btn.addEventListener("click", () => {
        const input = btn.previousElementSibling;
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        btn.textContent = isPassword ? "🙈" : "👁️";
      });
    });

    if (this.loginForm && this.loginError) {
      this.loginForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(this.loginForm);
        fetch(`${BASE_URL}/actions/traitement_connexion.php`, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              location.replace(data.redirect || `${BASE_URL}/index.php`);
            } else {
              this.loginError.textContent = data.message || "Erreur inconnue.";
            }
          })
          .catch(() => {
            this.loginError.textContent = "Erreur de connexion au serveur.";
          });
      });
    }

    if (this.registerForm && this.registerError) {
      this.registerForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(this.registerForm);
        fetch(`${BASE_URL}/actions/traitement_inscription.php`, {
          method: "POST",
          body: formData,
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              location.replace(data.redirect || `${BASE_URL}/index.php`);
            } else {
              this.registerError.textContent = data.message || "Erreur inconnue.";
            }
          })
          .catch(() => {
            console.error("Erreur brute :", err);
            this.registerError.textContent = "Erreur de connexion au serveur.";
          });
      });
    }
  }

  resetForms() {
    if (!this.modal) return;

    this.modal.querySelectorAll("form").forEach((form) => form.reset());
    this.modal.querySelectorAll(".toggle-password").forEach((btn) => {
      const input = btn.previousElementSibling;
      if (input && input.type === "text") {
        input.type = "password";
        btn.textContent = "👁️";
      }
    });

    if (this.loginError) this.loginError.textContent = "";
    if (this.registerError) this.registerError.textContent = "";
  }
}