<?php
if (!defined('BASE_URL')) {
    $envUrl = getenv('BASE_URL');
    if ($envUrl) {
        define('BASE_URL', $envUrl);
    } else {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('BASE_URL', $scheme . '://' . $host);
    }
}
define('APP_ENV', getenv('APP_ENV') ?: 'development');
