<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$id = $_POST['id'] ?? null;

if (!$id || !ValidationService::validateInteger($id, 1)) {
    ResponseService::validationError('ID de trajet invalide.');
}

try {
    $manager = new CovoiturageManager($pdo, (int)$_SESSION['user_id']);
    $result = $manager->annulerCovoiturage((int)$id);

    if ($result['success']) {
        ResponseService::success(null, $result['message'] ?? 'Trajet annulé.');
    } else {
        ResponseService::error($result['error'] ?? 'Erreur.', 400);
    }
} catch (\Exception $e) {
    LoggerService::error('Trip cancellation error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur lors de l\'annulation.');
}
