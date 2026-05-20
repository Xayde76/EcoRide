<?php
$host    = getenv('DB_HOST') ?: 'db';
$port    = getenv('DB_PORT') ?: '3306';
$db      = getenv('DB_NAME') ?: 'ecoride';
$user    = getenv('DB_USER') ?: 'ecouser';
$pass    = getenv('DB_PASS') ?: 'ecopass';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// SSL requis pour TiDB Cloud (et tout hébergeur distant)
if (getenv('DB_SSL') === 'true') {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $options[PDO::MYSQL_ATTR_SSL_CA]                 = true;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    LoggerService::error('Database connection failed', ['error' => $e->getMessage()]);
    ResponseService::serverError('Database connection failed');
}
