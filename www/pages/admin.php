<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$stats = [];

// Tentatives de connexion MongoDB (max 2 essais)
$mongoAttempts = 2;
while ($mongoAttempts-- > 0) {
    try {
        $mongoUri = getenv('MONGO_URI') ?: ('mongodb://' . (getenv('MONGO_HOST') ?: 'mongodb') . ':27017');
        $mongo    = new MongoDB\Driver\Manager(
            $mongoUri,
            ['serverSelectionTimeoutMS' => 8000, 'connectTimeoutMS' => 8000]
        );
        $filter  = ['date' => ['$gte' => date('Y-m-d', strtotime('-29 days'))]];
        $options = ['sort' => ['date' => 1]];
        $cursor  = $mongo->executeQuery('ecoride_stats.stats', new MongoDB\Driver\Query($filter, $options));
        foreach ($cursor as $doc) {
            $stats[] = [
                'date'            => $doc->date            ?? '',
                'nb_covoiturages' => (int)($doc->nb_covoiturages ?? 0),
                'credits_gagnes'  => (int)($doc->credits_gagnes  ?? 0),
            ];
        }
        break; // succès, on sort de la boucle
    } catch (\Exception $e) {
        LoggerService::warning('MongoDB unavailable', ['error' => $e->getMessage(), 'attempts_left' => $mongoAttempts]);
        if ($mongoAttempts > 0) usleep(300000); // attendre 300ms avant de réessayer
    }
}

$totalCreditsAll = array_sum(array_column($stats, 'credits_gagnes'));

// KPI globaux depuis MySQL
$totalCovoiturages = (int)$pdo->query("SELECT COUNT(*) FROM covoiturage WHERE statut = 'termine'")->fetchColumn();
$totalUsers        = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE actif = 1")->fetchColumn();

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin - EcoRide</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../partials/menu.php'; ?>
  <main class="admin-main user-page">
    <div class="user-banner">
      <div class="user-banner-identity">
        <div class="user-avatar">⚙️</div>
        <div>
          <h1>Tableau de bord Admin</h1>
          <p>Gestion de la plateforme EcoRide</p>
        </div>
      </div>
    </div>

    <section class="admin-stats-section">
      <h2>Statistiques de la plateforme</h2>
      <div class="admin-stats-kpi">
        <div class="admin-kpi-card">
          <span class="admin-kpi-value"><?= $totalCreditsAll ?></span>
          <span class="admin-kpi-label">Crédits gagnés (30j)</span>
        </div>
        <div class="admin-kpi-card">
          <span class="admin-kpi-value"><?= $totalCovoiturages ?></span>
          <span class="admin-kpi-label">Covoiturages terminés</span>
        </div>
        <div class="admin-kpi-card">
          <span class="admin-kpi-value"><?= $totalUsers ?></span>
          <span class="admin-kpi-label">Utilisateurs actifs</span>
        </div>
      </div>
      <div class="admin-charts-grid">
        <div class="admin-chart-card">
          <div class="chart-inner">
            <canvas id="chartCovoits"></canvas>
          </div>
        </div>
        <div class="admin-chart-card">
          <div class="chart-inner">
            <canvas id="chartCredits"></canvas>
          </div>
        </div>
      </div>
    </section>

    <!-- Créer un compte employé -->
    <div class="admin-users-table">
      <h2>Créer un compte employé</h2>
      <form id="form-employe" class="admin-employe-form">
        <div class="admin-employe-fields">
          <input type="text"     id="emp-nom"      placeholder="Nom complet"      required />
          <input type="email"    id="emp-email"     placeholder="Adresse email"    required />
          <input type="password" id="emp-password"  placeholder="Mot de passe"     required />
        </div>
        <button type="submit" class="admin-employe-btn">Créer l'employé</button>
        <p id="emp-message"></p>
      </form>
    </div>

    <!-- Tableau utilisateurs -->
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

  <?php include __DIR__ . '/../partials/footer.php'; ?>
  <?php include __DIR__ . '/../includes/layout.php'; ?>
  <div id="injection-modal"></div>
  <script src="../assets/js/modal-connexion.js"></script>
  <script>window.ecorideStats = <?= json_encode($stats) ?>;</script>
  <script src="../assets/js/admin.js" defer></script>
  <script src="../assets/js/menu-toggle.js" defer></script>
</body>
</html>
