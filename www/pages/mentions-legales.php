<?php session_start();
require_once __DIR__ . '/../classes/MenuBuilder.php';
require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../includes/layout.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mentions Légales | EcoRide</title>
    <link rel="stylesheet" href="/assets/css/styles.css" />
    <link rel="icon" href="data:,">
    <link
      href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap"
      rel="stylesheet"
    />
    <style>
      main {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background-color: var(--pure-white);
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }

      h1,
      h2 {
        color: var(--main-green);
        margin-bottom: 1rem;
      }

      p {
        margin-bottom: 1rem;
      }
    </style>
  </head>
  <body>
    <div class="wrapper">
      <?php include __DIR__ . '/../partials/menu.php'; ?>

      <main>
        <h1>Mentions Légales</h1>

        <h2>Éditeur du site</h2>
        <p><strong>Nom :</strong> EcoRide</p>
        <p>
          <strong>Adresse :</strong> 123 rue des Transports, 75000 Paris, France
        </p>
        <p><strong>Email :</strong> contact@ecoride.fr</p>
        <p><strong>Directeur de publication :</strong> José Leclerc</p>

        <h2>Hébergement</h2>
        <p>
          <strong>Nom de l'hébergeur :</strong> Infomaniak / OVH / 1&1 (au
          choix)
        </p>
        <p>
          <strong>Adresse :</strong> 2 rue Kellermann, 59100 Roubaix, France
        </p>
        <p><strong>Téléphone :</strong> 1007</p>

        <h2>Propriété intellectuelle</h2>
        <p>
          Le site EcoRide ainsi que tous ses contenus (textes, images, logo,
          etc.) sont la propriété exclusive d’EcoRide, sauf mention contraire.
          Toute reproduction, représentation, diffusion ou exploitation
          partielle ou totale est interdite sans autorisation préalable.
        </p>

        <h2>Protection des données personnelles</h2>
        <p>
          Conformément au Règlement Général sur la Protection des Données
          (RGPD), vous disposez d’un droit d’accès, de rectification, de
          suppression et d’opposition aux données personnelles vous concernant.
          Pour exercer ce droit, veuillez nous contacter à :
          <strong>contact@ecoride.fr</strong>
        </p>

        <h2>Cookies</h2>
        <p>
          Le site EcoRide peut utiliser des cookies à des fins de statistiques
          ou d'amélioration de l’expérience utilisateur. Vous pouvez les refuser
          via les paramètres de votre navigateur.
        </p>
      </main>

      <?php include __DIR__ . '/../partials/footer.php'; ?>
    </div>
    <div id="injection-modal"></div>
    <?php include '../includes/layout.php'; ?>
    <script src="/assets/js/modal-connexion.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        new ModalConnexion(); // instancie et lance automatiquement
      });
    </script>
  </body>
</html>
