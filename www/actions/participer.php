<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

if (!ValidationService::validateInteger($_POST['covoiturage_id'] ?? 0, 1)) {
    ResponseService::validationError('ID covoiturage invalide.');
}

try {
    $userId = (int)$_SESSION['user_id'];
    $covoiturageId = (int)$_POST['covoiturage_id'];

    $stmt = $pdo->prepare("SELECT nb_place, prix_personne FROM covoiturage WHERE covoiturage_id = ? AND statut = 'disponible'");
    $stmt->execute([$covoiturageId]);
    $voyage = $stmt->fetch();

    if (!$voyage) {
        ResponseService::notFound('Voyage introuvable ou non disponible.');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participation WHERE covoiturage_id = ? AND utilisateur_id = ?");
    $stmt->execute([$covoiturageId, $userId]);
    if ($stmt->fetchColumn() > 0) {
        ResponseService::error('Vous participez déjà à ce voyage.', 400);
    }

    $stmt = $pdo->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $credits = $stmt->fetchColumn();

    if ($credits < $voyage['prix_personne']) {
        ResponseService::error('Crédits insuffisants.', 400);
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO participation (covoiturage_id, utilisateur_id) VALUES (?, ?)");
    $stmt->execute([$covoiturageId, $userId]);

    $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits - ? WHERE id = ?");
    $stmt->execute([$voyage['prix_personne'], $userId]);

    $stmt = $pdo->prepare("UPDATE covoiturage SET nb_place = nb_place - 1 WHERE covoiturage_id = ?");
    $stmt->execute([$covoiturageId]);

    $pdo->commit();

    LoggerService::info('Participation added', ['user_id' => $userId, 'trip_id' => $covoiturageId]);
    ResponseService::success(null, 'Participation enregistrée avec succès.', 201);
} catch (\PDOException $e) {
    $pdo->rollBack();
    LoggerService::error('Participation error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur lors de la participation.');
}
