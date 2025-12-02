<?php
require_once '../config/config.php';
requireApprovedTeacher();

$courseId = $_GET['id'] ?? 0;
$user = getCurrentUser();

/** Retrieve course details with enrollment and content statistics (ownership verification) */
$stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count,
           (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) as quiz_count,
           (SELECT COUNT(*) FROM materials WHERE course_id = c.id) as material_count
    FROM courses c
    WHERE c.id = ? AND c.teacher_id = ?
");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

$pageTitle = $course['course_name'];

/** Retrieve all assignments for this course with submission statistics */
$stmt = $pdo->prepare("
    SELECT a.*,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id AND grade IS NULL) as pending_count
    FROM assignments a
    WHERE a.course_id = ?
    ORDER BY a.due_date DESC
");
$stmt->execute([$courseId]);
$assignments = $stmt->fetchAll();

/** Retrieve all quizzes for this course with student attempt count */
$stmt = $pdo->prepare("
    SELECT q.*,
           (SELECT COUNT(DISTINCT student_id) FROM quiz_attempts WHERE quiz_id = q.id) as attempt_count
    FROM quizzes q
    WHERE q.course_id = ?
    ORDER BY q.created_at DESC
");
$stmt->execute([$courseId]);
$quizzes = $stmt->fetchAll();

/** Retrieve all materials for this course */
$stmt = $pdo->prepare("
    SELECT m.*
    FROM materials m
    WHERE m.course_id = ?
    ORDER BY m.uploaded_at DESC
");
$stmt->execute([$courseId]);
$materials = $stmt->fetchAll();

/** Retrieve all enrolled students with enrollment dates */
$stmt = $pdo->prepare("
    SELECT u.id, u.full_name, u.email, e.enrolled_at
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    WHERE e.course_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$courseId]);
$students = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Main container for course details page -->
<div class="container my-5">
    <!-- Course header card with gradient background and statistics -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white p-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="opacity-75 mb-2"><?= sanitize($course['course_code']) ?></h6>
                    <h2 class="mb-3"><?= sanitize($course['course_name']) ?></h2>
                    <p class="mb-0 opacity-90"><?= sanitize($course['description'] ?: 'No description') ?></p>
                </div>
                <a href="courses.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
            
            <div class="row mt-4">
                <div class="col-6 col-md-3 text-center">
                    <h4 class="mb-0"><?= $course['student_count'] ?></h4>
                    <small class="opacity-75">Students</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <h4 class="mb-0"><?= $course['assignment_count'] ?></h4>
                    <small class="opacity-75">Assignments</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <h4 class="mb-0"><?= $course['quiz_count'] ?></h4>
                    <small class="opacity-75">Quizzes</small>
                </div>
                <div class="col-6 col-md-3 text-center">
                    <h4 class="mb-0"><?= $course['material_count'] ?></h4>
                    <small class="opacity-75">Materials</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab navigation for assignments, quizzes, materials, and students -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#assignments-tab" type="button">
                <i class="fas fa-tasks me-1"></i>Assignments (<?= count($assignments) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#quizzes-tab" type="button">
                <i class="fas fa-question-circle me-1"></i>Quizzes (<?= count($quizzes) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#materials-tab" type="button">
                <i class="fas fa-file-download me-1"></i>Materials (<?= count($materials) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#students-tab" type="button">
                <i class="fas fa-users me-1"></i>Students (<?= count($students) ?>)
            </button>
        </li>
    </ul>

    <!-- Tab content container with all course sections -->
    <div class="tab-content">
        <!-- Assignments tab panel with list of course assignments -->
        <div class="tab-pane fade show active" id="assignments-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Assignments</h5>
                <a href="create-assignment.php?course_id=<?= $courseId ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Assignment
                </a>
            </div>
            
            <?php if (empty($assignments)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No assignments yet</p>
                        <a href="create-assignment.php?course_id=<?= $courseId ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create First Assignment
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2"><?= sanitize($assignment['title']) ?></h5>
                                            <p class="text-muted mb-2"><?= sanitize($assignment['description'] ?: 'No description') ?></p>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span><i class="fas fa-calendar me-1"></i>Due: <?= formatDate($assignment['due_date']) ?></span>
                                                <span><i class="fas fa-star me-1"></i><?= $assignment['total_points'] ?> pts</span>
                                                <span><i class="fas fa-inbox me-1"></i><?= $assignment['submission_count'] ?> submissions</span>
                                                <?php if ($assignment['pending_count'] > 0): ?>
                                                    <span class="text-warning"><i class="fas fa-exclamation-circle me-1"></i><?= $assignment['pending_count'] ?> pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="gradebook.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="tab-pane fade" id="quizzes-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Quizzes</h5>
                <a href="create-quiz.php?course_id=<?= $courseId ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Quiz
                </a>
            </div>
            
            <?php if (empty($quizzes)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No quizzes yet</p>
                        <a href="create-quiz.php?course_id=<?= $courseId ?>" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Create First Quiz
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2"><?= sanitize($quiz['title']) ?></h5>
                                            <p class="text-muted mb-2"><?= sanitize($quiz['description'] ?: 'No description') ?></p>
                                            <div class="d-flex gap-3 text-muted small">
                                                <span><i class="fas fa-clock me-1"></i><?= $quiz['time_limit'] ?> min</span>
                                                <span><i class="fas fa-users me-1"></i><?= $quiz['attempt_count'] ?> students attempted</span>
                                            </div>
                                        </div>
                                        <a href="view-quiz-results.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-chart-bar me-1"></i>Results
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="tab-pane fade" id="materials-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Course Materials</h5>
                <a href="materials.php?course_id=<?= $courseId ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-upload me-1"></i>Upload Material
                </a>
            </div>
            
            <?php if (empty($materials)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-download fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No materials uploaded yet</p>
                        <a href="materials.php?course_id=<?= $courseId ?>" class="btn btn-secondary">
                            <i class="fas fa-upload me-1"></i>Upload First Material
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($materials as $material): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= sanitize($material['title']) ?></h6>
                                            <small class="text-muted"><?= formatDate($material['uploaded_at']) ?></small>
                                        </div>
                                    </div>
                                    <a href="../api/download.php?file=<?= urlencode($material['file_path']) ?>" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="tab-pane fade" id="students-tab">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Enrolled Students</h5>
                <div class="bg-light p-3 rounded">
                    <small class="text-muted d-block mb-1">Course Code:</small>
                    <strong class="fs-5 text-primary"><?= sanitize($course['course_code']) ?></strong>
                </div>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No students enrolled yet</p>
                        <p class="text-muted small">Share the course code <strong><?= sanitize($course['course_code']) ?></strong> with students</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Email</th>
                                        <th>Enrolled Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 icon-circle-sm">
                                                        <?= strtoupper(substr($student['full_name'], 0, 2)) ?>
                                                    </div>
                                                    <?= sanitize($student['full_name']) ?>
                                                </div>
                                            </td>
                                            <td><?= sanitize($student['email']) ?></td>
                                            <td class="text-muted"><?= formatDate($student['enrolled_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
