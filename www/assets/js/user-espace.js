function esc(str) {
  const d = document.createElement('div');
  d.textContent = String(str ?? '');
  return d.innerHTML;
}

class EspaceUtilisateur {
  constructor() {
    this.roleSelect = document.getElementById("role");
    this.chauffeurSection = document.getElementById("chauffeur-info");
    this.roleMessage = document.getElementById("role-message");
    this.voyageSection = document.getElementById("voyage-creation");
    this.formVoyage = document.getElementById("form-voyage");
    this.messageVoyage = document.getElementById("message-voyage");

    this.init();
  }

  init() {
    this.toggleChauffeurInfo(this.roleSelect.value);
    this.toggleVoyageCreation();
    this.updateVehiculeMessages();

    this.roleSelect?.addEventListener("change", this.handleRoleChange.bind(this));

    document.querySelectorAll(".annuler-covoiturage-form").forEach(form => {
      this.attachAnnulationHandler(form);
    });

    document.querySelectorAll(".supprimer-btn").forEach(button => {
      this.attachDeleteButton(button);
    });

    document.getElementById("form-ajout-vehicule")?.addEventListener("submit", this.handleAjoutVehicule.bind(this));

    this.formVoyage?.addEventListener("submit", this.handleVoyageSubmit.bind(this));

    this.initTripActions();
    this.initProfilForm();
    this.initPhotoUpload();
  }

