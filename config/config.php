<?php
/**
 * LearnHub Configuration File
 * Database connection and application settings
 */

/** Start PHP session if not already started for user authentication and state management */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Load multi-language translation system */
require_once __DIR__ . '/languages.php';

/** Load helper function libraries for authentication, validation, formatting, and utilities */
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/validation.php';
require_once __DIR__ . '/helpers/formatting.php';
require_once __DIR__ . '/helpers/utility.php';

/** Database connection credentials for MySQL/MariaDB */
define('DB_HOST', 'localhost');
define('DB_NAME', 'learnhub');
define('DB_USER', 'root');
define('DB_PASS', '');

/** Application-wide constants for site name, URL, and upload directory */
define('SITE_NAME', 'LearnHub');
define('SITE_URL', 'http://localhost/learnhub');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

/** Enable detailed error messages for development (set to false in production) */
define('DEBUG', false);

/** Initialize PDO database connection with error handling */
$pdo = null;
try {
    /** Create PDO instance with UTF-8 charset and prepared statement settings */
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
    /** Set PDO to null on connection failure */
    $pdo = null;
    /** Display error message only in debug mode for security */
    if (DEBUG) {
        die('Database connection failed: ' . $e->getMessage());
    }
}
?>