<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
  <div class="logo">
    <a href="/index.php"><img src="<?= BASE_URL ?>/images/logo-ecoride.png" alt="Logo" /></a>
    <span class="title">EcoRide</span>
  </div>

  <button class="menu-toggle" aria-label="Menu">&#9776;</button>

  <?php MenuBuilder::render(); ?>
</header>
