<?php
/**
 * Teacher Dashboard - Main page for approved teacher users
 * Displays courses, student count, pending submissions, and recent quiz attempts
 */

// Load configuration and require approved teacher status
require_once '../config/config.php';
requireApprovedTeacher();

// Set page title
$pageTitle = 'Dashboard';

// Get current user data
$user = getCurrentUser();

// Initialize dashboard statistics variables
$coursesCount = 0;
$studentsCount = 0;
$pendingSubmissions = [];
$recentQuizzes = [];

// Fetch dashboard data if database connection is available
if ($pdo) {
    // Get total number of courses created by this teacher
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE teacher_id = ?");
    $stmt->execute([$user['id']]);
    $coursesCount = $stmt->fetchColumn();

    // Get total number of unique students enrolled in teacher's courses
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.student_id) 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE c.teacher_id = ?
    ");
    $stmt->execute([$user['id']]);
    $studentsCount = $stmt->fetchColumn();

    // Get recent ungraded submissions (limit 10)
    $stmt = $pdo->prepare("
        SELECT s.*, a.title as assignment_title, a.total_points, c.course_code, u.full_name as student_name
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON s.student_id = u.id
        WHERE c.teacher_id = ? AND s.grade IS NULL
        ORDER BY s.submitted_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $pendingSubmissions = $stmt->fetchAll();

    // Get recent completed quiz attempts (limit 5)
    $stmt = $pdo->prepare("
        SELECT qa.*, q.title as quiz_title, c.course_code, u.full_name as student_name
        FROM quiz_attempts qa
        JOIN quizzes q ON qa.quiz_id = q.id
        JOIN courses c ON q.course_id = c.id
        JOIN users u ON qa.student_id = u.id
        WHERE c.teacher_id = ? AND qa.completed_at IS NOT NULL
        ORDER BY qa.completed_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $recentQuizzes = $stmt->fetchAll();
}

// Include header with navigation
include '../includes/header.php';
?>

<!-- Main container for teacher dashboard -->
<div class="container my-5">
    <!-- Welcome message with teacher name -->
    <h2 class="mb-4"><?= __('welcome') ?>, <?= sanitize($user['full_name']) ?>!</h2>

    <!-- Dashboard statistics cards showing courses, students, pending grades, and recent quizzes -->
    <div class="row g-4 mb-4">
        <!-- Courses Count Card -->
        <div class="col-md-3">
            <a href="courses.php" class="text-decoration-none">
                <div class="card text-center h-100 border-0 shadow-sm hover-shadow">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-book fa-3x text-primary"></i>
                        </div>
                        <h3 class="fw-bold text-dark"><?= $coursesCount ?></h3>
                        <p class="text-muted mb-0"><?= __('my_courses') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold"><?= $studentsCount ?></h3>
                    <p class="text-muted mb-0"><?= __('total_students') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-inbox fa-3x text-warning"></i>
                    </div>
                    <h3 class="fw-bold"><?= count($pendingSubmissions) ?></h3>
                    <p class="text-muted mb-0"><?= __('pending_grades') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-question-circle fa-3x text-info"></i>
                    </div>
                    <h3 class="fw-bold"><?= count($recentQuizzes) ?></h3>
                    <p class="text-muted mb-0"><?= __('recent_quiz_attempts') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick actions card with buttons for common teacher tasks -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-bolt text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('quick_actions') ?></h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <a href="create-assignment.php" class="btn btn-outline-primary d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-plus"></i><span><?= __('new_assignment') ?></span>
                </a>
                <a href="create-quiz.php" class="btn btn-outline-success d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-plus"></i><span><?= __('new_quiz') ?></span>
                </a>
                <a href="materials.php" class="btn btn-outline-secondary d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-upload"></i><span><?= __('upload_material') ?></span>
                </a>
                <a href="students.php" class="btn btn-outline-success d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-user-graduate"></i><span>View Students</span>
                </a>
                <a href="quizzes.php" class="btn btn-outline-primary d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-chart-bar"></i><span>Quiz Results</span>
                </a>
                <a href="gradebook.php" class="btn btn-outline-dark d-flex align-items-center gap-2 quick-action-btn">
                    <i class="fas fa-graduation-cap"></i><span><?= __('gradebook') ?></span>
                </a>
            </div>
        </div>
    </div>

    <!-- Pending submissions table card for grading student work -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-inbox text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('submissions_to_grade') ?></h5>
            <a href="gradebook.php" class="btn btn-sm btn-outline-primary"><?= __('view_all') ?></a>
        </div>
        <div class="card-body">
            <?php if (empty($pendingSubmissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <p class="text-muted"><?= __('all_caught_up') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('student') ?></th>
                                <th><?= __('assignment') ?></th>
                                <th><?= __('course') ?></th>
                                <th><?= __('submitted') ?></th>
                                <th><?= __('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSubmissions as $submission): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center icon-circle-sm">
                                                <?= strtoupper(substr($submission['student_name'], 0, 2)) ?>
                                            </div>
                                            <span><?= sanitize($submission['student_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= sanitize($submission['assignment_title']) ?></td>
                                    <td><span class="badge bg-primary"><?= sanitize($submission['course_code']) ?></span></td>
                                    <td class="text-muted"><?= formatDate($submission['submitted_at']) ?></td>
                                    <td>
                                        <a href="grade-submission.php?id=<?= $submission['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-star <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('grade') ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
</div>

<?php include '../includes/footer.php'; ?>
