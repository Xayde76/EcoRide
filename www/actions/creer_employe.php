<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    ResponseService::forbidden('Accès réservé aux administrateurs.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$data     = json_decode(file_get_contents('php://input'), true);
$nom      = trim($data['nom'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$errors = [];

if (!ValidationService::validateString($nom, 1, 255)) {
    $errors['nom'] = 'Le nom est requis.';
}
if (!ValidationService::validateEmail($email)) {
    $errors['email'] = 'Email invalide.';
}
if (!ValidationService::validatePassword($password)) {
    $errors['password'] = 'Le mot de passe doit faire au moins 6 caractères.';
}

if (!empty($errors)) {
    ResponseService::validationError('Données invalides.', $errors);
}

try {
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        ResponseService::error('Un compte avec cet email existe déjà.', 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, password, role_id, actif) VALUES (?, ?, ?, 2, 1)");
    $stmt->execute([$nom, $email, $hash]);
    $newId = $pdo->lastInsertId();

    LoggerService::info('Employee account created', ['admin_id' => $_SESSION['user_id'], 'new_employee_id' => $newId]);

    ResponseService::success([
        'id'    => $newId,
        'nom'   => $nom,
        'email' => $email,
    ], 'Compte employé créé avec succès.', 201);
} catch (\PDOException $e) {
    LoggerService::error('Create employee error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
