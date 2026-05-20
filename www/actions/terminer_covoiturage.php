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
$userId = (int)$_SESSION['user_id'];

if ($covoiturageId <= 0) {
    ResponseService::validationError('ID invalide.');
}

try {
    $stmt = $pdo->prepare("
        UPDATE covoiturage
        SET statut = 'termine'
        WHERE covoiturage_id = ? AND utilisateur_id = ? AND statut = 'en_cours'
    ");
    $stmt->execute([$covoiturageId, $userId]);

    if ($stmt->rowCount() === 0) {
        ResponseService::error('Impossible de terminer ce trajet.', 400);
    }

    MongoStatsService::recordTripCompleted();
    LoggerService::info('Trip ended', ['user_id' => $userId, 'trip_id' => $covoiturageId]);
    ResponseService::success(null, 'Arrivée enregistrée. Les passagers peuvent maintenant valider le trajet.');
} catch (\PDOException $e) {
    LoggerService::error('End trip error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
