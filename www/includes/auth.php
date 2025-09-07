<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_ajax(): bool {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

if (!isset($_SESSION['user_id'])) {
    if (is_ajax()) {
        // Appel AJAX → JSON (ex: pour fetch)
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
        exit;
    } else {
        // Appel normal → redirection
        header("Location: /index.php");
        exit;
    }
}