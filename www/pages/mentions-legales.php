<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mentions Légales | EcoRide</title>
    <meta name="description" content="Mentions légales d'EcoRide : informations sur l'éditeur, l'hébergeur, la propriété intellectuelle et la protection des données personnelles (RGPD).">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="https://www.ecoride.fr/pages/mentions-legales.php">
    <link rel="stylesheet" href="../assets/css/styles.css" />
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
  </head>
  <body>
    <?php include __DIR__ . '/../partials/menu.php'; ?>

    <main class="wrapper user-page">

      <div class="user-banner">
        <div class="user-banner-identity">
          <div class="user-avatar">📄</div>
          <div>
            <h1>Mentions Légales</h1>
            <p>Informations légales relatives au site EcoRide</p>
          </div>
        </div>
      </div>

      <div class="mentions-content">

        <h2>Éditeur du site</h2>
        <p><strong>Nom :</strong> EcoRide</p>
        <p><strong>Adresse :</strong> 123 rue des Transports, 75000 Paris, France</p>
        <p><strong>Email :</strong> contact.ecoride76@gmail.com</p>
        <p><strong>Directeur de publication :</strong> José Leclerc</p>

        <h2>Hébergement</h2>
        <p><strong>Nom de l'hébergeur :</strong> Infomaniak / OVH / 1&1 (au choix)</p>
        <p><strong>Adresse :</strong> 2 rue Kellermann, 59100 Roubaix, France</p>
        <p><strong>Téléphone :</strong> 1007</p>

        <h2>Propriété intellectuelle</h2>
        <p>
          Le site EcoRide ainsi que tous ses contenus (textes, images, logo, etc.) sont la propriété
          exclusive d'EcoRide, sauf mention contraire. Toute reproduction, représentation, diffusion
          ou exploitation partielle ou totale est interdite sans autorisation préalable.
        </p>

        <h2>Protection des données personnelles</h2>
        <p>
          Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un
          droit d'accès, de rectification, de suppression et d'opposition aux données personnelles vous
          concernant. Pour exercer ce droit, veuillez nous contacter à :
          <strong>contact.ecoride76@gmail.com</strong>
        </p>

        <h2>Cookies</h2>
        <p>
          Le site EcoRide peut utiliser des cookies à des fins de statistiques ou d'amélioration de
          l'expérience utilisateur. Vous pouvez les refuser via les paramètres de votre navigateur.
        </p>
      </main>

      </div><!-- /mentions-content -->
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
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
