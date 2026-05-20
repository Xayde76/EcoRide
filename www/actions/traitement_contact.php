<?php
session_start();
require_once __DIR__ . '/../bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/contact.php');
    exit;
}

$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

$contact = new ContactManager($nom, $email, $message);

if (!$contact->isValid()) {
    header('Location: ' . BASE_URL . '/pages/contact.php?error=invalid');
    exit;
}

if ($contact->send()) {
    header('Location: ' . BASE_URL . '/pages/contact.php?success=1');
} else {
    header('Location: ' . BASE_URL . '/pages/contact.php?error=send');
}
exit;
