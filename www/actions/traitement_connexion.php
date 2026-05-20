<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

if (APP_ENV !== 'development' && RateLimitService::isRateLimited('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'))) {
    LoggerService::security('Rate limit exceeded for login', ['ip' => $_SERVER['REMOTE_ADDR']]);
    ResponseService::error('Trop de tentatives. Réessayez dans quelques minutes.', 429);
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!ValidationService::validateEmail($email)) {
    ResponseService::validationError('Email invalide.', ['email' => 'Email invalide']);
}

if (empty($password)) {
    ResponseService::validationError('Mot de passe requis.', ['password' => 'Mot de passe requis']);
}

try {
    $manager = new UserManager($pdo);
    $response = $manager->login($email, $password);

    if ($response['success']) {
        RateLimitService::resetRateLimit('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        ResponseService::success(null, $response['message'] ?? 'Connexion réussie.', 200);
    } else {
        ResponseService::error($response['message'] ?? 'Erreur de connexion.', 401);
    }
} catch (\Exception $e) {
    LoggerService::error('Login error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur lors de la connexion.');
}