<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Non authentifié.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

try {
    $manager = new VehiculeManager($pdo, (int)$_SESSION['user_id']);
    $result = $manager->ajouterVehicule($_POST);

    if ($result['success']) {
        ResponseService::success($result['vehicule'] ?? null, $result['message'] ?? 'Véhicule ajouté.', 201);
    } else {
        if (isset($result['errors'])) {
            ResponseService::validationError($result['error'], $result['errors']);
        }
        ResponseService::error($result['error'] ?? 'Erreur.', 400);
    }
} catch (ValidationException $e) {
    ResponseService::validationError($e->getMessage(), $e->getErrors());
} catch (\Exception $e) {
    LoggerService::error('Vehicle add error', ['error' => $e->getMessage()]);
    ResponseService::serverError();
}