  initProfilForm() {
    const form = document.getElementById("profil-form");
    if (!form) return;
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const msgEl = document.getElementById("profil-message");
      const body = {
        nom:      document.getElementById("profil-nom").value.trim(),
        email:    document.getElementById("profil-email").value.trim(),
        password: document.getElementById("profil-password").value,
      };
      const res  = await fetch("../actions/modifier_profil.php", {
        method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body)
      });
      const data = await res.json();
      if (data.success) {
        msgEl.style.color = "green";
        msgEl.textContent = data.message;
        document.getElementById("banner-nom").textContent = body.nom;
        document.getElementById("profil-password").value = "";
      } else {
        msgEl.style.color = "red";
        const fieldErr = data.errors ? Object.values(data.errors)[0] : null;
        msgEl.textContent = fieldErr || data.error || "Erreur inconnue.";
      }
    });
  }

  initPhotoUpload() {
    const input = document.getElementById("photo-input");
    if (!input) return;
    input.addEventListener("change", async () => {
      if (!input.files[0]) return;
      const formData = new FormData();
      formData.append("photo", input.files[0]);
      const res  = await fetch("../actions/upload_photo.php", { method: "POST", body: formData });
      const data = await res.json();
      if (data.success) {
        const wrap = document.getElementById("avatar-wrap");
        const fallback = document.getElementById("avatar-fallback");
        let img = document.getElementById("avatar-img");
        if (!img) {
          img = document.createElement("img");
          img.id = "avatar-img";
          img.className = "avatar-img";
          img.alt = "Photo de profil";
          if (fallback) fallback.replaceWith(img);
          else wrap.prepend(img);
        }
        img.src = "../images/profil/" + data.data.photo + "?t=" + Date.now();
      }
    });
  }

  initTripActions() {
    // Démarrer un trajet
    document.querySelectorAll(".btn-demarrer").forEach(btn => {
      btn.addEventListener("click", async () => {
        if (!confirm("Démarrer ce trajet ?")) return;
        const res = await fetch("../actions/demarrer_covoiturage.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ covoiturage_id: btn.dataset.id })
        });
        const data = await res.json();
        if (data.success) {
          const li = btn.closest("li");
          li.querySelector(".statut-badge").className = "statut-badge en-cours";
          li.querySelector(".statut-badge").textContent = "En cours";
          btn.className = "btn-terminer";
          btn.dataset.id = btn.dataset.id;
          btn.textContent = "🏁 Arrivée à destination";
          this.initTripActions();
        } else {
          alert(data.error || "Erreur.");
        }
      });
    });

    // Terminer un trajet
    document.querySelectorAll(".btn-terminer").forEach(btn => {
      btn.addEventListener("click", async () => {
        if (!confirm("Confirmer l'arrivée à destination ?")) return;
        const res = await fetch("../actions/terminer_covoiturage.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ covoiturage_id: btn.dataset.id })
        });
        const data = await res.json();
        if (data.success) {
          const li = btn.closest("li");
          li.querySelector(".statut-badge").className = "statut-badge passe";
          li.querySelector(".statut-badge").textContent = "Terminé";
          li.classList.add("passe");
          btn.remove();
          alert(data.message);
        } else {
          alert(data.error || "Erreur.");
        }
      });
    });

    // Validation passager : bouton "Tout s'est bien passé"
    document.querySelectorAll(".btn-ok-trajet").forEach(btn => {
      btn.addEventListener("click", () => {
        const panel = btn.closest(".validation-panel");
        panel.querySelector(".vp-avis-form").style.display = "block";
        panel.querySelector(".vp-prob-form").style.display = "none";
        panel.querySelector(".vp-btns").style.display = "none";
      });
    });

    // Validation passager : bouton "Signaler un problème"
    document.querySelectorAll(".btn-prob-trajet").forEach(btn => {
      btn.addEventListener("click", () => {
        const panel = btn.closest(".validation-panel");
        panel.querySelector(".vp-prob-form").style.display = "block";
        panel.querySelector(".vp-avis-form").style.display = "none";
        panel.querySelector(".vp-btns").style.display = "none";
      });
    });

    // Étoiles de notation
    document.querySelectorAll(".vp-stars").forEach(starsEl => {
      const stars = starsEl.querySelectorAll(".vp-star");
      const input = starsEl.closest("form").querySelector(".vp-note-val");
      stars.forEach(star => {
        star.addEventListener("click", () => {
          const val = parseInt(star.dataset.val);
          input.value = val;
          stars.forEach(s => s.classList.toggle("active", parseInt(s.dataset.val) <= val));
        });
      });
    });

    // Soumettre validation OK
    document.querySelectorAll(".vp-avis-form").forEach(form => {
      form.addEventListener("submit", async e => {
        e.preventDefault();
        const id   = form.dataset.id;
        const note = form.querySelector(".vp-note-val").value;
        const comm = form.querySelector(".vp-commentaire").value;
        const res  = await fetch("../actions/valider_trajet.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ covoiturage_id: id, note: note ? parseInt(note) : null, commentaire: comm })
        });
        const data = await res.json();
        if (data.success) {
          this.replaceValidationPanel(form, "validee", "Validé ✓");
        } else {
          alert(data.error || "Erreur.");
        }
      });
    });

    // Soumettre signalement
    document.querySelectorAll(".vp-prob-form").forEach(form => {
      form.addEventListener("submit", async e => {
        e.preventDefault();
        const id   = form.dataset.id;
        const comm = form.querySelector(".vp-prob-commentaire").value.trim();
        if (!comm) return;
        const res = await fetch("../actions/signaler_probleme.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ covoiturage_id: id, commentaire: comm })
        });
        const data = await res.json();
        if (data.success) {
          this.replaceValidationPanel(form, "litige", "Litige en cours");
          alert(data.message);
        } else {
          alert(data.error || "Erreur.");
        }
      });
    });
  }

  replaceValidationPanel(formEl, badgeClass, badgeLabel) {
    const li = formEl.closest("li");
    const panel = formEl.closest(".validation-panel");
    panel.remove();
    li.querySelector(".statut-badge").className = `statut-badge ${badgeClass}`;
    li.querySelector(".statut-badge").textContent = badgeLabel;
    li.classList.add("passe");
  }

  toggleChauffeurInfo(value = this.roleSelect.value) {
    if (this.chauffeurSection) {
      this.chauffeurSection.style.display = ["chauffeur", "chauffeur_passager"].includes(value) ? "block" : "none";
    }
  }

  toggleVoyageCreation() {
    if (!this.voyageSection) return;
    const chauffeurVisible = ["chauffeur", "chauffeur_passager"].includes(this.roleSelect.value);
    const hasVehicles = document.querySelectorAll(".vehicule").length > 0;
    this.voyageSection.style.display = (chauffeurVisible && hasVehicles) ? "block" : "none";
  }

  updateVehiculeMessages() {
    const hasVehicles = document.querySelectorAll(".vehicule").length > 0;
    document.getElementById("titre-vehicules")?.style.setProperty("display", hasVehicles ? "block" : "none");
    document.getElementById("message-ajout")?.style.setProperty("display", hasVehicles ? "block" : "none");
    document.getElementById("message-aucun-vehicule")?.style.setProperty("display", hasVehicles ? "none" : "block");
  }

  async handleRoleChange() {
    const value = this.roleSelect.value;
    if (this.roleMessage) {
      this.roleMessage.textContent = "Enregistrement...";
      this.roleMessage.style.color = "#999";
    }
    try {
      const response = await fetch("../actions/traitement_role.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "role=" + encodeURIComponent(value)
      });
      const data = await response.json();
      if (data.success) {
        this.roleMessage.textContent = "Rôle mis à jour !";
        this.roleMessage.style.color = "green";
        this.toggleChauffeurInfo(value);
        this.toggleVoyageCreation();
      } else {
        this.roleMessage.textContent = data.error || "Erreur lors de la mise à jour.";
        this.roleMessage.style.color = "red";
      }
    } catch (err) {
      this.roleMessage.textContent = "Erreur réseau.";
      this.roleMessage.style.color = "red";
    }
  }

  attachDeleteButton(button) {
    button.addEventListener("click", async () => {
      const id = button.dataset.id;
      const res = await fetch("../actions/supprimer_vehicule.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "vehicule_id=" + encodeURIComponent(id)
      });

      if (!res.ok) return alert("Erreur lors de la suppression.");

      const data = await res.json();
      button.closest(".vehicule")?.remove();

      data.deletedVoyages?.forEach(id => {
        document.querySelector(`#historique-conducteur li form[data-id="${id}"]`)?.closest("li")?.remove();
      });

      document.querySelector(`#vehicule-select option[value="${id}"]`)?.remove();
      requestAnimationFrame(() => {
        this.updateVehiculeMessages();
        this.toggleVoyageCreation();
      });
    });
  }

  attachAnnulationHandler(form) {
    form.addEventListener("submit", async e => {
      e.preventDefault();
      const id = form.dataset.id;
      const url = form.dataset.type === "conducteur" ? "../actions/annuler_covoiturage.php" : "../actions/annuler_participation.php";
      const fd = new FormData();
      fd.append("id", id);

      const res = await fetch(url, { method: "POST", body: fd });
      const result = await res.json();

      if (result.success) {
        const li = form.closest("li");
        const ul = li?.closest("ul");
        li?.remove();
        if (ul && ul.querySelectorAll("li").length === 0) {
          ul.style.display = "none";
          const emptyMsg = document.getElementById(
            ul.id === "historique-conducteur" ? "empty-conducteur" : "empty-passager"
          );
          if (emptyMsg) emptyMsg.style.display = "";
        }
        if (result.data?.new_credits !== undefined) {
          const badge = document.querySelector(".credits-badge-amount");
          if (badge) badge.textContent = result.data.new_credits;
        }
      } else {
        alert("Erreur : " + (result.error || "Impossible d'annuler le voyage."));
      }
    });
  }

  async handleAjoutVehicule(e) {
    e.preventDefault();
    const form = e.target;
    const messageEl = document.getElementById("message-ajout-vehicule");
    const data = new FormData(form);
    const res = await fetch("../actions/ajouter_vehicule.php", { method: "POST", body: data });
    const result = await res.json();

    if (result.success) {
      const { id, marque, modele, couleur, plaque } = result.data;

      const container = document.createElement("div");
      container.className = "vehicule";
      container.dataset.id = id;
      container.innerHTML = `
        <div class="vehicule-info">
          <strong>${esc(marque)} ${esc(modele)}</strong>
          <span>${esc(couleur)} · ${esc(plaque)}</span>
        </div>
        <button class="btn-delete supprimer-btn" data-id="${esc(id)}">Supprimer</button>
      `;
      document.getElementById("vehicules-list")?.appendChild(container);
      this.attachDeleteButton(container.querySelector(".supprimer-btn"));

      form.reset();

      const vehiculeSelect = document.getElementById("vehicule-select");
      if (vehiculeSelect) {
        const option = document.createElement("option");
        option.value = id;
        option.textContent = `${marque} ${modele} · ${plaque}`;
        vehiculeSelect.appendChild(option);
      }

      requestAnimationFrame(() => {
        this.updateVehiculeMessages();
        this.toggleVoyageCreation();
      });

      if (messageEl) {
        messageEl.textContent = "Véhicule ajouté avec succès !";
        messageEl.style.color = "green";
        messageEl.style.display = "block";
        setTimeout(() => { messageEl.style.display = "none"; }, 3000);
      }
    } else {
      if (messageEl) {
        messageEl.textContent = "❌ " + (result.error || "Erreur lors de l’ajout.");
        messageEl.style.color = "red";
        messageEl.style.display = "block";
      }
    }
  }

  async handleVoyageSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this.formVoyage);
    const res = await fetch("../actions/traitement_voyage.php", { method: "POST", body: formData });
    const result = await res.json();

    if (this.messageVoyage) this.messageVoyage.style.display = "block";

    if (result.success) {
      this.messageVoyage.textContent = "Covoiturage créé avec succès !";
      this.messageVoyage.style.color = "green";
      this.ajouterVoyageHistoriqueConducteur(result.data);
      this.formVoyage.reset();
      setTimeout(() => {
        this.messageVoyage.style.display = "none";
      }, 3000);
    } else {
      this.messageVoyage.textContent = "❌ " + (result.error || "Erreur lors de la création.");
      this.messageVoyage.style.color = "red";
    }
  }

  ajouterVoyageHistoriqueConducteur(voyage) {
    const ul = document.getElementById("historique-conducteur");
    if (!ul) return;

    const emptyMsg = document.getElementById("empty-conducteur");
    if (emptyMsg) emptyMsg.style.display = "none";
    ul.style.display = "";

    const li = document.createElement("li");
    li.className = "trip-item disponible";
    li.innerHTML = `
      <a href="detail.php?id=${esc(voyage.id)}" class="trip-info-link">
        <div class="trip-info">
          <div class="trip-route">${esc(voyage.lieu_depart)} → ${esc(voyage.lieu_arrivee)}</div>
          <div class="trip-meta">📅 ${new Date(voyage.date_depart).toLocaleDateString('fr-FR')}</div>
          <span class="statut-badge disponible">Disponible</span>
        </div>
      </a>
      <form class="annuler-covoiturage-form" data-id="${esc(voyage.id)}" data-type="conducteur">
        <input type="hidden" name="id" value="${esc(voyage.id)}">
        <button type="submit" class="btn-annuler">Supprimer</button>
      </form>
    `;
    ul.prepend(li);
    this.attachAnnulationHandler(li.querySelector(".annuler-covoiturage-form"));
  }
}

// Initialisation
window.addEventListener("DOMContentLoaded", () => {
  new EspaceUtilisateur();

  const flash = sessionStorage.getItem("flash_success");
  if (flash) {
    sessionStorage.removeItem("flash_success");
    const toast = document.createElement("div");
    toast.className = "flash-toast flash-toast--success";
    toast.textContent = flash;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add("flash-toast--visible"), 50);
    setTimeout(() => {
      toast.classList.remove("flash-toast--visible");
      setTimeout(() => toast.remove(), 400);
    }, 4000);
  }
});