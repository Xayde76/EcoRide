<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

$covoiturageManager = new CovoiturageManager($pdo, $_SESSION['user_id'] ?? 0);

$filtres = [
    'depart'      => $_GET['depart'] ?? '',
    'destination' => $_GET['destination'] ?? '',
    'date'        => $_GET['date'] ?? '',
    'ecologique'  => isset($_GET['ecologique']),
    'prix_max'    => $_GET['prix_max'] ?? '',
    'duree_max'   => $_GET['duree_max'] ?? '',
    'note_min'    => $_GET['note_min'] ?? ''
];

$covoiturages = [];
$suggestion = null;

$searchSubmitted = array_key_exists('depart', $_GET);
if ($searchSubmitted) {
    $covoiturages = $covoiturageManager->rechercherCovoiturages($filtres);
    if (empty($covoiturages) && !empty($filtres['depart']) && !empty($filtres['destination'])) {
        $suggestion = $covoiturageManager->suggereProchaineDate($filtres['depart'], $filtres['destination']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rechercher un covoiturage | EcoRide</title>
  <meta name="description" content="Recherchez un covoiturage écologique en France. Filtrez par ville, date, prix et bien plus. Voyagez malin avec EcoRide.">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://www.ecoride.fr/pages/covoiturage.php">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.ecoride.fr/pages/covoiturage.php">
  <meta property="og:title" content="Rechercher un covoiturage | EcoRide">
  <meta property="og:description" content="Trouvez votre trajet parmi des dizaines de covoiturages en France. Simple, rapide, écologique.">
  <meta property="og:image" content="https://www.ecoride.fr/images/logo-ecoride.png">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="Rechercher un covoiturage | EcoRide">
  <meta name="twitter:description" content="Trouvez votre trajet parmi des dizaines de covoiturages en France. Simple, rapide, écologique.">

  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <div class="wrapper">
    <?php include __DIR__ . '/../partials/menu.php'; ?>

    <main class="user-page">
      <div class="user-banner">
        <div class="user-banner-identity">
          <div class="user-avatar">🚗</div>
          <div>
            <h1>Covoiturage</h1>
            <p>Trouvez ou proposez un trajet</p>
          </div>
        </div>
      </div>

      <form class="covoiturage-search" method="GET" action="">
        <input type="text" name="depart" placeholder="Départ" value="<?= htmlspecialchars($filtres['depart']) ?>" />
        <input type="text" name="destination" placeholder="Arrivée" value="<?= htmlspecialchars($filtres['destination']) ?>" />
        <input type="date" name="date" value="<?= htmlspecialchars($filtres['date']) ?>" />

        <details class="filtre-depliant">
          <summary>Filtres avancés</summary>
          <div class="filtre-grid">
            <label class="filtre-checkbox">
              <input type="checkbox" name="ecologique" <?= $filtres['ecologique'] ? 'checked' : '' ?> />
              Voyage écologique uniquement
            </label>
            <label>
              Prix max (€)
              <input type="number" name="prix_max" placeholder="Ex : 20" value="<?= htmlspecialchars($filtres['prix_max']) ?>" />
            </label>
            <label>
              Durée max (minutes)
              <input type="number" name="duree_max" placeholder="Ex : 120" value="<?= htmlspecialchars($filtres['duree_max']) ?>" />
            </label>
            <label>
              Note conducteur min
              <input type="number" step="0.1" min="0" max="5" name="note_min" placeholder="Ex : 4" value="<?= htmlspecialchars($filtres['note_min']) ?>" />
            </label>
          </div>
        </details>

        <button type="submit" class="recherche-btn">Recherche</button>
      </form>

      <?php if ($suggestion): ?>
        <p style="text-align:center; margin-top:2rem;">
          Aucun covoiturage pour cette date. Prochain trajet disponible : <strong><?= date('d/m/Y', strtotime($suggestion)) ?></strong>
        </p>
      <?php endif; ?>

      <?php if ($searchSubmitted): ?>
        <section class="trajets-list">
          <?php if (empty($covoiturages)): ?>
            <p style="text-align:center; margin-top:2rem;">Aucun covoiturage disponible pour le moment.</p>
          <?php else: ?>
            <?php foreach ($covoiturages as $c): ?>
              <div class="trajet-card">

                <!-- Route : villes + horaires -->
                <div class="trajet-route">
                  <div class="trajet-stops">
                    <div class="trajet-stop">
                      <span class="trajet-stop-time"><?= substr($c['heure_depart'], 0, 5) ?></span>
                      <span class="trajet-stop-city"><?= htmlspecialchars($c['lieu_depart']) ?></span>
                    </div>
                    <div class="trajet-stop-line">
                      <span class="trajet-stop-dot"></span>
                      <span class="trajet-stop-bar"></span>
                      <span class="trajet-stop-dot"></span>
                    </div>
                    <div class="trajet-stop">
                      <span class="trajet-stop-time"><?= $c['heure_arrivee'] ? substr($c['heure_arrivee'], 0, 5) : '—' ?></span>
                      <span class="trajet-stop-city"><?= htmlspecialchars($c['lieu_arrivee']) ?></span>
                    </div>
                  </div>
                  <div class="trajet-date">📅 <?= date('d/m/Y', strtotime($c['date_depart'])) ?></div>
                </div>

                <!-- Conducteur + badges -->
                <div class="trajet-meta-col">
                  <div class="trajet-conducteur-mini">
                    <div class="trajet-avatar-wrap">
                      <img src="../images/profil/default.png" alt="" class="trajet-avatar"
                           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                      <div class="trajet-avatar-fallback"><?= htmlspecialchars(strtoupper(mb_substr($c['conducteur_nom'], 0, 1))) ?></div>
                    </div>
                    <span class="trajet-conducteur-nom"><?= htmlspecialchars($c['conducteur_nom']) ?></span>
                    <?php if (!empty($c['nb_avis_conducteur'])): ?>
                      <span class="trajet-note">⭐ <?= number_format($c['note_conducteur'], 1) ?> <small>(<?= $c['nb_avis_conducteur'] ?>)</small></span>
                    <?php else: ?>
                      <span class="trajet-note trajet-note--empty">Pas encore d'avis</span>
                    <?php endif; ?>
                  </div>
                  <div class="trajet-badges">
                    <span class="trajet-badge">🪑 <?= (int)$c['nb_place'] ?> place<?= $c['nb_place'] > 1 ? 's' : '' ?></span>
                    <?php if ($c['type_vehicule'] === 'electrique'): ?>
                      <span class="trajet-badge trajet-badge--eco">🌿 Écologique</span>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Prix + bouton -->
                <div class="trajet-cta">
                  <span class="trajet-prix"><?= (int)$c['prix_personne'] ?> €</span>
                  <a href="detail.php?id=<?= $c['covoiturage_id'] ?>" class="trajet-btn-detail">Voir le détail →</a>
                </div>

              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
  </div>

  <div id="injection-modal"></div>
  <?php include __DIR__ . '/../includes/layout.php'; ?>
  <script src="../assets/js/modal-connexion.js"></script>
  <script src="../assets/js/menu-toggle.js" defer></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      new ModalConnexion();
    });
  </script>
</body>
</html>
