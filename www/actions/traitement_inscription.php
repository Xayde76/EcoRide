<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/constants.php';
require_once __DIR__ . '/../classes/UserManager.php';

header('Content-Type: application/json');

$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

$manager = new UserManager($pdo);
$response = $manager->register($nom, $email, $password, $confirm);

echo json_encode($response);