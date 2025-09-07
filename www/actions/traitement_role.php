<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/RoleManager.php';

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? null;
$role = $_POST['role'] ?? '';

if (!$userId) {
    echo json_encode(['success' => false, 'error' => "Utilisateur non authentifié."]);
    exit;
}

$manager = new RoleManager($pdo);
$response = $manager->updateRole((int)$userId, $role);
echo json_encode($response);