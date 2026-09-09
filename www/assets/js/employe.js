document.addEventListener("DOMContentLoaded", () => {
  new ModalConnexion();
});

// ---- Modération des avis ----
document.querySelectorAll(".btn-publier, .btn-rejeter").forEach(btn => {
  btn.addEventListener("click", async () => {
    const avisId = btn.dataset.id;
    const action = btn.classList.contains("btn-publier") ? "publier" : "rejeter";
    const msgEl  = document.getElementById(`msg-avis-${avisId}`);

    const res  = await fetch("../actions/moderer_avis.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ avis_id: parseInt(avisId), action })
    });
    const data = await res.json();

    if (data.success) {
      const card = document.getElementById(`avis-${avisId}`);
      card.style.opacity = "0.5";
      card.querySelectorAll("button").forEach(b => b.disabled = true);
      msgEl.style.color = action === "publier" ? "green" : "#c62828";
      msgEl.textContent = data.message;
      setTimeout(() => {
        card.remove();
        const count = document.getElementById("count-avis");
        count.textContent = parseInt(count.textContent) - 1;
      }, 1200);
    } else {
      msgEl.style.color = "red";
      msgEl.textContent = data.error || "Erreur.";
    }
  });
});

// ---- Résolution des litiges ----
document.querySelectorAll(".btn-resoudre-conducteur, .btn-resoudre-passager").forEach(btn => {
  btn.addEventListener("click", async () => {
    const covId      = btn.dataset.cov;
    const passagerId = btn.dataset.passager;
    const resolution = btn.classList.contains("btn-resoudre-conducteur") ? "conducteur" : "passager";
    const msgEl      = document.getElementById(`msg-litige-${covId}-${passagerId}`);

    const label = resolution === "conducteur"
      ? "Confirmer : les crédits seront versés au conducteur ?"
      : "Confirmer : le passager sera remboursé ?";
    if (!confirm(label)) return;

    const res  = await fetch("../actions/resoudre_litige.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ covoiturage_id: parseInt(covId), passager_id: parseInt(passagerId), resolution })
    });
    const data = await res.json();

    if (data.success) {
      const card = document.getElementById(`litige-${covId}-${passagerId}`);
      card.style.opacity = "0.5";
      card.querySelectorAll("button").forEach(b => b.disabled = true);
      msgEl.style.color = "green";
      msgEl.textContent = data.message;
      setTimeout(() => {
        card.remove();
        const count = document.getElementById("count-litiges");
        count.textContent = parseInt(count.textContent) - 1;
      }, 1200);
    } else {
      msgEl.style.color = "red";
      msgEl.textContent = data.error || "Erreur.";
    }
  });
});
