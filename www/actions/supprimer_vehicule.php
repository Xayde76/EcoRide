<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/VehiculeManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['vehicule_id'], $_SESSION['user_id'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Requête invalide.']);
  exit;
}

$vehiculeId = (int)$_POST['vehicule_id'];

try {
  $manager = new VehiculeManager($pdo, (int)$_SESSION['user_id']);
  $result = $manager->supprimerVehicule($vehiculeId);
  echo json_encode($result);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}