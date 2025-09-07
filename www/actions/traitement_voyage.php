<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/VoyageManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $manager = new VoyageManager($pdo, (int) $_SESSION['user_id']);

    $result = $manager->creerVoyage([
        'vehicule_id' => $_POST['vehicule_id'],
        'depart' => $_POST['depart'],
        'destination' => $_POST['destination'],
        'date_depart' => $_POST['date_depart'],
        'prix' => $_POST['prix']
    ]);

    echo json_encode($result);
    exit;
}

echo json_encode(["success" => false, "error" => "Requête invalide."]);
exit;