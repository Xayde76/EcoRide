<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non connecté']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'ID manquant']);
        exit;
    }

    $userId = (int)$input['user_id'];

    // Empêcher l'utilisateur de se suspendre lui-même
    if ($userId === $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Action interdite']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
    $stmt->execute([$userId]);

    // Récupération du nouveau statut
    $stmt = $pdo->prepare("SELECT actif FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $newStatus = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'new_status' => $newStatus ? 'Actif' : 'Suspendu'
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
exit;
?>