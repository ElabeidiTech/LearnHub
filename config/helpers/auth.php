<?php
/**
 * Check if a user is currently logged in
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current logged-in user's data from the database
 * @return array|null User data array or null if not logged in
 */
function getCurrentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'full_name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? ''
    ];
}

/**
 * Check if the current user has a specific role
 * @param string $role The role to check (student, teacher, admin)
 * @return bool True if user has the role, false otherwise
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Require user to be logged in, redirect to login page if not
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Require user to have a specific role, redirect if not authorized
 * @param string $role The required role (student, teacher, admin)
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . SITE_URL);
        exit;
    }
}

/**
 * Require user to be an approved teacher, check for pending/rejected/suspended status
 * Redirects with appropriate flash message if not approved
 */
function requireApprovedTeacher() {
    requireRole('teacher');
    $user = getCurrentUser();
    
    if (isset($user['status'])) {
        if ($user['status'] === 'pending') {
            setFlash('warning', __('teacher_account_pending'));
            redirect('/');
        } elseif ($user['status'] === 'rejected') {
            setFlash('error', __('teacher_application_rejected'));
            redirect('/');
        } elseif ($user['status'] === 'suspended') {
            setFlash('error', __('account_suspended'));
            redirect('/');
        }
    }
}
?>
