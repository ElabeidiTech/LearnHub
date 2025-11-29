<?php
$pageTitle = 'LearnHub';
require_once 'includes/header.php';

// Stats for display
$totalStudents = 12450;
$totalCourses = 850;
$totalTeachers = 320;
?>

<!-- Hero Section -->
<section class="hero-section text-white">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4 animate__animated animate__fadeInUp">
                    <?= __('hero_title') ?>
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    <?= __('hero_subtitle') ?>
                </p>
                <div class="d-flex gap-3 flex-wrap animate__animated animate__fadeInUp animate__delay-2s">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('teacher') || hasRole('admin')): ?>
                            <a href="teacher/create-course.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('create_course') ?>
                            </a>
                        <?php else: ?>
                            <a href="student/" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-book-reader <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('my_courses') ?>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('login') ?>
                        </a>
                        <a href="auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('register') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center animate__animated animate__fadeInRight">
                <img src="<?= SITE_URL ?>/assets/images/Learning Management/student-going-to-school.svg" 
                    alt="Learning Management" class="img-fluid" style="max-height: 400px;" loading="eager" fetchpriority="high">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalStudents ?>">0</div>
                <p class="text-light mb-0"><?= __('enrolled_students') ?></p>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalCourses ?>">0</div>
                <p class="text-light mb-0"><?= __('courses_created') ?></p>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-number" data-target="<?= $totalTeachers ?>">0</div>
                <p class="text-light mb-0"><?= __('instructors') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <h2><?= __('features_title') ?></h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="icon icon-primary">
                <i class="fas fa-tasks"></i>
            </div>
            <h3><?= __('assignments') ?></h3>
            <p><?= __('assignments_desc') ?></p>
        </div>
        <div class="feature-card">
            <div class="icon icon-info">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3><?= __('quizzes') ?></h3>
            <p><?= __('quizzes_desc') ?></p>
        </div>
        <div class="feature-card">
            <div class="icon icon-success">
                <i class="fas fa-star"></i>
            </div>
            <h3><?= __('gradebook') ?></h3>
            <p><?= __('gradebook_desc') ?></p>
        </div>
        <div class="feature-card">
            <div class="icon icon-warning">
                <i class="fas fa-folder"></i>
            </div>
            <h3><?= __('course_materials') ?></h3>
            <p><?= __('course_materials_desc') ?></p>
        </div>
        <div class="feature-card">
            <div class="icon icon-danger">
                <i class="fas fa-users"></i>
            </div>
            <h3><?= __('student_management') ?></h3>
            <p><?= __('student_management_desc') ?></p>
        </div>
        <div class="feature-card">
            <div class="icon icon-purple">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3><?= __('analytics') ?></h3>
            <p><?= __('analytics_desc') ?></p>
        </div>
    </div>
</section>

<!-- Partner Universities Section -->
<section class="py-4 bg-light overflow-hidden">
    <div class="container mb-3">
        <h5 class="text-center text-muted fw-semibold"><?= __('trusted_by') ?></h5>
    </div>
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

<?php require_once 'includes/footer.php'; ?>