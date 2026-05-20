<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$data     = json_decode(file_get_contents('php://input'), true);
$nom      = trim($data['nom'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$userId   = (int)$_SESSION['user_id'];

$errors = [];

if (!ValidationService::validateString($nom, 1, 255)) {
    $errors['nom'] = 'Le nom est requis.';
}
if (!ValidationService::validateEmail($email)) {
    $errors['email'] = 'Email invalide.';
}
if ($password !== '' && !ValidationService::validatePassword($password)) {
    $errors['password'] = 'Le mot de passe doit faire au moins 6 caractères.';
}

if (!empty($errors)) {
    ResponseService::validationError('Données invalides.', $errors);
}

try {
    // Vérifier que l'email n'est pas pris par un autre utilisateur
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
    $check->execute([$email, $userId]);
    if ($check->fetch()) {
        ResponseService::error('Cet email est déjà utilisé par un autre compte.', 400);
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$nom, $email, $hash, $userId]);
    } else {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
        $stmt->execute([$nom, $email, $userId]);
    }

    $_SESSION['user_nom'] = $nom;

    LoggerService::info('Profile updated', ['user_id' => $userId]);
    ResponseService::success(['nom' => $nom, 'email' => $email], 'Profil mis à jour avec succès.');
} catch (\PDOException $e) {
    LoggerService::error('Profile update error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
