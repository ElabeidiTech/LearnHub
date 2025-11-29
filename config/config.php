<?php
/**
 * LearnHub Configuration File
 * Database connection and application settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include language configuration
require_once __DIR__ . '/languages.php';

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'learnhub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('SITE_NAME', 'LearnHub');
define('SITE_URL', 'http://localhost/learnhub');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Database Connection (Optional)
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
    // Database not available - continue without database
    $pdo = null;
}

// Helper Functions

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    // Return basic user data from session if no database
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'full_name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? ''
    ];
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

// Require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . SITE_URL);
        exit;
    }
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($path) {
    $url = (strpos($path, 'http') === 0) ? $path : SITE_URL . $path;
    header('Location: ' . $url);
    exit;
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format date
function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

// Format date short
function formatDateShort($date) {
    return date('M d, Y', strtotime($date));
}

// Check if assignment is overdue
function isOverdue($dueDate) {
    return strtotime($dueDate) < time();
}

// Get time remaining
function getTimeRemaining($dueDate) {
    $diff = strtotime($dueDate) - time();
    if ($diff < 0) return 'Overdue';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' hours';
    return floor($diff / 86400) . ' days';
}

// Generate random string
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Get file extension
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Allowed file types
function getAllowedTypes() {
    return ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'];
}

// Max file size (10MB)
function getMaxFileSize() {
    return 10 * 1024 * 1024;
}
?>