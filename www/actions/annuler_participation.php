<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../classes/VoyageManager.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!$id || !$userId) {
        echo json_encode(["success" => false, "error" => "Requête invalide."]);
        exit;
    }

    $manager = new VoyageManager($pdo, (int)$userId);
    $result = $manager->annulerParticipation((int)$id);
    echo json_encode($result);
    exit;
}

echo json_encode(["success" => false, "error" => "Méthode non autorisée."]);
exit;