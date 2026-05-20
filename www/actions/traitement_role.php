<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Utilisateur non authentifié.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$role = $_POST['role'] ?? '';
$allowedRoles = ['passager', 'chauffeur', 'chauffeur_passager'];

if (!ValidationService::validateInArray($role, $allowedRoles)) {
    ResponseService::validationError('Rôle invalide.', ['role' => 'Valeur non autorisée.']);
}

try {
    $userId = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM roles_utilisateurs WHERE utilisateur_id = ?");
    $stmt->execute([$userId]);

    if ($stmt->fetchColumn() > 0) {
        $stmt = $pdo->prepare("UPDATE roles_utilisateurs SET role = ? WHERE utilisateur_id = ?");
        $stmt->execute([$role, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO roles_utilisateurs (utilisateur_id, role) VALUES (?, ?)");
        $stmt->execute([$userId, $role]);
    }

    LoggerService::info('User role updated', ['user_id' => $userId, 'role' => $role]);
    ResponseService::success(['role' => $role], 'Rôle mis à jour avec succès.');
} catch (\PDOException $e) {
    LoggerService::error('Role update error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur lors de la mise à jour du rôle.');
}
