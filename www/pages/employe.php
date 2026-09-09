<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

$roleId = $_SESSION['role_id'] ?? null;
if (!isset($_SESSION['user_id']) || !in_array($roleId, [1, 2], true)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Avis en attente de modération
$stmtAvis = $pdo->prepare("
    SELECT a.avis_id, a.auteur, a.commentaire, a.note, a.date_avis,
           c.covoiturage_id, c.lieu_depart, c.lieu_arrivee, c.date_depart,
           u.nom AS conducteur_nom
    FROM avis a
    JOIN covoiturage c ON a.covoiturage_id = c.covoiturage_id
    JOIN utilisateurs u ON c.utilisateur_id = u.id
    WHERE a.statut = 'en_attente'
    ORDER BY a.date_avis ASC
");
$stmtAvis->execute();
$avisEnAttente = $stmtAvis->fetchAll();

// Litiges en cours
$stmtLitiges = $pdo->prepare("
    SELECT p.covoiturage_id, p.utilisateur_id AS passager_id, p.commentaire_litige,
           up.nom AS passager_nom, up.email AS passager_email,
           uc.nom AS conducteur_nom, uc.email AS conducteur_email,
           c.lieu_depart, c.lieu_arrivee,
           c.date_depart, c.heure_depart,
           c.prix_personne
    FROM participation p
    JOIN covoiturage c  ON p.covoiturage_id = c.covoiturage_id
    JOIN utilisateurs up ON p.utilisateur_id = up.id
    JOIN utilisateurs uc ON c.utilisateur_id = uc.id
    WHERE p.statut = 'litige'
    ORDER BY c.date_depart DESC
");
$stmtLitiges->execute();
$litiges = $stmtLitiges->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espace Employé | EcoRide</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/../partials/menu.php'; ?>

  <main class="wrapper user-page">

    <div class="user-banner">
      <div class="user-banner-identity">
        <div class="user-avatar">🛠️</div>
        <div>
          <h1>Espace Employé</h1>
          <p>Modération des avis et gestion des litiges</p>
        </div>
      </div>
    </div>

    <!-- Avis en attente -->
    <div class="employe-section">
      <div class="employe-section-header">
        <h2>💬 Avis en attente de modération</h2>
        <span class="employe-count" id="count-avis"><?= count($avisEnAttente) ?></span>
      </div>

      <?php if (empty($avisEnAttente)): ?>
        <p class="employe-empty">Aucun avis en attente.</p>
      <?php else: ?>
        <div class="employe-list" id="liste-avis">
          <?php foreach ($avisEnAttente as $a): ?>
            <div class="employe-card" id="avis-<?= $a['avis_id'] ?>">
              <div class="employe-card-header">
                <div>
                  <span class="employe-card-title">
                    <?= htmlspecialchars($a['auteur']) ?>
                    — <?= htmlspecialchars($a['lieu_depart']) ?> → <?= htmlspecialchars($a['lieu_arrivee']) ?>
                    <em>(<?= date('d/m/Y', strtotime($a['date_depart'])) ?>)</em>
                  </span>
                  <span class="employe-card-sub">Conducteur : <?= htmlspecialchars($a['conducteur_nom']) ?></span>
                </div>
                <span class="employe-note">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $a['note'] ? 'star-full' : 'star-empty' ?>">★</span>
                  <?php endfor; ?>
                </span>
              </div>
              <?php if (!empty($a['commentaire'])): ?>
                <p class="employe-commentaire">"<?= htmlspecialchars($a['commentaire']) ?>"</p>
              <?php else: ?>
                <p class="employe-commentaire employe-no-comment">Aucun commentaire.</p>
              <?php endif; ?>
              <div class="employe-actions">
                <button class="btn-publier" data-id="<?= $a['avis_id'] ?>">✅ Publier</button>
                <button class="btn-rejeter" data-id="<?= $a['avis_id'] ?>">❌ Rejeter</button>
              </div>
              <p class="employe-msg" id="msg-avis-<?= $a['avis_id'] ?>"></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Litiges en cours -->
    <div class="employe-section">
      <div class="employe-section-header">
        <h2>⚠️ Litiges en cours</h2>
        <span class="employe-count" id="count-litiges"><?= count($litiges) ?></span>
      </div>

      <?php if (empty($litiges)): ?>
        <p class="employe-empty">Aucun litige en cours.</p>
      <?php else: ?>
        <div class="employe-list" id="liste-litiges">
          <?php foreach ($litiges as $l): ?>
            <div class="employe-card" id="litige-<?= $l['covoiturage_id'] ?>-<?= $l['passager_id'] ?>">
              <div class="employe-card-header">
                <div>
                  <span class="employe-card-title">
                    Trajet #<?= $l['covoiturage_id'] ?>
                    — <?= htmlspecialchars($l['lieu_depart']) ?> → <?= htmlspecialchars($l['lieu_arrivee']) ?>
                    <em>(<?= date('d/m/Y', strtotime($l['date_depart'])) ?> à <?= substr($l['heure_depart'], 0, 5) ?>)</em>
                  </span>
                </div>
                <span class="employe-prix"><?= (int)$l['prix_personne'] ?> crédits</span>
              </div>

              <div class="employe-parties">
                <div class="employe-partie">
                  <span class="employe-partie-label">🎒 Passager</span>
                  <strong><?= htmlspecialchars($l['passager_nom']) ?></strong>
                  <a href="mailto:<?= htmlspecialchars($l['passager_email']) ?>"><?= htmlspecialchars($l['passager_email']) ?></a>
                </div>
                <div class="employe-partie">
                  <span class="employe-partie-label">🚘 Conducteur</span>
                  <strong><?= htmlspecialchars($l['conducteur_nom']) ?></strong>
                  <a href="mailto:<?= htmlspecialchars($l['conducteur_email']) ?>"><?= htmlspecialchars($l['conducteur_email']) ?></a>
                </div>
              </div>

              <div class="employe-litige-desc">
                <span class="employe-litige-label">Problème signalé :</span>
                <p>"<?= htmlspecialchars($l['commentaire_litige']) ?>"</p>
              </div>

              <div class="employe-actions">
                <button class="btn-resoudre-conducteur"
                        data-cov="<?= $l['covoiturage_id'] ?>"
                        data-passager="<?= $l['passager_id'] ?>">
                  ✅ Conducteur non fautif <small>(créditer le conducteur)</small>
                </button>
                <button class="btn-resoudre-passager"
                        data-cov="<?= $l['covoiturage_id'] ?>"
                        data-passager="<?= $l['passager_id'] ?>">
                  🔄 Conducteur fautif <small>(rembourser le passager)</small>
                </button>
              </div>
              <p class="employe-msg" id="msg-litige-<?= $l['covoiturage_id'] ?>-<?= $l['passager_id'] ?>"></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>
  <?php include __DIR__ . '/../includes/layout.php'; ?>
  <div id="injection-modal"></div>
  <script src="../assets/js/modal-connexion.js"></script>
  <script src="../assets/js/menu-toggle.js" defer></script>
  <script src="../assets/js/employe.js" defer></script>
</body>
</html>
