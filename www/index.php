<?php
session_start(); 
require_once __DIR__ . '/classes/MenuBuilder.php';
require_once __DIR__ . '/includes/constants.php';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoRide</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />
  </head>
  <body>
    <div class="wrapper">
      <?php include __DIR__ . '/partials/menu.php'; ?>

      <main>
        <h1 class="hero-title">
          C’est parti !
          <img src="images/voiture.png" alt="Planète Terre" class="icon" />
        </h1>
        <section class="hero">
          <form class="search-box" method="GET" action="pages/covoiturage.php">
            <input type="text" name="depart" placeholder="Départ"/>
            <input type="text" name="destination" placeholder="Arrivée"/>
            <input type="date" name="date" />
            <button type="submit">Recherche</button>
          </form>
          <div class="carousel">
            <div class="slides">
              <div class="slide">
                <img src="images/paris.jpg" alt="Image 1" />
              </div>
              <div class="slide">
                <img src="images/mont-saint-michel.jpg" alt="Image 2" />
              </div>
              <div class="slide">
                <img src="images/rouen.jpg" alt="Image 3" />
              </div>
            </div>
          </div>
        </section>

        <section class="about">
          <h2>Qui sommes-nous ?</h2>
          <p>
            Lorem ipsum dolor sit amet. Est blanditiis consequatur eos repellat
            exercitationem ea aliquam ipsum...
          </p>
        </section>
      </main>
      <?php include 'partials/footer.php'; ?>
      <?php require_once __DIR__ . '/includes/layout.php'; ?>
    </div>
    <div id="injection-modal"></div>
    <script src="assets/js/modal-connexion.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", () => {
        new ModalConnexion(); // instancie et lance automatiquement
      });
    </script>
    <script src="assets/js/carousel.js"></script>
    <script src="assets/js/menu-toggle.js" defer></script>
  </body>
</html>
