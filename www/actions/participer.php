<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => "Vous devez être connecté pour participer à un covoiturage."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['covoiturage_id'])) {
    $userId = $_SESSION['user_id'];
    $covoiturageId = (int)$_POST['covoiturage_id'];

    // Vérifie que le voyage existe
    $stmt = $pdo->prepare("SELECT nb_place, prix_personne FROM covoiturage WHERE covoiturage_id = ? AND statut = 'disponible'");
    $stmt->execute([$covoiturageId]);
    $voyage = $stmt->fetch();

    if (!$voyage) {
        echo json_encode(['success' => false, 'error' => "Voyage introuvable ou non disponible."]);
        exit;
    }

    // Vérifie si l'utilisateur participe déjà
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE covoiturage_id = ? AND utilisateur_id = ?");
    $stmt->execute([$covoiturageId, $userId]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => "Vous participez déjà à ce voyage."]);
        exit;
    }

    // Vérifie les crédits
    $stmt = $pdo->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $credits = $stmt->fetchColumn();

    if ($credits < $voyage['prix_personne']) {
        echo json_encode(['success' => false, 'error' => "Crédits insuffisants pour participer."]);
        exit;
    }

    // Enregistrement
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO participation (covoiturage_id, utilisateur_id) VALUES (?, ?)");
        $stmt->execute([$covoiturageId, $userId]);

        $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits - ? WHERE id = ?");
        $stmt->execute([$voyage['prix_personne'], $userId]);

        $pdo->commit();

        echo json_encode(['success' => true, 'message' => "Participation enregistrée avec succès."]);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => "Erreur : " . $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => "Requête invalide."]);
exit;