<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

try {
    $manager = new VoyageManager($pdo, (int)$_SESSION['user_id']);

    $result = $manager->creerVoyage([
        'vehicule_id' => $_POST['vehicule_id'] ?? '',
        'depart' => $_POST['depart'] ?? '',
        'destination' => $_POST['destination'] ?? '',
        'date_depart' => $_POST['date_depart'] ?? '',
        'prix' => $_POST['prix'] ?? ''
    ]);

    if ($result['success']) {
        ResponseService::success($result['voyage'] ?? null, $result['message'] ?? 'Trajet créé.', 201);
    } else {
        if (isset($result['errors'])) {
            ResponseService::validationError($result['error'], $result['errors']);
        }
        ResponseService::error($result['error'] ?? 'Erreur lors de la création.', 400);
    }
} catch (\Exception $e) {
    LoggerService::error('Trip creation error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur lors de la création du trajet.');
}