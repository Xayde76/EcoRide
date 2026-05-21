<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contactez EcoRide | Covoiturage écologique</title>
  <meta name="description" content="Une question sur EcoRide ? Contactez notre équipe via le formulaire ou par email. Nous répondons rapidement.">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="https://www.ecoride.fr/pages/contact.php">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.ecoride.fr/pages/contact.php">
  <meta property="og:title" content="Contactez EcoRide">
  <meta property="og:description" content="Une question sur notre service de covoiturage ? Écrivez-nous, nous sommes là pour vous aider.">
  <meta property="og:image" content="https://www.ecoride.fr/images/logo-ecoride.png">

  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/../partials/menu.php'; ?>

  <main class="wrapper user-page">

    <div class="user-banner">
      <div class="user-banner-identity">
        <div class="user-avatar">✉️</div>
        <div>
          <h1>Contactez-nous</h1>
          <p>Une question ? Nous vous répondons rapidement</p>
        </div>
      </div>
    </div>

    <div class="contact-wrapper">
      <?php if (isset($_GET['success'])): ?>
        <p class="contact-msg contact-msg--success">Votre message a été envoyé avec succès !</p>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
        <p class="contact-msg contact-msg--error">Veuillez remplir tous les champs (email valide, message d'au moins 2 caractères).</p>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'send'): ?>
        <p class="contact-msg contact-msg--error">Une erreur est survenue lors de l'envoi. Veuillez réessayer.</p>
      <?php endif; ?>
      <p>Une question, une suggestion ou un souci ? Envoyez-nous un message via le formulaire ci-dessous.</p>

      <form action="../actions/traitement_contact.php" method="post" class="contact-form">
        <label for="nom"><strong>Nom complet :</strong></label>
        <input type="text" id="nom" name="nom" required />

        <label for="email"><strong>Adresse email :</strong></label>
        <input type="email" id="email" name="email" required />

        <label for="message"><strong>Message :</strong></label>
        <textarea id="message" name="message" rows="6" required></textarea>

        <button type="submit">Envoyer</button>
      </form>

      <div class="contact-info">
        <h2>Nos coordonnées</h2>
        <p><strong>Email :</strong> <a href="mailto:contact.ecoride76@gmail.com">contact.ecoride76@gmail.com</a></p>
        <p><strong>Adresse :</strong> 123 rue des Transports, 75000 Paris, France</p>
        <p><strong>Téléphone :</strong> 0102030405</p>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/../partials/footer.php'; ?>

<?php include __DIR__ . '/../includes/layout.php'; ?>
<div id="injection-modal"></div>
<script src="../assets/js/modal-connexion.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    new ModalConnexion();
  });
</script>
<script src="../assets/js/menu-toggle.js" defer></script>
</body>
</html>
