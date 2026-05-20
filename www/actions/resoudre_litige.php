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

$data           = json_decode(file_get_contents('php://input'), true);
$covoiturageId  = (int)($data['covoiturage_id'] ?? 0);
$passagerId     = (int)($data['passager_id'] ?? 0);
$resolution     = $data['resolution'] ?? '';

if ($covoiturageId <= 0 || $passagerId <= 0 || !in_array($resolution, ['conducteur', 'passager'], true)) {
    ResponseService::validationError('Paramètres invalides.');
}

try {
    $stmt = $pdo->prepare("
        SELECT p.statut AS p_statut, c.utilisateur_id AS conducteur_id, c.prix_personne
        FROM participation p
        JOIN covoiturage c ON p.covoiturage_id = c.covoiturage_id
        WHERE p.covoiturage_id = ? AND p.utilisateur_id = ? AND p.statut = 'litige'
    ");
    $stmt->execute([$covoiturageId, $passagerId]);
    $row = $stmt->fetch();

    if (!$row) {
        ResponseService::notFound('Litige introuvable ou déjà résolu.');
    }

    $pdo->beginTransaction();

    if ($resolution === 'conducteur') {
        // Conducteur non fautif : il reçoit les crédits (prix - 2)
        $creditsDriver = max(0, $row['prix_personne'] - 2);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?");
        $stmt->execute([$creditsDriver, $row['conducteur_id']]);
        $message = 'Litige résolu : crédits transférés au conducteur.';
    } else {
        // Conducteur fautif : le passager est remboursé
        $stmt = $pdo->prepare("UPDATE utilisateurs SET credits = credits + ? WHERE id = ?");
        $stmt->execute([$row['prix_personne'], $passagerId]);
        $message = 'Litige résolu : passager remboursé.';
    }

    $stmt = $pdo->prepare("
        UPDATE participation SET statut = 'validee'
        WHERE covoiturage_id = ? AND utilisateur_id = ?
    ");
    $stmt->execute([$covoiturageId, $passagerId]);

    $pdo->commit();

    LoggerService::info('Dispute resolved', [
        'employee_id'    => $_SESSION['user_id'],
        'covoiturage_id' => $covoiturageId,
        'resolution'     => $resolution,
    ]);

    ResponseService::success(null, $message);
} catch (\PDOException $e) {
    $pdo->rollBack();
    LoggerService::error('Resolve dispute error', ['error' => $e->getMessage()]);
    ResponseService::serverError('Erreur serveur.');
}
