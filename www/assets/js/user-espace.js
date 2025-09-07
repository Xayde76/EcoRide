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

      document.querySelector(`select[name="vehicule_id"] option[value="${id}"]`)?.remove();
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
        li?.classList.add("annule");
        form.remove();
        const span = li?.querySelector("span:last-of-type");
        if (span) {
          span.textContent = "Statut : annulé";
        }
      } else {
        alert("Erreur : " + (result.error || "Impossible d'annuler le voyage."));
      }
    });
  }

  async handleAjoutVehicule(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const res = await fetch("../actions/ajouter_vehicule.php", { method: "POST", body: data });
    const result = await res.json();

    if (result.success) {
      const { id, marque, modele, couleur, plaque } = result.vehicule;
      const container = document.createElement("div");
      container.className = "vehicule";
      container.dataset.id = id;
      container.innerHTML = `<p><strong>${marque} ${modele}</strong> (${couleur}) - ${plaque}</p><button class="btn-delete supprimer-btn" data-id="${id}">Supprimer</button>`;
      document.getElementById("vehicules-list")?.appendChild(container);

      const option = document.createElement("option");
      option.value = id;
      option.textContent = `${marque} ${modele} - ${plaque}`;
      document.querySelector("select[name='vehicule_id']")?.appendChild(option);

      form.reset();
      this.attachDeleteButton(container.querySelector(".supprimer-btn"));

      requestAnimationFrame(() => {
        this.updateVehiculeMessages();
        this.toggleVoyageCreation();
      });
    } else {
      alert(result.error || "Erreur lors de l’ajout.");
    }
  }

  async handleVoyageSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this.formVoyage);
    const res = await fetch("../actions/traitement_voyage.php", { method: "POST", body: formData });
    const result = await res.json();

    if (this.messageVoyage) this.messageVoyage.style.display = "block";

    if (result.success) {
      this.messageVoyage.textContent = "🚗 Covoiturage créé avec succès !";
      this.messageVoyage.style.color = "green";
      this.ajouterVoyageHistoriqueConducteur(result.voyage);
      this.formVoyage.reset();
      setTimeout(() => location.reload(), 0);
    } else {
      this.messageVoyage.textContent = "❌ " + (result.error || "Erreur lors de la création.");
      this.messageVoyage.style.color = "red";
    }
  }

  ajouterVoyageHistoriqueConducteur(voyage) {
    const ul = document.getElementById("historique-conducteur");
    if (!ul) return;

    const li = document.createElement("li");
    li.innerHTML = `
      <div>
        <strong>${voyage.lieu_depart} → ${voyage.lieu_arrivee}</strong><br>
        <span>📅 le ${new Date(voyage.date_depart).toLocaleDateString('fr-FR')}</span><br>
        <span>🛈 Statut : ${voyage.statut}</span>
      </div>
      <form class="annuler-covoiturage-form" data-id="${voyage.id}" data-type="conducteur">
        <input type="hidden" name="id" value="${voyage.id}">
        <button type="submit" class="btn-delete">Annuler</button>
      </form>
    `;
    ul.prepend(li);
    this.attachAnnulationHandler(li.querySelector(".annuler-covoiturage-form"));
  }
}

// Initialisation
window.addEventListener("DOMContentLoaded", () => new EspaceUtilisateur());