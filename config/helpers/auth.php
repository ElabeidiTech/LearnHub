<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

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

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . SITE_URL);
        exit;
    }
}

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
