<?php
require_once '../config/config.php';

// Destroy session
session_destroy();

// Redirect to login
header('Location: ' . SITE_URL . '/index.php');
exit;
?>