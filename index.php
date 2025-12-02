<?php
/**
 * Homepage - Landing page for LearnHub
 * Displays hero section, statistics, features, and login modal
 */

// Set page title for browser tab
$pageTitle = 'LearnHub';

// Include header with navigation and common elements
require_once 'includes/header.php';

// Platform statistics to display (could be made dynamic with database queries)
$totalStudents = 12450;
$totalCourses = 850;
$totalTeachers = 320;
?>

<!-- Hero Section: Main banner with title, description, and call-to-action buttons -->
<section class="hero-section text-white">
    <div class="container hero-content">
        <div class="row align-items-center">
            <!-- Left column: Hero text and call-to-action buttons -->
            <div class="col-lg-6 mb-5 mb-lg-0">
                <!-- Main hero title with animation -->
                <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInUp">
                    <?= __('hero_title') ?>
                </h1>
                <!-- Hero subtitle/description -->
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <?= __('hero_subtitle') ?>
                </p>
                <!-- Call-to-action buttons: Different based on user login status and role -->
                <div class="d-flex gap-3 flex-wrap animate__animated animate__fadeInUp animate__delay-2s">
                    <?php if (isLoggedIn()): ?>
                        <!-- Logged in users: Show role-specific action buttons -->
                        <!-- Logged in users: Show role-specific action buttons -->
                        <?php if (hasRole('teacher') || hasRole('admin')): ?>
                            <!-- Teachers/Admins: Show create course button -->
                            <a href="teacher/create-course.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('create_course') ?>
                            </a>
                        <?php else: ?>
                            <!-- Students: Show my courses button -->
                            <a href="student/" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-book-reader <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('my_courses') ?>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Guest users: Show login and register buttons -->
                        <a href="#" class="btn btn-light btn-lg px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                            <i class="fas fa-sign-in-alt <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('login') ?>
                        </a>
                        <a href="auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('register') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Right column: Hero illustration image -->
            <div class="col-lg-6 text-center animate__animated animate__fadeInRight">
                <img src="<?= SITE_URL ?>/assets/images/Learning Management/student-going-to-school.svg" 
                    alt="Learning Management" class="img-fluid" loading="eager" fetchpriority="high">
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section: Platform metrics with animated counters -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <!-- Total Students Counter -->
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalStudents ?>">0</div>
                <p class="text-light mb-0"><?= __('enrolled_students') ?></p>
            </div>
            <!-- Total Courses Counter -->
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalCourses ?>">0</div>
                <p class="text-light mb-0"><?= __('courses_created') ?></p>
            </div>
            <!-- Total Teachers Counter -->
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalTeachers ?>">0</div>
                <p class="text-light mb-0"><?= __('instructors') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section: Key platform features in grid layout -->
<section class="features">
    <h2><?= __('features_title') ?></h2>
    <!-- Feature grid showing 6 key platform capabilities -->
    <div class="feature-grid">
        
        <!-- Feature 1: Assignments Management -->
        <div class="feature-card">
            <div class="icon icon-primary">
                <i class="fas fa-tasks"></i>
            </div>
            <h3><?= __('assignments') ?></h3>
            <p><?= __('assignments_desc') ?></p>
        </div>
        
        <!-- Feature 2: Quiz System -->
        <div class="feature-card">
            <div class="icon icon-info">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3><?= __('quizzes') ?></h3>
            <p><?= __('quizzes_desc') ?></p>
        </div>
        
        <!-- Feature 3: Gradebook -->
        <div class="feature-card">
            <div class="icon icon-success">
                <i class="fas fa-star"></i>
            </div>
            <h3><?= __('gradebook') ?></h3>
            <p><?= __('gradebook_desc') ?></p>
        </div>
        
        <!-- Feature 4: Course Materials -->
        <div class="feature-card">
            <div class="icon icon-warning">
                <i class="fas fa-folder"></i>
            </div>
            <h3><?= __('course_materials') ?></h3>
            <p><?= __('course_materials_desc') ?></p>
        </div>
        
        <!-- Feature 5: Student Management -->
        <div class="feature-card">
            <div class="icon icon-danger">
                <i class="fas fa-users"></i>
            </div>
            <h3><?= __('student_management') ?></h3>
            <p><?= __('student_management_desc') ?></p>
        </div>
        
        <!-- Feature 6: Analytics & Reports -->
        <div class="feature-card">
            <div class="icon icon-purple">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3><?= __('analytics') ?></h3>
            <p><?= __('analytics_desc') ?></p>
        </div>
    </div>
</section>

<!-- Partner Universities Section: Scrolling carousel of university logos -->
<section class="py-4 bg-light overflow-hidden">
    <div class="container mb-3">
        <h5 class="text-center text-muted fw-semibold"><?= __('trusted_by') ?></h5>
    </div>
    <!-- Infinite scrolling logo carousel (JavaScript clones logos for seamless loop) -->
    <div class="university-logos-wrapper">
        <div class="university-logos-track">
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/university_of_toronto.png" alt="University of Toronto" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/McGill_University.png" alt="McGill University" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/University_of_British_Columbia.png" alt="UBC" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/University_of_Alberta.png" alt="University of Alberta" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/McMaster_University.png" alt="McMaster University" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/University_of_Waterloo.png" alt="University of Waterloo" loading="lazy">
            </div>
            <div class="university-logo-item">
                <img src="<?= SITE_URL ?>/assets/images/universities/Ontario Tech University.png" alt="Ontario Tech University" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- Login Modal: Popup form for user authentication (triggered from hero section) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="loginModalLabel">
                    <i class="fas fa-sign-in-alt me-2"></i><?= __('login') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body: Login form -->
            <div class="modal-body">
                <!-- Login form submits to auth/login.php -->
                <form method="POST" action="auth/login.php" id="loginForm">
                    <!-- Email input field -->
                    <div class="mb-3">
                        <label for="email" class="form-label"><?= __('email_address') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="<?= __('enter_email') ?>" required>
                        </div>
                    </div>
                    
                    <!-- Password input field with visibility toggle -->
                    <div class="mb-3">
                        <label for="password" class="form-label"><?= __('password') ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="<?= __('enter_password') ?>" required>
                            <!-- Toggle password visibility button -->
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleModalPassword()">
                                <i class="fas fa-eye" id="toggleModalIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember me checkbox and forgot password link -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                <?= __('remember_me') ?>
                            </label>
                        </div>
                        <a href="auth/forgot-password.php" class="text-decoration-none small"><?= __('forgot_password') ?></a>
                    </div>
                    
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i><?= __('login') ?>
                    </button>
                    
                    <!-- Link to registration page for new users -->
                    <div class="text-center">
                        <span class="text-muted"><?= __('dont_have_account') ?> </span>
                        <a href="auth/register.php" class="text-decoration-none"><?= __('register') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// Include footer with scripts and closing HTML tags
require_once 'includes/footer.php'; 
?>
