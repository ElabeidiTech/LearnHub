<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'My Profile';
$user = getCurrentUser();
$error = '';
$success = '';

/** Handle profile management POST requests */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /** Process profile information update */
    if (isset($_POST['update_profile'])) {
        /** Extract and sanitize profile data */
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        /** Validate required fields and email format */
        if (empty($fullName) || empty($email)) {
            $error = __('name_email_required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = __('invalid_email');
        } else {
            /** Check if email is taken by another user */
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            
            if ($stmt->fetch()) {
                $error = __('email_taken');
            } else {
                /** Update user profile in database */
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
                if ($stmt->execute([$fullName, $email, $phone, $bio, $user['id']])) {
                    /** Update session with new profile data */
                    $_SESSION['user']['full_name'] = $fullName;
                    $_SESSION['user']['email'] = $email;
                    setFlash('success', __('profile_updated'));
                    redirect('/pages/profile.php');
                } else {
                    $error = __('update_failed');
                }
            }
        }
    }
    
    /** Process password change request */
    if (isset($_POST['change_password'])) {
        /** Extract password fields */
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        /** Validate password requirements */
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = __('all_fields_required');
        } elseif ($newPassword !== $confirmPassword) {
            $error = __('passwords_not_match');
        } elseif (strlen($newPassword) < 6) {
            $error = __('password_min_length');
        } else {
            /** Retrieve current password hash for verification */
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();
            
            /** Verify current password is correct */
            if (!password_verify($currentPassword, $userData['password'])) {
                $error = __('current_password_incorrect');
            } else {
                /** Hash new password and update in database */
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashedPassword, $user['id']])) {
                    setFlash('success', __('password_changed'));
                    redirect('/pages/profile.php');
                } else {
                    $error = __('update_failed');
                }
            }
        }
    }
    
    /** Process profile picture upload */
    if (isset($_POST['upload_picture'])) {
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            /** Validate image format and size (max 5MB) */
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = __('invalid_image_format');
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = __('image_too_large');
            } else {
                /** Ensure upload directory exists */
                $uploadDir = UPLOAD_PATH . 'profiles/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                /** Delete old profile picture if exists */
                if (!empty($user['profile_picture'])) {
                    $oldPath = UPLOAD_PATH . $user['profile_picture'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                
                /** Generate unique filename and save uploaded file */
                $fileName = $user['id'] . '_' . time() . '.' . $ext;
                $filePath = 'profiles/' . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filePath)) {
                    /** Update user's profile picture path in database */
                    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    
                    if ($stmt->execute([$filePath, $user['id']])) {
                        $_SESSION['user']['profile_picture'] = $filePath;
                        setFlash('success', __('profile_picture_updated'));
                        redirect('/pages/profile.php');
                    } else {
                        $error = __('update_failed');
                    }
                } else {
                    $error = __('upload_failed');
                }
            }
        } else {
            $error = __('no_file_selected');
        }
    }
    
    /** Process profile picture deletion request */
    if (isset($_POST['delete_picture'])) {
        if (!empty($user['profile_picture'])) {
            /** Delete physical file from server storage */
            $oldPath = UPLOAD_PATH . $user['profile_picture'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            
            /** Remove profile picture path from database */
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
            if ($stmt->execute([$user['id']])) {
                unset($_SESSION['user']['profile_picture']);
                setFlash('success', 'Profile picture deleted successfully.');
                redirect('/pages/profile.php');
            } else {
                $error = __('update_failed');
            }
        }
    }
}

/** Retrieve latest user data from database */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-user-circle text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('my_profile') ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
            <?= sanitize($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if (!empty($userData['profile_picture'])): ?>
                            <img src="<?= SITE_URL ?>/uploads/<?= sanitize($userData['profile_picture']) ?>" 
                                 class="rounded-circle border border-3 border-primary" 
                                 class="rounded-circle avatar-xl" 
                                 alt="<?= sanitize($userData['full_name']) ?>">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center border border-3 border-primary" 
                                 class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center avatar-xl">
                                <?= strtoupper(substr($userData['full_name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4><?= sanitize($userData['full_name']) ?></h4>
                    <p class="text-muted mb-2"><?= sanitize($userData['email']) ?></p>
                    <span class="badge bg-<?= $userData['role'] === 'teacher' ? 'success' : 'primary' ?> mb-3">
                        <i class="fas fa-<?= $userData['role'] === 'teacher' ? 'chalkboard-teacher' : 'user-graduate' ?> <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                        <?= ucfirst($userData['role']) ?>
                    </span>
                    
                    <?php if ($userData['student_id']): ?>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-id-card <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <?= __('student_id') ?>: <?= sanitize($userData['student_id']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="text-muted small">
                        <i class="fas fa-calendar <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                        <?= __('member_since') ?> <?= formatDate($userData['created_at']) ?>
                    </p>
                    
                    <hr>
                    
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="btn btn-outline-primary w-100">
                                <i class="fas fa-camera <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                <?= __('change_picture') ?>
                                <input type="file" name="profile_picture" class="d-none" accept="image/*" id="profilePictureInput">
                            </label>
                        </div>
                        <button type="submit" name="upload_picture" class="btn btn-primary w-100 d-none" id="uploadBtn">
                            <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <?= __('upload') ?>
                        </button>
                    </form>
                    
                    <?php if (!empty($userData['profile_picture'])): ?>
                    <form method="POST" class="mt-2">
                        <button type="submit" name="delete_picture" class="btn btn-outline-danger w-100 btn-sm"
                                onclick="return confirm('Are you sure you want to delete your profile picture?')">
                            <i class="fas fa-trash <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            Delete Picture
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-8">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                        <?= __('personal_information') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label"><?= __('full_name') ?> *</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?= sanitize($userData['full_name']) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label"><?= __('email') ?> *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= sanitize($userData['email']) ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label"><?= __('phone') ?></label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?= sanitize($userData['phone'] ?? '') ?>" 
                                       placeholder="+1 234 567 8900">
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label"><?= __('bio') ?></label>
                                <textarea name="bio" class="form-control" rows="3" 
                                          placeholder="<?= __('tell_about_yourself') ?>"><?= sanitize($userData['bio'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                <?= __('save_changes') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-lock text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                        <?= __('change_password') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label"><?= __('current_password') ?> *</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label"><?= __('new_password') ?> *</label>
                                <input type="password" name="new_password" class="form-control" 
                                       minlength="6" required>
                                <small class="text-muted"><?= __('min_6_characters') ?></small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label"><?= __('confirm_password') ?> *</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       minlength="6" required>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" name="change_password" class="btn btn-warning">
                                <i class="fas fa-key <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                <?= __('update_password') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
