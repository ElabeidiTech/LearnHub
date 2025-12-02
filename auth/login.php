<?php
/**
 * Login Page - User authentication
 * Handles user login for students, teachers, and admins
 */

// Set page title
$pageTitle = 'Login';

// Load configuration and helper functions
require_once '../config/config.php';

// Redirect if already logged in based on user role
if (isLoggedIn()) {
    redirect('/' . $_SESSION['user_role'] . '/');
}

// Initialize error message variable
$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate required fields
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Query database for user with provided email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables for authenticated user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'];
            
            // Set welcome flash message
            $_SESSION['flash_success'] = 'Welcome back, ' . $user['full_name'] . '!';
            
            // Redirect to role-specific dashboard
            redirect('/' . $user['role'] . '/');
        } else {
            // Invalid credentials
            $error = 'Invalid email or password.';
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
    <!-- Auth Page Wrapper: Centered login form container -->
    <div class="auth-wrapper">
        <div class="container">
            <div class="auth-card">
                <div class="text-center mb-4">
                    <a href="<?= SITE_URL ?>" class="logo text-decoration-none text-primary">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="fw-bold"><?= SITE_NAME ?></span>
                    </a>
                    <h4 class="mt-3 mb-1"><?= __('welcome_back') ?></h4>
                    <p class="text-muted"><?= __('sign_in_continue') ?></p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
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
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"><?= __('remember_me') ?></label>
                        </div>
                        <a href="forgot-password.php" class="text-primary"><?= __('forgot_password') ?></a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i><?= __('sign_in') ?>
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0"><?= __('dont_have_account') ?> 
                            <a href="register.php" class="text-primary fw-semibold"><?= __('sign_up') ?></a>
                        </p>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="text-muted small mb-2">Demo Accounts:</p>
                    <p class="small mb-1"><strong>Teacher:</strong> teacher@learnhub.com</p>
                    <p class="small mb-0"><strong>Student:</strong> student@learnhub.com</p>
                    <p class="small text-muted">(Password: password)</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>