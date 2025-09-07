<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../classes/MenuBuilder.php';
?>

<header>
  <div class="logo">
    <img src="<?= BASE_URL ?>/images/logo-ecoride.png" alt="Logo" />
    <span class="title">EcoRide</span>
  </div>

  <button class="menu-toggle" aria-label="Menu">&#9776;</button>

  <?php MenuBuilder::render(); ?>
</header>