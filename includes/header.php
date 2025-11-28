<?php
require_once __DIR__ . '/../config/config.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : SITE_NAME ?></title>
    
    <!-- DNS Prefetch for faster resource loading -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    
    <!-- Preconnect to critical resources -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Critical CSS for faster initial render -->
    <style>
        body{font-family:'Inter',sans-serif;background-color:#f8fafc;color:#334155;line-height:1.6;margin:0}
        .hero-section{background:linear-gradient(135deg,#4f46e5 0%,#06b6d4 100%);padding:80px 0;min-height:500px}
        .navbar{padding:1rem 0;background:#fff!important}
        .stats-section{background:linear-gradient(135deg,#1f2937 0%,#111827 100%);padding:60px 0}
    </style>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= SITE_URL ?>">
                <i class="fas fa-graduation-cap me-2"></i><?= SITE_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/about.php">
                            <i class="fas fa-circle-info me-1"></i>About
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('teacher') || hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/teacher/">
                                    <i class="fas fa-chalkboard-user me-1"></i>Teacher Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (hasRole('student')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= SITE_URL ?>/student/">
                                    <i class="fas fa-book-open-reader me-1"></i>My Learning
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?= sanitize($currentUser['full_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/<?= $currentUser['role'] ?>/">
                                    <i class="fas fa-gauge me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="<?= SITE_URL ?>/<?= $currentUser['role'] ?>/profile.php">
                                    <i class="fas fa-user me-2"></i>Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/auth/logout.php">
                                    <i class="fas fa-right-from-bracket me-2"></i>Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/auth/login.php">
                                <i class="fas fa-right-to-bracket me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="<?= SITE_URL ?>/auth/register.php">
                                Get Started
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <div class="container mt-3">
        <?= flashMessage() ?>
    </div>
    
    <!-- Main Content -->
    <main>