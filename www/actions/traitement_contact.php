<?php
require_once __DIR__ . '/../classes/ContactManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = new ContactManager($_POST['nom'], $_POST['email'], $_POST['message']);

    if ($contact->isValid()) {
        if ($contact->send()) {
            header('Location: ../pages/contact.php?success=1');
            exit;
        } else {
            header('Location: ../pages/contact.php?error=send');
            exit;
        }
    } else {
        header('Location: ../pages/contact.php?error=invalid');
        exit;
    }
} else {
    header('Location: ../pages/contact.php');
    exit;
}