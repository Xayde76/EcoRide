<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    ResponseService::unauthorized('Vous devez être connecté.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ResponseService::error('Method not allowed', 405);
}

$userId = (int)$_SESSION['user_id'];

if (empty($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    ResponseService::validationError('Aucun fichier reçu.');
}

$file     = $_FILES['photo'];
$maxSize  = 2 * 1024 * 1024; // 2 Mo
$allowed  = ['image/jpeg', 'image/png', 'image/webp'];

if ($file['size'] > $maxSize) {
    ResponseService::validationError('Le fichier ne doit pas dépasser 2 Mo.');
}

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, $allowed, true)) {
    ResponseService::validationError('Format accepté : JPG, PNG, WebP.');
}

$ext      = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$destDir  = __DIR__ . '/../images/profil/';
$destPath = $destDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    ResponseService::serverError('Impossible de sauvegarder le fichier.');
}

// Supprimer l'ancienne photo
$old = $pdo->prepare("SELECT photo FROM utilisateurs WHERE id = ?");
$old->execute([$userId]);
$oldPhoto = $old->fetchColumn();
if ($oldPhoto && file_exists($destDir . basename($oldPhoto))) {
    unlink($destDir . basename($oldPhoto));
}

$pdo->prepare("UPDATE utilisateurs SET photo = ? WHERE id = ?")->execute([$filename, $userId]);

LoggerService::info('Profile photo updated', ['user_id' => $userId]);
ResponseService::success(['photo' => $filename], 'Photo mise à jour.');
