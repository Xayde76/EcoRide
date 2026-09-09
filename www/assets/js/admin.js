document.addEventListener("DOMContentLoaded", () => {
  new ModalConnexion();
});

// `ecorideStats` est injecté par admin.php (données MongoDB sérialisées côté serveur)
const stats = window.ecorideStats || [];

if (stats.length === 0) {
  document.querySelectorAll('.admin-chart-card').forEach(card => {
    card.innerHTML = '<p class="chart-empty">Aucune donnée pour le moment.<br>Les graphiques se rempliront dès que des trajets seront terminés et validés.</p>';
  });
} else {
  const labels  = stats.map(s => s.date);
  const covoits = stats.map(s => s.nb_covoiturages);
  const credits = stats.map(s => s.credits_gagnes);

  const chartOptions = (title) => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      title: { display: true, text: title, font: { size: 13, weight: '600' }, color: '#333', padding: { bottom: 12 } },
      legend: { display: false }
    },
    scales: {
      x: { ticks: { font: { size: 10 }, maxRotation: 45 } },
      y: { ticks: { font: { size: 10 } }, beginAtZero: true }
    }
  });

  new Chart(document.getElementById('chartCovoits'), {
    type: 'line',
    data: { labels, datasets: [{ data: covoits, borderColor: '#4caf50', backgroundColor: 'rgba(76,175,80,0.1)', fill: true, tension: 0.3, pointRadius: 4 }] },
    options: chartOptions('Nombre de covoiturages par jour')
  });

  new Chart(document.getElementById('chartCredits'), {
    type: 'bar',
    data: { labels, datasets: [{ data: credits, backgroundColor: 'rgba(76,175,80,0.7)', borderRadius: 3 }] },
    options: chartOptions('Crédits gagnés par jour')
  });
}

// Création employé
document.getElementById('form-employe').addEventListener('submit', async (e) => {
  e.preventDefault();
  const msgEl = document.getElementById('emp-message');
  const body = {
    nom:      document.getElementById('emp-nom').value.trim(),
    email:    document.getElementById('emp-email').value.trim(),
    password: document.getElementById('emp-password').value,
  };

  const res  = await fetch('../actions/creer_employe.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  const data = await res.json();

  if (data.success) {
    msgEl.style.color = 'green';
    msgEl.textContent = data.message;
    e.target.reset();

    // Ajoute la ligne dans le tableau
    const tbody = document.querySelector('.admin-users-table:last-of-type tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${data.data.nom}</td>
      <td>${data.data.email}</td>
      <td>employe</td>
      <td>Actif</td>
      <td>
        <button class="toggle-btn" data-id="${data.data.id}" data-status="actif">Suspendre</button>
      </td>`;
    tbody.appendChild(tr);
    attachToggle(tr.querySelector('.toggle-btn'));
  } else {
    const fieldErr = data.errors ? Object.values(data.errors)[0] : null;
    msgEl.style.color = 'red';
    msgEl.textContent = fieldErr || data.error || 'Erreur inconnue.';
  }
});

function attachToggle(button) {
  button.addEventListener('click', async () => {
    const res = await fetch('../actions/toggle_user.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: button.dataset.id })
    });
    const data = await res.json();
    if (data.success) {
      button.textContent = data.data.new_status === 'Actif' ? 'Suspendre' : 'Réactiver';
      button.closest('tr').querySelector('td:nth-child(4)').textContent = data.data.new_status;
    } else {
      alert(data.error || 'Erreur inconnue');
    }
  });
}

document.querySelectorAll('.toggle-btn').forEach(button => attachToggle(button));
