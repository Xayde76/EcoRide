<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    ResponseService::forbidden('Accès réservé aux administrateurs.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || !ValidationService::validateInteger($input['user_id'], 1)) {
    ResponseService::validationError('ID utilisateur invalide.');
}

$targetId = (int)$input['user_id'];
$adminId = (int)$_SESSION['user_id'];

if ($targetId === $adminId) {
    ResponseService::error('Vous ne pouvez pas modifier votre propre compte.', 400);
}

try {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
    $stmt->execute([$targetId]);

    $stmt = $pdo->prepare("SELECT actif FROM utilisateurs WHERE id = ?");
    $stmt->execute([$targetId]);
    $newStatus = $stmt->fetchColumn();

    if ($newStatus === false) {
        ResponseService::notFound('Utilisateur introuvable.');
    }

    LoggerService::security('User status toggled', ['admin_id' => $adminId, 'target_user_id' => $targetId, 'new_status' => $newStatus]);

    ResponseService::success(
        ['new_status' => $newStatus ? 'Actif' : 'Suspendu'],
        'Statut mis à jour.'
    );
} catch (\PDOException $e) {
    LoggerService::error('Toggle user error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur lors de la modification.');
}
