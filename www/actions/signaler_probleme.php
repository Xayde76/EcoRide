<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$data          = json_decode(file_get_contents('php://input'), true);
$covoiturageId = (int)($data['covoiturage_id'] ?? 0);
$commentaire   = trim($data['commentaire'] ?? '');
$userId        = (int)$_SESSION['user_id'];

if ($covoiturageId <= 0 || empty($commentaire)) {
    ResponseService::validationError('Veuillez décrire le problème rencontré.');
}

try {
    $stmt = $pdo->prepare("
        SELECT p.statut AS participation_statut, c.statut AS trip_statut
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

    $stmt = $pdo->prepare("
        UPDATE participation SET statut = 'litige', commentaire_litige = ?
        WHERE covoiturage_id = ? AND utilisateur_id = ?
    ");
    $stmt->execute([$commentaire, $covoiturageId, $userId]);

    LoggerService::info('Trip problem reported', ['user_id' => $userId, 'trip_id' => $covoiturageId]);
    ResponseService::success(null, 'Votre signalement a été enregistré. Un employé traitera votre demande.');
} catch (\PDOException $e) {
    LoggerService::error('Problem report error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
