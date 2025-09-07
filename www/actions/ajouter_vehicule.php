<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/VehiculeManager.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'error' => 'Non authentifié']);
  exit;
}

try {
  $manager = new VehiculeManager($pdo, (int)$_SESSION['user_id']);
  $vehicule = $manager->ajouterVehicule($_POST);
  echo json_encode(['success' => true, 'vehicule' => $vehicule]);
} catch (InvalidArgumentException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
}