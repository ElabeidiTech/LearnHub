<?php
$pageTitle = 'Forgot Password';
require_once '../config/config.php';

$error = '';
$success = '';

/** Handle password reset request form submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /** Extract and sanitize email address */
    $email = sanitize($_POST['email'] ?? '');
    
    /** Validate email format */
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        /** Check if user account exists with this email */
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            /** Generate secure random token for password reset */
            $token = bin2hex(random_bytes(32));
            /** Set token expiration to 1 hour from now */
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            /** Store password reset token in database */
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires]);
            
            /** Generic success message to prevent email enumeration attacks */
            $success = 'If an account with that email exists, we have sent password reset instructions.';
        } else {
            /** Same message even if email not found (security best practice) */
            $success = 'If an account with that email exists, we have sent password reset instructions.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Auth Page Wrapper: Centered password reset form -->
    <div class="auth-wrapper">
        <div class="container">
            <!-- Password Reset Card -->
            <div class="auth-card animate__animated animate__fadeIn">
                <div class="text-center mb-4">
                    <a href="<?= SITE_URL ?>" class="logo text-decoration-none">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="fw-bold"><?= SITE_NAME ?></span>
                    </a>
                    <h4 class="mt-3 mb-1">Forgot Password?</h4>
                    <p class="text-muted">Enter your email to reset your password</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="login.php" class="text-muted">
                        <i class="fas fa-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>