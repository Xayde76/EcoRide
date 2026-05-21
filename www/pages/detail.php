<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_GET['id']) || !ValidationService::validateInteger($_GET['id'], 1)) {
    header('Location: ' . BASE_URL . '/pages/covoiturage.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT c.*, u.nom AS conducteur_nom, v.modele, v.marque, v.couleur, v.preferences,
           ROUND(AVG(a.note), 1) AS note_conducteur,
           COUNT(a.note)         AS nb_avis_conducteur
    FROM covoiturage c
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    LEFT JOIN vehicules v ON c.vehicule_id = v.id
    LEFT JOIN avis a ON a.covoiturage_id IN (
        SELECT covoiturage_id FROM covoiturage WHERE utilisateur_id = u.id
    ) AND a.statut = 'publié'
    WHERE c.covoiturage_id = ?
    GROUP BY c.covoiturage_id, u.nom, v.modele, v.marque, v.couleur, v.preferences
");
$stmt->execute([$id]);
$trajet = $stmt->fetch();

if (!$trajet) {
    header('Location: ' . BASE_URL . '/pages/covoiturage.php');
    exit;

}

$eco = $trajet['type_vehicule'] === 'electrique' ? 'Oui' : 'Non';

$roleUtilisateur = '';
if (isset($_SESSION['user_id'])) {
    $stmtRole = $pdo->prepare("SELECT role FROM roles_utilisateurs WHERE utilisateur_id = ?");
    $stmtRole->execute([$_SESSION['user_id']]);
    $roleUtilisateur = $stmtRole->fetchColumn() ?: '';
}

$stmtAvis = $pdo->prepare("SELECT auteur, commentaire, note FROM avis WHERE statut = 'publié' AND covoiturage_id = ?");
$stmtAvis->execute([$id]);
$avis = $stmtAvis->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($trajet['lieu_depart']) ?> → <?= htmlspecialchars($trajet['lieu_arrivee']) ?> | EcoRide</title>
  <meta name="description" content="Covoiturage de <?= htmlspecialchars($trajet['lieu_depart']) ?> à <?= htmlspecialchars($trajet['lieu_arrivee']) ?> le <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?> pour <?= (int)$trajet['prix_personne'] ?> crédits. Réservez votre place sur EcoRide.">
  <meta name="robots" content="index, follow">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Covoiturage <?= htmlspecialchars($trajet['lieu_depart']) ?> → <?= htmlspecialchars($trajet['lieu_arrivee']) ?> | EcoRide">
  <meta property="og:description" content="Trajet le <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?> pour <?= (int)$trajet['prix_personne'] ?> crédits. Conducteur : <?= htmlspecialchars($trajet['conducteur_nom']) ?>.">
  <meta property="og:image" content="https://ecoride-production-48f6.up.railway.app/images/logo-ecoride.png">

  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/../partials/menu.php'; ?>

  <main class="wrapper user-page">

    <!-- Bannière -->
    <div class="user-banner">
      <div class="user-banner-identity">
        <div class="user-avatar">🚗</div>
        <div>
          <h1><?= htmlspecialchars($trajet['lieu_depart']) ?> → <?= htmlspecialchars($trajet['lieu_arrivee']) ?></h1>
          <p>le <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?> · <?= (int)$trajet['prix_personne'] ?> crédits</p>
        </div>
      </div>
      <button onclick="history.back()" class="detail-retour-btn">← Retour</button>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert-error">
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>

    <!-- Layout 2 colonnes -->
    <div class="detail-layout">

      <!-- Colonne principale : infos trajet -->
      <div class="detail-main">

        <!-- Horaires -->
        <div class="user-card detail-trip-card">
          <div class="detail-times">
            <div class="detail-time-stop">
              <span class="time-value"><?= date('H\hi', strtotime($trajet['heure_depart'])) ?></span>
              <span class="time-city"><?= htmlspecialchars($trajet['lieu_depart']) ?></span>
            </div>
            <div class="detail-time-line">
              <div class="detail-time-bar"></div>
              <span class="detail-time-arrow">→</span>
            </div>
            <div class="detail-time-stop detail-time-stop--right">
              <?php if (!empty($trajet['heure_arrivee'])): ?>
                <span class="time-value"><?= date('H\hi', strtotime($trajet['heure_arrivee'])) ?></span>
              <?php else: ?>
                <span class="time-value time-value--empty">—</span>
              <?php endif; ?>
              <span class="time-city"><?= htmlspecialchars($trajet['lieu_arrivee']) ?></span>
            </div>
          </div>

          <!-- Badges -->
          <div class="detail-badges">
            <span class="detail-badge">🪑 <?= (int)$trajet['nb_place'] ?> place<?= $trajet['nb_place'] > 1 ? 's' : '' ?> restante<?= $trajet['nb_place'] > 1 ? 's' : '' ?></span>
            <?php if ($eco === 'Oui'): ?>
              <span class="detail-badge detail-badge--eco">🌿 Voyage écologique</span>
            <?php else: ?>
              <span class="detail-badge">⛽ Non écologique</span>
            <?php endif; ?>
          </div>

          <!-- Véhicule -->
          <div class="detail-vehicule">
            <h3>Véhicule</h3>
            <p><?= htmlspecialchars($trajet['marque']) ?> <?= htmlspecialchars($trajet['modele']) ?> · <?= ucfirst($trajet['type_vehicule']) ?></p>
            <?php if (!empty($trajet['preferences'])): ?>
              <p class="detail-prefs">💬 <?= htmlspecialchars($trajet['preferences']) ?></p>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /detail-main -->

      <!-- Sidebar : conducteur + participation -->
      <div class="detail-sidebar">
        <div class="user-card detail-conducteur-card">
          <div class="detail-conducteur-initiale">
            <?= htmlspecialchars(strtoupper(mb_substr($trajet['conducteur_nom'], 0, 1))) ?>
          </div>
          <h3><?= htmlspecialchars($trajet['conducteur_nom']) ?></h3>
          <p class="detail-conducteur-label">Conducteur</p>
          <?php if ($trajet['nb_avis_conducteur'] > 0): ?>
            <div class="detail-conducteur-note">
              <span class="star-full">★</span>
              <strong><?= number_format($trajet['note_conducteur'], 1) ?></strong>
              <span class="detail-conducteur-nb-avis">(<?= $trajet['nb_avis_conducteur'] ?> avis)</span>
            </div>
          <?php else: ?>
            <p class="detail-conducteur-nb-avis">Aucun avis pour le moment</p>
          <?php endif; ?>
          <?php if ($roleUtilisateur === 'chauffeur'): ?>
            <p class="detail-role-notice">En tant que chauffeur, vous ne pouvez pas participer à un covoiturage. Changez votre rôle en "Passager" ou "Chauffeur/Passager" dans votre espace.</p>
          <?php else: ?>
            <button class="btn detail-participer-btn" id="participer-btn" data-id="<?= $trajet['covoiturage_id'] ?>" data-prix="<?= (int)$trajet['prix_personne'] ?>">Participer</button>
            <p id="message-participation"></p>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /detail-layout -->

    <!-- Avis -->
    <section class="avis-section">
      <?php if (empty($avis)): ?>
        <div class="avis-empty-block">
          <div class="avis-empty-icon">💬</div>
          <p class="avis-empty-title">Aucun avis pour le moment</p>
          <p class="avis-empty-sub">Les passagers pourront laisser un avis après le trajet.</p>
        </div>
      <?php else:
        $noteTotal = array_sum(array_column($avis, 'note'));
        $noteMoy   = $noteTotal / count($avis);
      ?>
        <div class="avis-header-section">
          <div>
            <h2>Avis des passagers</h2>
            <p class="avis-count"><?= count($avis) ?> avis</p>
          </div>
          <div class="avis-moyenne">
            <span class="avis-moyenne-note"><?= number_format($noteMoy, 1) ?></span>
            <div>
              <div class="avis-moyenne-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= round($noteMoy) ? 'star-full' : 'star-empty' ?>">★</span>
                <?php endfor; ?>
              </div>
              <span class="avis-moyenne-label">sur 5</span>
            </div>
          </div>
        </div>
        <div class="avis-list">
          <?php foreach ($avis as $a): ?>
            <div class="avis-card">
              <div class="avis-card-header">
                <div class="avis-auteur-initiale"><?= htmlspecialchars(strtoupper(mb_substr($a['auteur'], 0, 1))) ?></div>
                <div class="avis-auteur-info">
                  <strong><?= htmlspecialchars($a['auteur']) ?></strong>
                  <span class="avis-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="<?= $i <= (int)$a['note'] ? 'star-full' : 'star-empty' ?>">★</span>
                    <?php endfor; ?>
                  </span>
                </div>
              </div>
              <?php if (!empty($a['commentaire'])): ?>
                <p class="avis-commentaire"><?= htmlspecialchars($a['commentaire']) ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>
  <?php include __DIR__ . '/../includes/layout.php'; ?>
  <div id="injection-modal"></div>
  <script src="../assets/js/modal-connexion.js"></script>
  <script src="../assets/js/participer.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      new ModalConnexion();
    });
  </script>
  <script src="../assets/js/menu-toggle.js" defer></script>
</body>
</html>
