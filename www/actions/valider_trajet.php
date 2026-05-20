<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$covoiturageId = (int)($data['covoiturage_id'] ?? 0);
$note          = isset($data['note']) ? (int)$data['note'] : null;
$commentaire   = trim($data['commentaire'] ?? '');
$userId        = (int)$_SESSION['user_id'];

if ($covoiturageId <= 0) {
    ResponseService::validationError('ID invalide.');
}

try {
    $stmt = $pdo->prepare("
        SELECT p.statut AS participation_statut,
               c.prix_personne,
               c.utilisateur_id AS conducteur_id,
               c.statut         AS trip_statut
        FROM participation p
        JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
        WHERE p.covoiturage_id = ? AND p.utilisateur_id = ?
    ");
    $stmt->execute([$covoiturageId, $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        ResponseService::notFound('Participation introuvable.');
    }
    if ($row['trip_statut'] !== 'termine') {
        ResponseService::error('Le trajet n\'est pas encore terminé.', 400);
    }
    if ($row['participation_statut'] !== 'en_attente') {
        ResponseService::error('Vous avez déjà évalué ce trajet.', 400);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE participation SET statut = 'validee'
        WHERE covoiturage_id = ? AND utilisateur_id = ?
    ");
    $stmt->execute([$covoiturageId, $userId]);

    // Plateforme prend 2 crédits ; le reste va au conducteur
    $creditsDriver = max(0, $row['prix_personne'] - 2);
    $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?");
    $stmt->execute([$creditsDriver, $row['conducteur_id']]);

    // Avis optionnel → en attente de validation employé
    if ($note !== null && $note >= 1 && $note <= 5) {
        $auteur = $_SESSION['user_nom'] ?? 'Anonyme';
        $stmt = $pdo->prepare("
            INSERT INTO avis (covoiturage_id, utilisateur_id, auteur, commentaire, note, statut)
            VALUES (?, ?, ?, ?, ?, 'en_attente')
        ");
        $stmt->execute([$covoiturageId, $userId, $auteur, $commentaire ?: null, $note]);
    }

    $pdo->commit();

    MongoStatsService::recordCreditsEarned(2);
    LoggerService::info('Trip validated by passenger', ['user_id' => $userId, 'trip_id' => $covoiturageId]);
    ResponseService::success(null, 'Trajet validé. Merci !');
} catch (\PDOException $e) {
    $pdo->rollBack();
    LoggerService::error('Validation error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
