<?php
require_once __DIR__ . '/../config/config.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLanguage() ?>" dir="<?= getLanguageDirection() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : SITE_NAME ?></title>
    
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
    
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= SITE_URL ?>">
                <i class="fas fa-graduation-cap <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= SITE_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav <?= getLanguageDirection() === 'rtl' ? 'me-auto' : 'ms-auto' ?> align-items-center">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-bs-toggle="dropdown">
                            <?= AVAILABLE_LANGUAGES[getCurrentLanguage()]['flag'] ?> <?= AVAILABLE_LANGUAGES[getCurrentLanguage()]['name'] ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach (AVAILABLE_LANGUAGES as $code => $lang): ?>
                                <li>
                                    <a class="dropdown-item <?= getCurrentLanguage() === $code ? 'active' : '' ?>" 
                                       href="?lang=<?= $code ?>">
                                        <?= $lang['flag'] ?> <?= $lang['name'] ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/admin/index.php">
                                    <i class="fas fa-user-shield <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (hasRole('teacher') && !hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/teacher/index.php">
                                    <i class="fas fa-gauge <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('dashboard') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (hasRole('student')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/student/index.php">
                                    <i class="fas fa-book-open-reader <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('my_learning') ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if (!empty($currentUser['profile_picture'])): ?>
                                    <img src="<?= SITE_URL ?>/uploads/<?= sanitize($currentUser['profile_picture']) ?>" 
                                         class="rounded-circle" 
                                         class="rounded-circle avatar-sm" 
                                         alt="<?= sanitize($currentUser['full_name']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                                <span><?= sanitize($currentUser['full_name']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/pages/profile.php">
                                    <i class="fas fa-user <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('profile') ?>
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/auth/logout.php">
                                    <i class="fas fa-right-from-bracket <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('logout') ?>
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-right-to-bracket <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('login') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary <?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>" href="<?= SITE_URL ?>/auth/register.php">
                                <?= __('get_started') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-3">
        <?= flashMessage() ?>
    </div>
    
    <main>