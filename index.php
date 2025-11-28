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
                    Complete Learning Management System
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                    Powerful platform for educational institutions to create, manage, and deliver courses efficiently.
                </p>
                <div class="d-flex gap-3 flex-wrap animate__animated animate__fadeInUp animate__delay-2s">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('teacher') || hasRole('admin')): ?>
                            <a href="teacher/create-course.php" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-plus me-2"></i>Create Course
                            </a>
                        <?php else: ?>
                            <a href="student/" class="btn btn-light btn-lg px-4">
                                <i class="fas fa-book-reader me-2"></i>My Courses
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="auth/register.php" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>Register
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
                <div class="stat-number"><?= number_format($totalStudents) ?>+</div>
                <p class="text-light mb-0">Enrolled Students</p>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-number"><?= number_format($totalCourses) ?>+</div>
                <p class="text-light mb-0">Courses Created</p>
            </div>
            <div class="col-md-4 stat-item">
                <div class="stat-number"><?= number_format($totalTeachers) ?>+</div>
                <p class="text-light mb-0">Instructors</p>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <h2>Everything You Need to Teach & Learn</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="icon" style="background: rgba(79, 70, 229, 0.1); color: var(--primary);">
                <i class="fas fa-tasks"></i>
            </div>
            <h3>Assignments</h3>
            <p>Create assignments with deadlines and point values. Students can upload files and submit their work.</p>
        </div>
        <div class="feature-card">
            <div class="icon" style="background: rgba(6, 182, 212, 0.1); color: var(--info);">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3>Quizzes</h3>
            <p>Build timed quizzes with multiple choice questions. Automatic grading saves time.</p>
        </div>
        <div class="feature-card">
            <div class="icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
                <i class="fas fa-star"></i>
            </div>
            <h3>Gradebook</h3>
            <p>Track all grades in one place. Provide feedback to students on their submissions.</p>
        </div>
        <div class="feature-card">
            <div class="icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i class="fas fa-folder"></i>
            </div>
            <h3>Course Materials</h3>
            <p>Upload PDFs, documents, and other files. Students can download materials anytime.</p>
        </div>
        <div class="feature-card">
            <div class="icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
                <i class="fas fa-users"></i>
            </div>
            <h3>Student Management</h3>
            <p>Easy enrollment with course codes. Monitor student progress and participation.</p>
        </div>
        <div class="feature-card">
            <div class="icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Analytics</h3>
            <p>View quiz statistics, submission rates, and class performance at a glance.</p>
        </div>
    </div>
</section>

<!-- Partner Universities Section -->
<section class="py-4 bg-light overflow-hidden">
    <div class="container mb-3">
        <h5 class="text-center text-muted fw-semibold">Trusted by Leading Canadian Universities</h5>
    </div>
    <div class="university-logos-wrapper">
        <div class="university-logos-track">
            <!-- First set of logos -->
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
            <!-- Duplicate set for seamless loop -->
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