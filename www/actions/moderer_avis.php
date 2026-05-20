<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

$roleId = $_SESSION['role_id'] ?? null;
if (!isset($_SESSION['user_id']) || !in_array($roleId, [1, 2], true)) {
    ResponseService::forbidden('Accès réservé aux employés.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$data   = json_decode(file_get_contents('php://input'), true);
$avisId = (int)($data['avis_id'] ?? 0);
$action = $data['action'] ?? '';

if ($avisId <= 0 || !in_array($action, ['publier', 'rejeter'], true)) {
    ResponseService::validationError('Paramètres invalides.');
}

$statut = $action === 'publier' ? 'publié' : 'rejeté';

try {
    $stmt = $pdo->prepare("UPDATE avis SET statut = ? WHERE avis_id = ? AND statut = 'en_attente'");
    $stmt->execute([$statut, $avisId]);

    if ($stmt->rowCount() === 0) {
        ResponseService::error('Avis introuvable ou déjà modéré.', 404);
    }

    LoggerService::info('Review moderated', [
        'employee_id' => $_SESSION['user_id'],
        'avis_id'     => $avisId,
        'action'      => $action,
    ]);

    ResponseService::success(['statut' => $statut], ucfirst($action === 'publier' ? 'Avis publié.' : 'Avis rejeté.'));
} catch (\PDOException $e) {
    LoggerService::error('Moderate review error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
