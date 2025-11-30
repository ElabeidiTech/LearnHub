<?php
$pageTitle = 'Register';
require_once '../config/config.php';

if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_role'] . '/');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['student', 'teacher'])) {
        $error = 'Invalid role selected.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if ($role === 'teacher') {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, 'teacher', 'pending')");
                
                if ($stmt->execute([$fullName, $email, $hashedPassword])) {
                    $_SESSION['flash_info'] = 'Your teacher account has been created and is pending admin approval. You will be notified once approved.';
                    redirect('/auth/login.php');
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, 'approved')");
                
                if ($stmt->execute([$fullName, $email, $hashedPassword, $role])) {
                    $userId = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'full_name' => $fullName,
                        'email' => $email,
                        'role' => $role,
                        'status' => 'approved'
                    ];
                    
                    $_SESSION['flash_success'] = 'Account created successfully!';
                    redirect('/student/');
                } else {
                    $error = 'Something went wrong. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getLanguageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="container">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <a href="<?= SITE_URL ?>" class="logo text-decoration-none text-primary">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="fw-bold"><?= SITE_NAME ?></span>
                    </a>
                    <h4 class="mt-3 mb-1"><?= __('create_account') ?></h4>
                    <p class="text-muted"><?= __('start_learning_journey') ?></p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="full_name" class="form-label"><?= __('full_name') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?= __('email_address') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?= __('password') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   minlength="6" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small id="password-strength" class="form-text"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label"><?= __('confirm_password') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label"><?= __('i_want_to') ?></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role_student" value="student" checked>
                                <label class="btn btn-outline-primary w-100 py-3" for="role_student">
                                    <i class="fas fa-user-graduate d-block mb-2 fs-4"></i>
                                    <?= __('learn') ?>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role_teacher" value="teacher">
                                <label class="btn btn-outline-primary w-100 py-3" for="role_teacher">
                                    <i class="fas fa-chalkboard-teacher d-block mb-2 fs-4"></i>
                                    <?= __('teach') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="terms" required>
                        <label class="form-check-label" for="terms">
                            <?= __('agree_to') ?> <a href="#" class="text-primary"><?= __('terms_of_service') ?></a> 
                            <?= __('and') ?> <a href="#" class="text-primary"><?= __('privacy_policy') ?></a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="fas fa-user-plus me-2"></i><?= __('create_account') ?>
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0"><?= __('already_have_account') ?> 
                            <a href="login.php" class="text-primary fw-semibold"><?= __('sign_in') ?></a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</body>
</html>