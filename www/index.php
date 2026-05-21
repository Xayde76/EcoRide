<?php
session_start();
require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EcoRide – Covoiturage écologique en France</title>
    <meta name="description" content="EcoRide, la plateforme de covoiturage écologique. Trouvez ou proposez un trajet partout en France et réduisez votre empreinte carbone.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://ecoride-production-2313.up.railway.app/">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ecoride-production-2313.up.railway.app/">
    <meta property="og:title" content="EcoRide – Covoiturage écologique en France">
    <meta property="og:description" content="Trouvez ou proposez un trajet partout en France. Voyagez moins cher, plus vert, ensemble.">
    <meta property="og:image" content="https://ecoride-production-2313.up.railway.app/images/logo-ecoride.png">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="EcoRide – Covoiturage écologique en France">
    <meta name="twitter:description" content="Trouvez ou proposez un trajet partout en France. Voyagez moins cher, plus vert, ensemble.">
    <meta name="twitter:image" content="https://ecoride-production-2313.up.railway.app/images/logo-ecoride.png">

    <link rel="stylesheet" href="assets/css/styles.css" />
    <link rel="icon" href="data:,">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet" />

    <!-- Schema.org – Site web + Action de recherche -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "name": "EcoRide",
          "url": "https://ecoride-production-2313.up.railway.app",
          "logo": "https://ecoride-production-2313.up.railway.app/images/logo-ecoride.png",
          "contactPoint": {
            "@type": "ContactPoint",
            "email": "contact@ecoride.fr",
            "contactType": "customer service",
            "availableLanguage": "French"
          }
        },
        {
          "@type": "WebSite",
          "name": "EcoRide",
          "url": "https://ecoride-production-2313.up.railway.app",
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "https://ecoride-production-2313.up.railway.app/pages/covoiturage.php?depart={depart}&destination={destination}&date={date}"
            },
            "query-input": "required name=depart required name=destination required name=date"
          }
        }
      ]
    }
    </script>
  </head>
  <body>
    <div class="wrapper">
      <?php include __DIR__ . '/partials/menu.php'; ?>

      <main>
        <h1 class="hero-title">
          C'est parti !
          <img src="images/voiture.png" alt="Voiture EcoRide – covoiturage écologique" class="icon" />
        </h1>
        <section class="hero">
          <form class="search-box" method="GET" action="pages/covoiturage.php" role="search" aria-label="Rechercher un covoiturage">
            <input type="text" name="depart" placeholder="Départ" aria-label="Ville de départ"/>
            <input type="text" name="destination" placeholder="Arrivée" aria-label="Ville d'arrivée"/>
            <input type="date" name="date" aria-label="Date du trajet"/>
            <button type="submit">Recherche</button>
          </form>
          <div class="carousel">
            <div class="slides">
              <div class="slide">
                <img src="images/paris.jpg" alt="Covoiturage Paris – trajet disponible sur EcoRide" />
              </div>
              <div class="slide">
                <img src="images/mont-saint-michel.jpg" alt="Covoiturage Mont-Saint-Michel – trajet disponible sur EcoRide" />
              </div>
              <div class="slide">
                <img src="images/rouen.jpg" alt="Covoiturage Rouen – trajet disponible sur EcoRide" />
              </div>
            </div>
          </div>
        </section>

        <section class="about">
          <h2>Qui sommes-nous ?</h2>
          <p>
            EcoRide est une plateforme de covoiturage engagée pour une mobilité plus verte et plus solidaire.
            Nous mettons en relation des conducteurs et des passagers pour partager les trajets du quotidien,
            réduire les émissions de CO₂ et faire des économies. Rejoignez notre communauté et voyagez
            autrement, partout en France.
          </p>
        </section>
      </main>
      <?php include __DIR__ . '/partials/footer.php'; ?>
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
