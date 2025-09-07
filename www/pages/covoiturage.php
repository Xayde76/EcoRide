<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../classes/MenuBuilder.php';
require_once __DIR__ . '/../classes/CovoiturageManager.php';
session_start();

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

if (!empty($filtres['depart']) && !empty($filtres['destination']) && !empty($filtres['date'])) {
    $covoiturages = $covoiturageManager->rechercherCovoiturages($filtres);
    if (empty($covoiturages)) {
        $suggestion = $covoiturageManager->suggereProchaineDate($filtres['depart'], $filtres['destination']);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Covoiturage - EcoRide</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <div class="wrapper">
    <?php include '../partials/menu.php'; ?>

    <main>
      <h1 class="hero-title">Où vas-t-on ?</h1>

      <form class="covoiturage-search" method="GET" action="">
        <input type="text" name="depart" placeholder="Départ" value="<?= htmlspecialchars($filtres['depart']) ?>" />
        <input type="text" name="destination" placeholder="Arrivée" value="<?= htmlspecialchars($filtres['destination']) ?>" />
        <input type="date" name="date" value="<?= htmlspecialchars($filtres['date']) ?>" />

        <details class="filtre-depliant">
          <summary>Filtres avancés</summary>
          <label>
            <input type="checkbox" name="ecologique" <?= $filtres['ecologique'] ? 'checked' : '' ?> />
            Voyage écologique
          </label>
          <label>
            Prix max (€) :
            <input type="number" name="prix_max" value="<?= htmlspecialchars($filtres['prix_max']) ?>" />
          </label>
          <label>
            Durée max (minutes) :
            <input type="number" name="duree_max" value="<?= htmlspecialchars($filtres['duree_max']) ?>" />
          </label>
          <label>
            Note min :
            <input type="number" step="0.1" name="note_min" value="<?= htmlspecialchars($filtres['note_min']) ?>" />
          </label>
        </details>

        <button type="submit" class="recherche-btn">Recherche</button>
      </form>

      <?php if ($suggestion): ?>
        <p style="text-align:center; margin-top:2rem;">
          Aucun covoiturage pour cette date. Prochain trajet disponible : <strong><?= date('d/m/Y', strtotime($suggestion)) ?></strong>
        </p>
      <?php endif; ?>

      <?php if (!empty($filtres['depart']) && !empty($filtres['destination']) && !empty($filtres['date'])): ?>
        <section class="trajets-list">
          <?php if (empty($covoiturages)): ?>
            <p style="text-align:center; margin-top:2rem;">Aucun covoiturage disponible pour le moment.</p>
          <?php else: ?>
            <?php foreach ($covoiturages as $c): ?>
              <div class="trajet-card">
                <div class="conducteur-info">
                  <img src="../images/profil/default.png" alt="Photo conducteur" />
                  <p><?= htmlspecialchars($c['conducteur_nom']) ?></p>
                  <p>⭐ <?= number_format($c['note'] ?? 0, 1) ?>/5</p>
                </div>
                <div class="details">
                  <p><strong>Places :</strong> <?= $c['nb_place'] ?></p>
                  <p><strong>Prix :</strong> <?= $c['prix_personne'] ?> €</p>
                  <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($c['date_depart'])) ?></p>
                  <p><strong>Heure :</strong> <?= substr($c['heure_depart'], 0, 5) ?> <?= $c['heure_arrivee'] ? '→ ' . substr($c['heure_arrivee'], 0, 5) : '' ?></p>
                  <p><strong>EcoVoyage :</strong> <?= ($c['type_vehicule'] === 'électrique') ? 'Oui' : 'Non' ?></p>
                </div>
                <div class="trajet-direction">
                  <p><strong><?= htmlspecialchars($c['lieu_depart']) ?></strong></p>
                  <p>↓</p>
                  <p><strong><?= htmlspecialchars($c['lieu_arrivee']) ?></strong></p>
                  <a href="detail.php?id=<?= $c['covoiturage_id'] ?>" class="btn">Détail</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      <?php endif; ?>
    </main>

    <?php include '../partials/footer.php'; ?>
  </div>

  <div id="injection-modal"></div>
  <?php include '../includes/layout.php'; ?>
  <script src="../assets/js/modal-connexion.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      new ModalConnexion();
    });
  </script>
</body>
</html>