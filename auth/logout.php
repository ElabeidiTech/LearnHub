<?php
require_once '../config/config.php';

/** Destroy user session to log them out */
session_destroy();

/** Redirect to homepage */
header('Location: ' . SITE_URL . '/index.php');
exit;
?>