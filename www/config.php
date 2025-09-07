<?php
$host = 'db'; // DOCKER HOSTNAME = nom du service MySQL
$db = 'ecoride';
$user = 'ecouser';
$pass = 'ecopass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Affiche l'erreur dans la console JS
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur PDO : " . $e->getMessage()
    ]);
    exit;
}

