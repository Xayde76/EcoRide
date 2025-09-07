<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact – EcoRide</title>
  <link rel="stylesheet" href="../assets/css/styles.css" />
  <link rel="icon" href="data:,">
  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
</head>
<body>
<div class="wrapper"> <!-- ✅ Ajouté -->

  <?php include '../partials/menu.php'; ?>

  <main>
    <div class="contact-wrapper">
      <h1>Contactez-nous</h1>
      <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Votre message a été envoyé avec succès !</p>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
        <p style="color: red;">Veuillez remplir tous les champs correctement.</p>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'send'): ?>
        <p style="color: red;">Une erreur est survenue lors de l’envoi. Veuillez réessayer.</p>
      <?php endif; ?>
      <p>Une question, une suggestion ou un souci ? Envoyez-nous un message via le formulaire ci-dessous.</p>

      <form action="../actions/traitement_contact.php" method="post" class="contact-form">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required />

        <label for="email">Adresse email :</label>
        <input type="email" id="email" name="email" required />

        <label for="message">Message :</label>
        <textarea id="message" name="message" rows="6" required></textarea>

        <button type="submit">Envoyer</button>
      </form>

      <div class="contact-info">
        <h2>Nos coordonnées</h2>
        <p><strong>Email :</strong> <a href="mailto:contact@ecoride.fr">contact@ecoride.fr</a></p>
        <p><strong>Adresse :</strong> 123 rue des Transports, 75000 Paris, France</p>
        <p><strong>Téléphone :</strong> 0102030405</p>
      </div>
    </div>
  </main>

  <?php include '../partials/footer.php'; ?>

</div> <!-- ✅ Fermeture du wrapper -->

<!-- Scripts -->
<?php require_once __DIR__ . '/../includes/layout.php'; ?>
<div id="injection-modal"></div>
<script src="../assets/js/modal-connexion.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    new ModalConnexion();
  });
</script>
<script src="../assets/js/carousel.js"></script>
<script src="../assets/js/menu-toggle.js" defer></script>
</body>
</html>