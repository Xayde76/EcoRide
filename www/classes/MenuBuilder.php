<?php
require_once __DIR__ . '/../includes/constants.php';

class MenuBuilder {
  public static function getMenuLinks(): array {
    $base = BASE_URL;
    $links = [
      ["label" => "Accueil", "href" => $base . "/index.php"],
      ["label" => "Covoiturage", "href" => $base . "/pages/covoiturage.php"],
      ["label" => "Contact", "href" => "/pages/contact.php"]
    ];

    if (isset($_SESSION['user_id'])) {
      $roleId = $_SESSION['role_id'] ?? null;

      $links[] = ["label" => "Profil", "href" => $base . "/pages/user.php"];

      if ($roleId === 1) {
        $links[] = ["label" => "Admin", "href" => $base . "/pages/admin.php"];
      }

      $links[] = ["label" => "Déconnexion", "href" => $base . "/actions/logout.php", "class" => "btn-logout"];
    } else {
      $links[] = ["label" => "Connexion", "href" => "#", "class" => "btn-login"];
    }

    return $links;
  }

  public static function render(): void {
    echo '<nav class="nav-links">';
    foreach (self::getMenuLinks() as $link) {
      $label = htmlspecialchars($link['label']);
      $href = htmlspecialchars($link['href']);
      $class = $link['class'] ?? '';
      $active = ($_SERVER['SCRIPT_NAME'] === parse_url($href, PHP_URL_PATH)) ? 'active' : '';
      echo "<a href=\"$href\" class=\"$class $active\">$label</a>";
    }
    echo '</nav>';
  }
}
?>