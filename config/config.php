<?php
/**
 * LearnHub Configuration File
 * Database connection and application settings
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/languages.php';

require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';
require_once __DIR__ . '/helpers/formatting.php';
require_once __DIR__ . '/helpers/utility.php';

define('DB_HOST', 'localhost');
define('DB_NAME', 'learnhub');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'LearnHub');
define('SITE_URL', 'http://localhost/learnhub');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

define('DEBUG', false);

$pdo = null;
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
    if (DEBUG) {
        die('Database connection failed: ' . $e->getMessage());
    }
}
?>