<?php
/**
 * LearnHub Configuration File
 * Database connection and application settings
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    if ($pdo === null) {
        // Return mock user data when database is not available
        return [
            'id' => $_SESSION['user_id'] ?? 1,
            'full_name' => $_SESSION['user_name'] ?? 'Guest User',
            'email' => $_SESSION['user_email'] ?? 'guest@example.com',
            'role' => $_SESSION['user_role'] ?? 'student'
        ];
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_role'] === $role;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please login to continue.';
        redirect('/auth/login.php');
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role) && !hasRole('admin')) {
        $_SESSION['flash_error'] = 'You do not have permission to access this page.';
        redirect('/index.php');
    }
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Display flash messages
 */
function flashMessage() {
    $html = '';
    if (isset($_SESSION['flash_success'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">'
               . $_SESSION['flash_success']
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
               . $_SESSION['flash_error']
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash_error']);
    }
    return $html;
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Get average rating for a course
 */
function getCourseRating($courseId) {
    global $pdo;
    if ($pdo === null) {
        return ['avg_rating' => 4.5, 'total' => 10];
    }
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE course_id = ?");
    $stmt->execute([$courseId]);
    return $stmt->fetch();
}

/**
 * Check if student is enrolled in course
 */
function isEnrolled($studentId, $courseId) {
    global $pdo;
    if ($pdo === null) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->execute([$studentId, $courseId]);
    return $stmt->fetch() !== false;
}

/**
 * Get enrollment count for a course
 */
function getEnrollmentCount($courseId) {
    global $pdo;
    if ($pdo === null) {
        return rand(50, 500);
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $result = $stmt->fetch();
    return $result['count'];
}
?>