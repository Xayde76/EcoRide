<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../classes/UserManager.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$manager = new UserManager($pdo);
$response = $manager->login($email, $password);

echo json_encode($response);