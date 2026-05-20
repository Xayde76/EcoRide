<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Non authentifié.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

if (!isset($_POST['vehicule_id'])) {
    ResponseService::validationError('ID véhicule manquant.');
}

try {
    $manager = new VehiculeManager($pdo, (int)$_SESSION['user_id']);
    $result = $manager->supprimerVehicule((int)$_POST['vehicule_id']);

    if ($result['success']) {
        ResponseService::success(null, $result['message'] ?? 'Véhicule supprimé.');
    } else {
        ResponseService::error($result['error'] ?? 'Erreur.', 400);
    }
} catch (\Exception $e) {
    LoggerService::error('Vehicle deletion error', ['error' => $e->getMessage()]);
    ResponseService::serverError();
}
