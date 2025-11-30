<?php
require_once '../config/config.php';
requireRole('student');

$pageTitle = 'Dashboard';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_code'])) {
    $courseCode = trim($_POST['course_code']);
    
    if (empty($courseCode)) {
        setFlash('danger', 'Please enter a course code.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
        $stmt->execute([$courseCode]);
        $course = $stmt->fetch();
        
        if (!$course) {
            setFlash('danger', 'Course not found. Please check the course code and try again.');
        } else {
            $stmt = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$user['id'], $course['id']]);
            
            if ($stmt->fetch()) {
                setFlash('warning', 'You are already enrolled in this course.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $stmt->execute([$user['id'], $course['id']]);
                setFlash('success', 'Successfully enrolled in the course!');
            }
        }
    }
    
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
$stmt->execute([$user['id']]);
$coursesCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT a.id) 
    FROM assignments a 
    JOIN enrollments e ON a.course_id = e.course_id 
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE e.student_id = ? AND s.id IS NULL AND a.due_date >= NOW()
");
$stmt->execute([$user['id'], $user['id']]);
$pendingAssignments = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT q.id)
    FROM quizzes q
    JOIN enrollments e ON q.course_id = e.course_id
    WHERE e.student_id = ? AND q.due_date >= NOW()
");
$stmt->execute([$user['id']]);
$upcomingQuizzesCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as teacher_name,
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count,
           (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) as quiz_count,
           (SELECT COUNT(*) FROM materials WHERE course_id = c.id) as material_count
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><?= __('welcome') ?>, <?= sanitize($user['full_name']) ?>!</h2>

    
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-book fa-3x text-primary"></i>
                    </div>
                    <h3 class="fw-bold"><?= $coursesCount ?></h3>
                    <p class="text-muted mb-0"><?= __('enrolled courses') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-tasks fa-3x text-warning"></i>
                    </div>
                    <h3 class="fw-bold"><?= $pendingAssignments ?></h3>
                    <p class="text-muted mb-0"><?= __('pending assignments') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-question-circle fa-3x text-info"></i>
                    </div>
                    <h3 class="fw-bold"><?= $upcomingQuizzesCount ?></h3>
                    <p class="text-muted mb-0"><?= __('upcoming quizzes') ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-book text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('my_courses') ?></h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#enrollModal">
                    <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('join course') ?>
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($courses)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-3"><?= __('no_courses_enrolled') ?></p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollModal">
                        <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('join course') ?>
                    </button>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php 
                    $colors = ['primary', 'success', 'warning', 'danger', 'info', 'secondary'];
                    foreach ($courses as $index => $course): 
                        $colorClass = $colors[$index % count($colors)];
                    ?>
                        <div class="col-md-4">
                            <a href="course-details.php?id=<?= $course['id'] ?>" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm hover-card">
                                    <div class="card-header bg-<?= $colorClass ?> text-white border-0 text-center py-4">
                                        <i class="fas fa-book fa-3x"></i>
                                    </div>
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2"><?= sanitize($course['course_code']) ?></span>
                                        <h5 class="card-title text-dark"><?= sanitize($course['course_name']) ?></h5>
                                        <p class="text-muted mb-3"><i class="fas fa-user <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= sanitize($course['teacher_name']) ?></p>
                                        
                                        <div class="d-flex gap-1 flex-wrap">
                                            <span class="badge bg-info"><?= $course['assignment_count'] ?> <?= __('assignments') ?></span>
                                            <span class="badge bg-success"><?= $course['quiz_count'] ?> <?= __('quizzes') ?></span>
                                            <span class="badge bg-warning"><?= $course['material_count'] ?> <?= __('materials') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="enrollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= __('join course') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= __('course_code') ?></label>
                        <input type="text" name="course_code" class="form-control" placeholder="e.g., CS301" required>
                        <small class="text-muted"><?= __('enter_course_code') ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('join course') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>