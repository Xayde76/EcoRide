<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

if (APP_ENV !== 'development' && RateLimitService::isRateLimited('register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 3, 3600)) {
    LoggerService::security('Rate limit exceeded for registration', ['ip' => $_SERVER['REMOTE_ADDR']]);
    ResponseService::error('Trop de registrations. Réessayez plus tard.', 429);
}

$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

try {
    $manager = new UserManager($pdo);
    $response = $manager->register($nom, $email, $password, $confirm);

    if ($response['success']) {
        RateLimitService::resetRateLimit('register_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        ResponseService::success(null, $response['message'] ?? 'Inscription réussie.', 201);
    } else {
        if (isset($response['errors'])) {
            ResponseService::validationError($response['message'], $response['errors']);
        }
        ResponseService::error($response['message'] ?? 'Erreur lors de l\'inscription.', 400);
    }
} catch (\Exception $e) {
    LoggerService::error('Registration error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur lors de l\'inscription.');
}