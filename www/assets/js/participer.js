document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector("#participer-btn");
  if (!btn) return;

  const messageEl = document.querySelector("#message-participation");

  btn.addEventListener("click", async () => {
    const id = btn.dataset.id;

    const res = await fetch("../actions/participer.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: new URLSearchParams({ covoiturage_id: id })
    });

    const data = await res.json();

    if (data.success) {
      messageEl.style.color = "green";
      messageEl.textContent = data.message;
      setTimeout(() => {
        location.href = "user.php#historique-covoiturages";
      }, 1500);
    } else {
      messageEl.style.color = "red";
      messageEl.textContent = data.error;
    }
  });
});