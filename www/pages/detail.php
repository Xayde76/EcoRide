<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_GET['id'])) {
    header('Location: covoiturage.php');
    exit;
}

$id = $_GET['id'];

// Récupération des informations du covoiturage
$stmt = $pdo->prepare("SELECT c.*, u.nom AS conducteur_nom, v.modele, v.marque, v.couleur, v.preferences
                       FROM covoiturage c
                       JOIN utilisateurs u ON c.utilisateur_id = u.id
                       LEFT JOIN vehicules v ON c.vehicule_id = v.id
                       WHERE c.covoiturage_id = ?");
$stmt->execute([$id]);
$trajet = $stmt->fetch();

if (!$trajet) {
    echo "<p>Trajet introuvable.</p>";
    exit;
}

$eco = $trajet['type_vehicule'] === 'électrique' ? 'Oui' : 'Non';

// Récupération des avis liés à ce covoiturage
$stmtAvis = $pdo->prepare("SELECT auteur, commentaire, note FROM avis WHERE statut = 'publié' AND covoiturage_id = ?");
$stmtAvis->execute([$id]);
$avis = $stmtAvis->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détail du Voyage</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <div class="wrapper">
    <?php include '../partials/menu.php'; ?>
    <main>
      <h1 class="hero-title">Ce Voyage</h1>
      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-error">
          <?= htmlspecialchars($_SESSION['error']) ?>
          <?php unset($_SESSION['error']); ?>
        </div>
      <?php endif; ?>
      <section class="detail-voyage-card">
        <div class="info-global">
          <p><strong><?= htmlspecialchars($trajet['lieu_depart']) ?></strong> → <strong><?= htmlspecialchars($trajet['lieu_arrivee']) ?></strong></p>
          <p>le <?= date('d/m/Y', strtotime($trajet['date_depart'])) ?> pour <?= htmlspecialchars($trajet['prix_personne']) ?> crédits</p>
        </div>

        <div class="grille-voyage">
          <div class="horaires">
            <p><?= date('H\hi', strtotime($trajet['heure_depart'])) ?></p>
            <?php if ($trajet['heure_arrivee']): ?>
              <div class="ligne-temps"></div>
              <p><?= date('H\hi', strtotime($trajet['heure_arrivee'])) ?></p>
            <?php endif; ?>
            <p>Places restantes : <?= htmlspecialchars($trajet['nb_place']) ?></p>
            <p>Écologique : <?= $eco ?></p>
          </div>

          <div class="conducteur-box">
            <img src="../images/profil/default.png" alt="Conducteur" class="conducteur-photo">
            <p><?= htmlspecialchars($trajet['conducteur_nom']) ?></p>
            <div class="participer-wrapper">
              <button class="btn" id="participer-btn" data-id="<?= $trajet['covoiturage_id'] ?>">Participer</button>
              <p id="message-participation" style="margin-top: 1rem; color: red;"></p>
            </div>
          </div>
        </div>

        <div class="details">
          <h3>Détails :</h3>
          <p>Véhicule : <?= htmlspecialchars($trajet['modele']) ?> / <?= htmlspecialchars($trajet['marque']) ?> / <?= htmlspecialchars($trajet['type_vehicule']) ?></p>
          <p>Commentaire : <?= htmlspecialchars($trajet['preferences'] ?? 'Aucun') ?></p>
        </div>
      </section>

      <div class="retour-wrapper">
      <a href="covoiturage.php" class="retour-link">&larr; Retour aux covoiturages</a>
    </div>


      <section class="avis-section">
        <h2>Avis</h2>
        <?php if (empty($avis)): ?>
          <p>Aucun avis pour ce trajet.</p>
        <?php else: ?>
          <?php foreach ($avis as $a): ?>
            <div class="avis-card">
              <p><strong><?= htmlspecialchars($a['auteur']) ?></strong></p>
              <p><?= htmlspecialchars($a['commentaire']) ?></p>
              <p>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <?= $i <= (int)$a['note'] ? '&#9733;' : '&#9734;' ?>
                <?php endfor; ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </main>
    <?php include '../partials/footer.php'; ?>
    <?php include '../includes/layout.php'; ?>
    <script src="../assets/js/modal-connexion.js"></script>
    <script src="../assets/js/participer.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        new ModalConnexion();
      });
    </script>
    </div>
</body>
</html>
