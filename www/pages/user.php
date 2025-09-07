<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../includes/auth.php';

$userId = $_SESSION['user_id'];

// Récupérer le rôle actuel depuis la BDD
$stmt = $pdo->prepare("SELECT role FROM roles_utilisateurs WHERE utilisateur_id = ?");
$stmt->execute([$userId]);
$roleActuel = ($row = $stmt->fetch()) ? $row['role'] : '';

// Récupérer les crédits de l'utilisateur
$stmtCredits = $pdo->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
$stmtCredits->execute([$userId]);
$credits = $stmtCredits->fetchColumn();

// Récupérer les véhicules existants
$stmtVehicules = $pdo->prepare("SELECT * FROM vehicules WHERE utilisateur_id = ?");
$stmtVehicules->execute([$userId]);
$vehicules = $stmtVehicules->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profil - EcoRide</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include '../partials/menu.php'; ?>

  <main class="wrapper">
    <h1 class="hero-title">Mon Espace</h1>
    <div class="credits-display">Crédits disponibles : <?= htmlspecialchars($credits) ?> 💰</div>

    <section class="user-role">
      <h2>Choisissez votre rôle</h2>
      <form id="role-form" autocomplete="off">
        <label for="role">Je suis :</label>
        <select name="role" id="role" required>
          <option value="" disabled <?= $roleActuel === '' ? 'selected' : '' ?>>-- Sélectionnez --</option>
          <option value="passager" <?= $roleActuel === 'passager' ? 'selected' : '' ?>>Passager</option>
          <option value="chauffeur" <?= $roleActuel === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
          <option value="chauffeur_passager" <?= $roleActuel === 'chauffeur_passager' ? 'selected' : '' ?>>Chauffeur / Passager</option>
        </select>
      </form>
      <span id="role-message" style="margin-left:1rem;"></span>
    </section>

    <section class="chauffeur-info" id="chauffeur-info">
      <h2>Informations Chauffeur</h2>
      <h3 id="titre-vehicules" <?= empty($vehicules) ? 'style="display:none;"' : '' ?>>Mes véhicules enregistrés</h3>
      <div id="vehicules-list">
        <?php foreach ($vehicules as $v): ?>
          <div class="vehicule" data-id="<?= $v['id'] ?>">
            <p><strong><?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?></strong> (<?= htmlspecialchars($v['couleur']) ?>) - <?= htmlspecialchars($v['plaque']) ?></p>
            <button class="btn-delete supprimer-btn" data-id="<?= $v['id'] ?>">Supprimer</button>
          </div>
        <?php endforeach; ?>
      </div>

      <p id="message-ajout" <?= empty($vehicules) ? 'style="display:none;"' : '' ?>>Vous pouvez ajouter un nouveau véhicule si besoin :</p>
      <p id="message-aucun-vehicule" <?= !empty($vehicules) ? 'style="display:none;"' : '' ?>>
        <strong>Veuillez enregistrer un véhicule pour pouvoir utiliser le service en tant que chauffeur.</strong>
      </p>

      <form id="form-ajout-vehicule">
        <input type="text" name="plaque" placeholder="Plaque d'immatriculation" required>
        <input type="date" name="date_immat" required>
        <input type="text" name="modele" placeholder="Modèle" required>
        <input type="text" name="marque" placeholder="Marque" required>
        <input type="text" name="couleur" placeholder="Couleur" required>
        <input type="number" name="places" placeholder="Places disponibles" min="1" required>
        <label for="type_vehicule"><strong>Type de véhicule</strong></label>
        <select name="type_vehicule" id="type_vehicule" required>
          <option value="" disabled selected>-- Sélectionnez le type --</option>
          <option value="essence">Essence</option>
          <option value="diesel">Diesel</option>
          <option value="électrique">Électrique</option>
          <option value="hybride">Hybride</option>
        </select>
        <h3>Préférences</h3>
        <div class="checkbox-group">
          <label><input type="checkbox" name="prefs[]" value="fumeur"> Accepte fumeurs</label>
        </div>
        <div class="checkbox-group">
          <label><input type="checkbox" name="prefs[]" value="animaux"> Accepte animaux</label>
        </div>
        <input type="text" name="prefs_autres" placeholder="Autres préférences...">
        <button type="submit">Ajouter un véhicule</button>
      </form>
    </section>

    <section class="voyage-creation" id="voyage-creation" style="display: none;">
      <h3>Créer un nouveau covoiturage</h3>
      <form id="form-voyage">
        <label for="vehicule">Véhicule utilisé :</label>
        <select name="vehicule_id" required>
          <?php foreach ($vehicules as $v): ?>
            <option value="<?= $v['id'] ?>">
              <?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?> - <?= htmlspecialchars($v['plaque']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="text" name="depart" placeholder="Ville de départ" required>
        <input type="text" name="destination" placeholder="Ville d’arrivée" required>
        <input type="datetime-local" name="date_depart" id="date_depart" required>
        <input type="number" name="prix" placeholder="Prix (€)" step="1" min="2" required>
        <button type="submit">Créer le covoiturage</button>
      </form>
      <div id="message-voyage" style="display: none; margin-bottom: 1rem; text-align:center; font-weight:bold;"></div>
    </section>

    <section class="historique-covoiturages" id="historique-covoiturages">
      <h2>Historique de mes covoiturages</h2>
      <div id="message-voyage" style="text-align:center; color: green; font-weight: bold; margin-bottom: 1rem;"></div>
      <?php
      $aujourdHui = date('Y-m-d');
      $stmtConducteur = $pdo->prepare("SELECT * FROM covoiturage WHERE utilisateur_id = ? ORDER BY date_depart DESC");
      $stmtConducteur->execute([$userId]);
      $covoituragesConducteur = $stmtConducteur->fetchAll();
      $stmtPassager = $pdo->prepare("SELECT c.* FROM participation p JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id WHERE p.utilisateur_id = ? ORDER BY c.date_depart DESC");
      $stmtPassager->execute([$userId]);
      $covoituragesPassager = $stmtPassager->fetchAll();
      ?>
      <div class="historique-bloc">
        <h3>En tant que conducteur</h3>
        <?php if (empty($covoituragesConducteur)): ?>
          <p>Aucun covoiturage créé.</p>
        <?php else: ?>
          <ul id="historique-conducteur">
            <?php foreach ($covoituragesConducteur as $c): ?>
              <?php
              $statutClass = '';
              if ($c['statut'] === 'annulé') {
                $statutClass = 'annule';
              } elseif ($c['date_depart'] < $aujourdHui) {
                $statutClass = 'passe';
              }
              ?>
              <li class="<?= $statutClass ?>">
                <div>
                  <strong><?= htmlspecialchars($c['lieu_depart']) ?> → <?= htmlspecialchars($c['lieu_arrivee']) ?></strong><br>
                  <span>📅 le <?= date('d/m/Y', strtotime($c['date_depart'])) ?></span><br>
                  <span>🛈 Statut : <?= htmlspecialchars($c['statut']) ?></span>
                </div>
                <?php if ($c['statut'] === 'disponible'): ?>
                  <form class="annuler-covoiturage-form" data-id="<?= $c['covoiturage_id'] ?>" data-type="conducteur">
                    <input type="hidden" name="id" value="<?= $c['covoiturage_id'] ?>">
                    <button type="submit" class="btn-delete">Annuler</button>
                  </form>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <div class="historique-bloc">
        <h3>En tant que passager</h3>
        <?php if (empty($covoituragesPassager)): ?>
          <p>Aucune participation à un covoiturage.</p>
        <?php else: ?>
          <ul id="historique-passager">
            <?php foreach ($covoituragesPassager as $c): ?>
              <?php
              $statutClass = '';
              if ($c['statut'] === 'annulé') {
                $statutClass = 'annule';
              } elseif ($c['date_depart'] < $aujourdHui) {
                $statutClass = 'passe';
              }
              ?>
              <li class="<?= $statutClass ?>">
                <div>
                  <strong><?= htmlspecialchars($c['lieu_depart']) ?> → <?= htmlspecialchars($c['lieu_arrivee']) ?></strong><br>
                  <span>📅 le <?= date('d/m/Y', strtotime($c['date_depart'])) ?></span><br>
                  <span>🛈 Statut : <?= htmlspecialchars($c['statut']) ?></span>
                </div>
                <?php if ($c['statut'] === 'disponible'): ?>
                  <form class="annuler-covoiturage-form" data-id="<?= $c['covoiturage_id'] ?>" data-type="passager">
                    <input type="hidden" name="id" value="<?= $c['covoiturage_id'] ?>">
                    <button type="submit" class="btn-delete">Annuler</button>
                  </form>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include '../partials/footer.php'; ?>
  <div id="injection-modal"></div>
  <?php include '../includes/layout.php'; ?>
  <script src="../assets/js/modal-connexion.js"></script>
  <script src="../assets/js/user-espace.js"></script>
</body>
</html>
