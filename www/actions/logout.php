<?php
session_start();
session_unset();
session_destroy();

require_once __DIR__ . '/../includes/constants.php';
header('Location: ' . BASE_URL . '/index.php');
exit();
?>