<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../config.php';

// Connexion MongoDB
$mongo = new MongoDB\Driver\Manager("mongodb://mongodb:27017");

$query = new MongoDB\Driver\Query([]);
$cursor = $mongo->executeQuery("ecoride_stats.stats", $query);

$stats = [];
$totalCredits = 0;

foreach ($cursor as $doc) {
    $stats[] = [
        'date' => $doc->date,
        'nb_covoiturages' => $doc->nb_covoiturages,
        'credits_gagnes' => $doc->credits_gagnes
    ];
    $totalCredits += $doc->credits_gagnes;
}

// Récupération des utilisateurs avec leur rôle (via LEFT JOIN car tous n’ont pas de rôle)
$stmt = $pdo->query("
    SELECT u.id, u.nom, u.email, COALESCE(r.role, 'non défini') AS role, 
           COALESCE(u.actif, 1) AS actif
    FROM utilisateurs u
    LEFT JOIN roles_utilisateurs r ON r.utilisateur_id = u.id
");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin - EcoRide</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include '../partials/menu.php'; ?>
  <main class="admin-main">
    <h1>Tableau de bord Admin</h1>

    <section>
      <h2>Statistiques de la plateforme</h2>
      <p>Total de crédits gagnés : <strong><?= $totalCredits ?></strong></p>
      <canvas id="chartCovoits" width="400" height="200"></canvas>
      <canvas id="chartCredits" width="400" height="200"></canvas>
    </section>

    <div class="admin-users-table">
      <h2>Gestion des utilisateurs</h2>
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($utilisateurs as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['nom']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['role']) ?></td>
              <td><?= $u['actif'] ? 'Actif' : 'Suspendu' ?></td>
              <td>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <button class="toggle-btn" data-id="<?= $u['id'] ?>" data-status="<?= $u['actif'] ? 'actif' : 'suspendu' ?>">
                    <?= $u['actif'] ? 'Suspendre' : 'Réactiver' ?>
                  </button>
                <?php else: ?>
                  <em>Vous</em>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const stats = <?= json_encode($stats) ?>;
    const labels = stats.map(s => s.date);
    const covoits = stats.map(s => s.nb_covoiturages);
    const credits = stats.map(s => s.credits_gagnes);

    new Chart(document.getElementById('chartCovoits'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Nombre de covoiturages',
          data: covoits
        }]
      }
    });

    new Chart(document.getElementById('chartCredits'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Crédits gagnés',
          data: credits,
          backgroundColor: 'green'
        }]
      }
    });
  </script>
      <?php include '../partials/footer.php'; ?>
      <?php require_once __DIR__ . '/../includes/layout.php'; ?>
    <div id="injection-modal"></div>
    <script src="../assets/js/modal-connexion.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        new ModalConnexion(); // instancie et lance automatiquement
      });
    </script>
    <script src="../assets/js/carousel.js"></script>
    <script src="../assets/js/menu-toggle.js" defer></script>
    <script>
      document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', async () => {
          const userId = button.dataset.id;

          const res = await fetch('../actions/toggle_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
          });

          const data = await res.json();

          if (data.success) {
            button.textContent = data.new_status === 'Actif' ? 'Suspendre' : 'Réactiver';
            const row = button.closest('tr');
            row.querySelector('td:nth-child(4)').textContent = data.new_status;
          } else {
            alert(data.error || 'Erreur inconnue');
          }
        });
      });
    </script>
</body>
</html>