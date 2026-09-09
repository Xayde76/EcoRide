<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT role FROM roles_utilisateurs WHERE utilisateur_id = ?");
$stmt->execute([$userId]);
$roleActuel = ($row = $stmt->fetch()) ? $row['role'] : '';

$stmtUser = $pdo->prepare("SELECT nom, email, credits, photo FROM utilisateurs WHERE id = ?");
$stmtUser->execute([$userId]);
$userInfo = $stmtUser->fetch();
$credits  = $userInfo['credits'];
$userNom  = $userInfo['nom'];
$userEmail = $userInfo['email'];
$userPhoto = $userInfo['photo'] ?? null;

$stmtVehicules = $pdo->prepare("SELECT * FROM vehicules WHERE utilisateur_id = ?");
$stmtVehicules->execute([$userId]);
$vehicules = $stmtVehicules->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mon Espace - EcoRide</title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700;800&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/../partials/menu.php'; ?>

  <main class="wrapper user-page">

    <!-- Bannière profil -->
    <div class="user-banner">
      <div class="user-banner-identity">
        <div class="user-avatar user-avatar--photo" id="avatar-wrap">
          <?php if ($userPhoto): ?>
            <img src="../images/profil/<?= htmlspecialchars($userPhoto) ?>" alt="Photo de profil" id="avatar-img" class="avatar-img">
          <?php else: ?>
            <span id="avatar-fallback">👤</span>
          <?php endif; ?>
          <label class="avatar-upload-btn" title="Changer la photo">
            📷
            <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp" class="hidden">
          </label>
        </div>
        <div>
          <h1 id="banner-nom"><?= htmlspecialchars($userNom) ?></h1>
          <p>Gérez votre profil EcoRide</p>
        </div>
      </div>
      <div class="credits-badge">
        <span class="credits-badge-icon">💰</span>
        <div>
          <div class="credits-badge-amount"><?= htmlspecialchars($credits) ?></div>
          <div class="credits-badge-label">Crédits</div>
        </div>
      </div>
    </div>

    <!-- Grille principale -->
    <div class="user-layout">

      <!-- Colonne gauche : actions -->
      <div class="user-left-col">

        <!-- Modifier profil -->
        <div class="user-card">
          <h2>✏️ Mon profil</h2>
          <form id="profil-form" autocomplete="off">
            <div class="profil-form-group">
              <label for="profil-nom">Nom complet</label>
              <input type="text" id="profil-nom" name="nom" value="<?= htmlspecialchars($userNom) ?>" required maxlength="255" />
            </div>
            <div class="profil-form-group">
              <label for="profil-email">Email</label>
              <input type="email" id="profil-email" name="email" value="<?= htmlspecialchars($userEmail) ?>" required />
            </div>
            <div class="profil-form-group">
              <label for="profil-password">Nouveau mot de passe <small>(laisser vide pour ne pas changer)</small></label>
              <input type="password" id="profil-password" name="password" placeholder="••••••" minlength="6" />
            </div>
            <button type="submit" class="btn btn-profil-save">Enregistrer</button>
            <span id="profil-message"></span>
          </form>
        </div>

        <!-- Rôle -->
        <div class="user-card">
          <h2>🎭 Mon rôle</h2>
          <div class="role-form-wrapper">
            <form id="role-form" autocomplete="off">
              <label for="role">Je suis :</label>
              <select name="role" id="role" required>
                <option value="" disabled <?= $roleActuel === '' ? 'selected' : '' ?>>-- Sélectionnez --</option>
                <option value="passager" <?= $roleActuel === 'passager' ? 'selected' : '' ?>>Passager</option>
                <option value="chauffeur" <?= $roleActuel === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
                <option value="chauffeur_passager" <?= $roleActuel === 'chauffeur_passager' ? 'selected' : '' ?>>Chauffeur / Passager</option>
              </select>
            </form>
            <span id="role-message"></span>
          </div>
        </div>

        <!-- Infos chauffeur -->
        <div class="user-card hidden" id="chauffeur-info">
          <h2>🚗 Informations Chauffeur</h2>

          <h3 id="titre-vehicules" class="<?= empty($vehicules) ? 'hidden' : '' ?>">Mes véhicules</h3>
          <div id="vehicules-list">
            <?php foreach ($vehicules as $v): ?>
              <div class="vehicule" data-id="<?= $v['id'] ?>">
                <div class="vehicule-info">
                  <strong><?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?></strong>
                  <span><?= htmlspecialchars($v['couleur']) ?> · <?= htmlspecialchars($v['plaque']) ?></span>
                </div>
                <button class="btn-delete supprimer-btn" data-id="<?= $v['id'] ?>">Supprimer</button>
              </div>
            <?php endforeach; ?>
          </div>

          <p id="message-aucun-vehicule" class="info-msg warning <?= !empty($vehicules) ? 'hidden' : '' ?>">
            Enregistrez un véhicule pour utiliser le service en tant que chauffeur.
          </p>

          <!-- Formulaire d'ajout dépliable -->
          <span id="message-ajout" class="hidden"></span>
          <details class="add-vehicle-details">
            <summary>➕ Ajouter un véhicule</summary>
            <form id="form-ajout-vehicule" class="vehicle-form">
              <div class="form-row">
                <input type="text" name="plaque" placeholder="Plaque d'immatriculation" required>
                <input type="date" name="date_immat" required>
              </div>
              <div class="form-row">
                <input type="text" name="marque" placeholder="Marque" required>
                <input type="text" name="modele" placeholder="Modèle" required>
              </div>
              <div class="form-row">
                <input type="text" name="couleur" placeholder="Couleur" required>
                <input type="number" name="places" placeholder="Nb. places" min="1" max="9" required>
              </div>
              <select name="type_vehicule" id="type_vehicule" required>
                <option value="" disabled selected>-- Type de véhicule --</option>
                <option value="essence">Essence</option>
                <option value="diesel">Diesel</option>
                <option value="electrique">Électrique</option>
                <option value="hybride">Hybride</option>
              </select>
              <div class="prefs-group">
                <p class="prefs-title">Préférences</p>
                <label class="checkbox-label"><input type="checkbox" name="prefs[]" value="fumeur"> Fumeurs acceptés</label>
                <label class="checkbox-label"><input type="checkbox" name="prefs[]" value="animaux"> Animaux acceptés</label>
                <input type="text" name="prefs_autres" placeholder="Autres préférences...">
              </div>
              <button type="submit">Ajouter le véhicule</button>
              <div id="message-ajout-vehicule" class="hidden"></div>
            </form>
          </details>
        </div>

        <!-- Création de voyage -->
        <div class="user-card hidden" id="voyage-creation">
          <h2>➕ Nouveau covoiturage</h2>
          <form id="form-voyage">
            <label class="field-label">Véhicule utilisé</label>
            <select name="vehicule_id" id="vehicule-select" required>
              <?php foreach ($vehicules as $v): ?>
                <option value="<?= $v['id'] ?>">
                  <?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?> · <?= htmlspecialchars($v['plaque']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-row">
              <input type="text" name="depart" placeholder="Ville de départ" required>
              <input type="text" name="destination" placeholder="Ville d'arrivée" required>
            </div>
            <label class="field-label-inline">
              Date de départ
              <input type="date" name="date_depart" id="date_depart" required>
            </label>
            <div class="form-row">
              <label class="field-label-inline">
                Heure de départ
                <input type="time" name="heure_depart" required>
              </label>
              <label class="field-label-inline">
                Heure d'arrivée <span class="field-optional">(optionnel)</span>
                <input type="time" name="heure_arrivee">
              </label>
            </div>
            <label class="field-label-inline">
              Prix par personne (crédits)
              <input type="number" name="prix" placeholder="Ex : 5" step="1" min="2" required>
            </label>
            <button type="submit">Créer le covoiturage</button>
          </form>
          <div id="message-voyage" class="hidden"></div>
        </div>

      </div><!-- /user-left-col -->

      <!-- Colonne droite : historique -->
      <div class="user-right-col">
        <div class="user-card" id="historique-covoiturages">
          <h2>📋 Mes covoiturages</h2>

          <?php
          $aujourdHui = date('Y-m-d');
          $stmtConducteur = $pdo->prepare("SELECT * FROM covoiturage WHERE utilisateur_id = ? AND statut != 'annule' ORDER BY date_depart DESC");
          $stmtConducteur->execute([$userId]);
          $covoituragesConducteur = $stmtConducteur->fetchAll();
          $stmtPassager = $pdo->prepare("SELECT c.*, p.statut AS participation_statut FROM participation p JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id WHERE p.utilisateur_id = ? AND c.statut != 'annule' ORDER BY c.date_depart DESC");
          $stmtPassager->execute([$userId]);
          $covoituragesPassager = $stmtPassager->fetchAll();
          ?>

          <!-- Conducteur -->
          <div class="historique-bloc">
            <div class="historique-header">🚘 En tant que conducteur</div>
            <p id="empty-conducteur" class="empty-msg" <?= !empty($covoituragesConducteur) ? 'style="display:none"' : '' ?>>
              Aucun covoiturage créé pour l'instant.
            </p>
            <ul id="historique-conducteur" <?= empty($covoituragesConducteur) ? 'style="display:none"' : '' ?>>
              <?php foreach ($covoituragesConducteur as $c):
                $isPast    = $c['date_depart'] < $aujourdHui;
                $isToday   = $c['date_depart'] === $aujourdHui;
                $canStart  = $c['statut'] === 'disponible' && ($isToday || $isPast);
                $isRunning = $c['statut'] === 'en_cours';
                $isTermine = $c['statut'] === 'termine';

                if ($isRunning) {
                    $itemClass = ''; $badgeClass = 'en-cours'; $badgeLabel = 'En cours';
                } elseif ($isTermine) {
                    $itemClass = 'passe'; $badgeClass = 'passe'; $badgeLabel = 'Terminé';
                } elseif ($isPast) {
                    $itemClass = 'passe'; $badgeClass = 'passe'; $badgeLabel = 'Terminé';
                } else {
                    $itemClass = ''; $badgeClass = $c['statut']; $badgeLabel = ucfirst($c['statut']);
                }
              ?>
                <li class="trip-item <?= $itemClass ?>">
                  <a href="detail.php?id=<?= $c['covoiturage_id'] ?>" class="trip-info-link">
                    <div class="trip-info">
                      <div class="trip-route"><?= htmlspecialchars($c['lieu_depart']) ?> → <?= htmlspecialchars($c['lieu_arrivee']) ?></div>
                      <div class="trip-meta">📅 <?= date('d/m/Y', strtotime($c['date_depart'])) ?></div>
                      <span class="statut-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    </div>
                  </a>
                  <?php if ($c['statut'] === 'disponible' && !$isPast && !$isToday): ?>
                    <form class="annuler-covoiturage-form" data-id="<?= $c['covoiturage_id'] ?>" data-type="conducteur">
                      <input type="hidden" name="id" value="<?= $c['covoiturage_id'] ?>">
                      <button type="submit" class="btn-annuler">Supprimer</button>
                    </form>
                  <?php elseif ($canStart): ?>
                    <button class="btn-demarrer" data-id="<?= $c['covoiturage_id'] ?>">▶ Démarrer</button>
                  <?php elseif ($isRunning): ?>
                    <button class="btn-terminer" data-id="<?= $c['covoiturage_id'] ?>">🏁 Arrivée à destination</button>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Passager -->
          <div class="historique-bloc">
            <div class="historique-header">🎒 En tant que passager</div>
            <p id="empty-passager" class="empty-msg" <?= !empty($covoituragesPassager) ? 'style="display:none"' : '' ?>>
              Aucune participation pour l'instant.
            </p>
            <ul id="historique-passager" <?= empty($covoituragesPassager) ? 'style="display:none"' : '' ?>>
              <?php foreach ($covoituragesPassager as $c):
                $isPast          = $c['date_depart'] < $aujourdHui;
                $isTermine       = $c['statut'] === 'termine';
                $isRunning       = $c['statut'] === 'en_cours';
                $partStatut      = $c['participation_statut'] ?? 'en_attente';
                $needsValidation = $isTermine && $partStatut === 'en_attente';

                if ($isRunning) {
                    $itemClass = ''; $badgeClass = 'en-cours'; $badgeLabel = 'En cours';
                } elseif ($isTermine && $partStatut === 'validee') {
                    $itemClass = 'passe'; $badgeClass = 'validee'; $badgeLabel = 'Validé ✓';
                } elseif ($isTermine && $partStatut === 'litige') {
                    $itemClass = 'passe'; $badgeClass = 'litige'; $badgeLabel = 'Litige en cours';
                } elseif ($isTermine) {
                    $itemClass = ''; $badgeClass = 'en-cours'; $badgeLabel = 'À valider';
                } elseif ($isPast) {
                    $itemClass = 'passe'; $badgeClass = 'passe'; $badgeLabel = 'Terminé';
                } else {
                    $itemClass = ''; $badgeClass = $c['statut']; $badgeLabel = ucfirst($c['statut']);
                }
              ?>
                <li class="trip-item <?= $itemClass ?>">
                  <a href="detail.php?id=<?= $c['covoiturage_id'] ?>" class="trip-info-link">
                    <div class="trip-info">
                      <div class="trip-route"><?= htmlspecialchars($c['lieu_depart']) ?> → <?= htmlspecialchars($c['lieu_arrivee']) ?></div>
                      <div class="trip-meta">📅 <?= date('d/m/Y', strtotime($c['date_depart'])) ?></div>
                      <span class="statut-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                    </div>
                  </a>
                  <?php if ($c['statut'] === 'disponible' && !$isPast): ?>
                    <form class="annuler-covoiturage-form" data-id="<?= $c['covoiturage_id'] ?>" data-type="passager">
                      <input type="hidden" name="id" value="<?= $c['covoiturage_id'] ?>">
                      <button type="submit" class="btn-annuler">Supprimer</button>
                    </form>
                  <?php elseif ($needsValidation): ?>
                    <div class="validation-panel">
                      <div class="vp-btns">
                        <button class="btn-ok-trajet" data-id="<?= $c['covoiturage_id'] ?>">👍 Tout s'est bien passé</button>
                        <button class="btn-prob-trajet" data-id="<?= $c['covoiturage_id'] ?>">⚠️ Signaler un problème</button>
                      </div>
                      <form class="vp-avis-form hidden" data-id="<?= $c['covoiturage_id'] ?>">
                        <p class="vp-label">Laisser un avis (optionnel)</p>
                        <div class="vp-stars">
                          <span class="vp-star" data-val="1">★</span>
                          <span class="vp-star" data-val="2">★</span>
                          <span class="vp-star" data-val="3">★</span>
                          <span class="vp-star" data-val="4">★</span>
                          <span class="vp-star" data-val="5">★</span>
                        </div>
                        <input type="hidden" class="vp-note-val" value="">
                        <textarea class="vp-commentaire" placeholder="Votre commentaire..."></textarea>
                        <button type="submit" class="btn-submit-ok">Confirmer la validation</button>
                      </form>
                      <form class="vp-prob-form hidden" data-id="<?= $c['covoiturage_id'] ?>">
                        <textarea class="vp-prob-commentaire" placeholder="Décrivez le problème rencontré..." required></textarea>
                        <button type="submit" class="btn-submit-prob">Envoyer le signalement</button>
                      </form>
                    </div>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

        </div>
      </div><!-- /user-right-col -->

    </div><!-- /user-layout -->
  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>
  <div id="injection-modal"></div>
  <?php include __DIR__ . '/../includes/layout.php'; ?>
  <script src="../assets/js/modal-connexion.js"></script>
  <script src="../assets/js/user-espace.js"></script>
  <script src="../assets/js/menu-toggle.js" defer></script>
</body>
</html>
