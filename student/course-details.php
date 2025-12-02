<?php
require_once '../config/config.php';
requireRole('student');

$user = getCurrentUser();
$courseId = $_GET['id'] ?? 0;

/** Verify student is enrolled in this course and retrieve course details with teacher info */
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name as teacher_name,
           e.id as enrollment_id
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    WHERE c.id = ? AND e.student_id = ?
");
$stmt->execute([$courseId, $user['id']]);
$course = $stmt->fetch();

/** Redirect if course not found or student not enrolled */
if (!$course) {
    setFlash('danger', 'Course not found or you are not enrolled.');
    header('Location: course.php');
    exit;
}

/** Retrieve all assignments for this course with student's submission status and grades */
$stmt = $pdo->prepare("
    SELECT a.*,
           s.id as submission_id,
           s.grade,
           s.submitted_at,
           s.feedback
    FROM assignments a
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE a.course_id = ?
    ORDER BY a.due_date DESC
");
$stmt->execute([$user['id'], $courseId]);
$assignments = $stmt->fetchAll();

/** Retrieve all quizzes with student's attempt count and best score */
$stmt = $pdo->prepare("
    SELECT q.*,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND completed_at IS NOT NULL) as completed_attempts,
           (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND completed_at IS NOT NULL ORDER BY score DESC LIMIT 1) as best_score
    FROM quizzes q
    WHERE q.course_id = ?
    ORDER BY q.due_date DESC
");
$stmt->execute([$user['id'], $user['id'], $courseId]);
$quizzes = $stmt->fetchAll();

/** Retrieve all downloadable course materials ordered by upload date */
$stmt = $pdo->prepare("
    SELECT m.*
    FROM materials m
    WHERE m.course_id = ?
    ORDER BY m.uploaded_at DESC
");
$stmt->execute([$courseId]);
$materials = $stmt->fetchAll();

$pageTitle = $course['course_name'];
include '../includes/header.php';
?>

<!-- Main container for course details page -->
<div class="container my-5">
    
    <!-- Course header card: course name, code, teacher, and description -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="mb-2">
                        <a href="course.php" class="text-decoration-none text-muted">
                            <i class="fas fa-arrow-left me-1"></i>Back to Courses
                        </a>
                    </div>
                    <span class="badge bg-primary mb-2"><?= sanitize($course['course_code']) ?></span>
                    <h2 class="mb-2"><?= sanitize($course['course_name']) ?></h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user me-1"></i><?= sanitize($course['teacher_name']) ?>
                    </p>
                </div>
            </div>
            <?php if ($course['description']): ?>
                <hr>
                <p class="mb-0"><?= nl2br(sanitize($course['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tab navigation for assignments, quizzes, materials, and grades sections -->
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
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#grades-tab" type="button">
                <i class="fas fa-chart-bar me-1"></i>Grades
            </button>
        </li>
    </ul>

    <!-- Tab content panels -->
    <div class="tab-content">
        
        <!-- Assignments tab: all course assignments with submission status and grades -->
        <div class="tab-pane fade show active" id="assignments-tab">
            <?php if (empty($assignments)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No assignments yet</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($assignments as $assignment): ?>
                        <?php $isOverdue = isOverdue($assignment['due_date']); ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2"><?= sanitize($assignment['title']) ?></h5>
                                            <p class="text-muted mb-2"><?= sanitize($assignment['description']) ?></p>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <span class="badge bg-<?= $isOverdue ? 'danger' : 'primary' ?>">
                                                    <i class="fas fa-calendar me-1"></i>Due: <?= formatDateShort($assignment['due_date']) ?>
                                                </span>
                                                <?php if ($assignment['submission_id']): ?>
                                                    <?php if ($assignment['grade'] !== null): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Graded: <?= $assignment['grade'] ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-clock me-1"></i>Submitted - Pending Grade
                                                        </span>
                                                    <?php endif; ?>
                                                <?php elseif ($isOverdue): ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>Overdue
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-exclamation me-1"></i>Not Submitted
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if (!$assignment['submission_id'] && !$isOverdue): ?>
                                                <a href="submit-assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-upload me-1"></i>Submit
                                                </a>
                                            <?php elseif ($assignment['submission_id']): ?>
                                                <a href="assignment-details.php?id=<?= $assignment['id'] ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quizzes tab: all course quizzes with attempt tracking and best scores -->
        <div class="tab-pane fade" id="quizzes-tab">
            <?php if (empty($quizzes)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No quizzes yet</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php 
                        $isOverdue = isOverdue($quiz['due_date']);
                        $canRetry = $quiz['max_attempts'] == -1 || $quiz['completed_attempts'] < $quiz['max_attempts'];
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-2"><?= sanitize($quiz['title']) ?></h5>
                                            <div class="d-flex gap-2 flex-wrap mb-2">
                                                <span class="badge bg-info"><?= $quiz['time_limit'] ?> min</span>
                                                <span class="badge bg-secondary"><?= $quiz['total_points'] ?> pts</span>
                                                <span class="badge bg-<?= $isOverdue ? 'danger' : 'primary' ?>">
                                                    Due: <?= formatDateShort($quiz['due_date']) ?>
                                                </span>
                                            </div>
                                            <?php if ($quiz['completed_attempts'] > 0): ?>
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <?php if ($quiz['max_attempts'] == -1): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-infinity me-1"></i>Unlimited attempts
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">
                                                            <?= $quiz['completed_attempts'] ?>/<?= $quiz['max_attempts'] ?> attempts
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($quiz['best_score'] !== null): ?>
                                                        <span class="badge bg-primary">
                                                            Best Score: <?= $quiz['best_score'] ?>/<?= $quiz['total_points'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <?php if ($isOverdue): ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-lock me-1"></i>Expired
                                                </button>
                                            <?php elseif ($quiz['completed_attempts'] == 0): ?>
                                                <a href="take-quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-play me-1"></i>Start Quiz
                                                </a>
                                            <?php elseif ($canRetry): ?>
                                                <a href="take-quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-warning btn-sm">
                                                    <i class="fas fa-redo me-1"></i>Retry
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    <i class="fas fa-check me-1"></i>Completed
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Materials tab: downloadable course files and resources -->
        <div class="tab-pane fade" id="materials" role="tabpanel">
            <?php if (empty($materials)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-download fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No materials yet</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Material items: file name, icon, upload date, and download button -->
                <div class="list-group">
                    <?php foreach ($materials as $material): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div>
                                        <?php
                                        $ext = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
                                        $iconClass = 'fa-file';
                                        if (in_array($ext, ['pdf'])) $iconClass = 'fa-file-pdf text-danger';
                                        elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'fa-file-word text-primary';
                                        elseif (in_array($ext, ['ppt', 'pptx'])) $iconClass = 'fa-file-powerpoint text-warning';
                                        elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel text-success';
                                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'fa-file-image text-info';
                                        ?>
                                        <i class="fas <?= $iconClass ?> fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($material['title']) ?></h6>
                                        <small class="text-muted">
                                            <?= sanitize($material['file_name']) ?>
                                            • Uploaded <?= formatDateShort($material['uploaded_at']) ?>
                                        </small>
                                    </div>
                                </div>
                                <a href="<?= SITE_URL ?>/api/download.php?id=<?= $material['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>Download
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grades tab: course average, assignment grades, and quiz scores -->
        <div class="tab-pane fade" id="grades-tab">
            <?php
            /** Filter assignments and quizzes that have been graded */
            $assignmentGrades = array_filter($assignments, function($a) { return $a['grade'] !== null; });
            $quizGrades = array_filter($quizzes, function($q) { return $q['best_score'] !== null; });
            
            /** Calculate total earned points and total possible points across all graded items */
            $totalEarned = 0;
            $totalPossible = 0;
            
            foreach ($assignmentGrades as $g) {
                $totalEarned += $g['grade'];
                $totalPossible += 100; // Assignments are out of 100
            }
            foreach ($quizGrades as $g) {
                $totalEarned += $g['best_score'];
                $stmt = $pdo->prepare("SELECT total_points FROM quizzes WHERE id = ?");
                $stmt->execute([$g['id']]);
                $quizData = $stmt->fetch();
                $totalPossible += $quizData['total_points'];
            }
            
            /** Calculate overall course average as percentage */
            $courseAverage = $totalPossible > 0 ? round(($totalEarned / $totalPossible) * 100, 1) : 0;
            ?>
            
            <!-- Statistics cards: course average percentage, graded assignments count, completed quizzes count -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-percentage fa-3x text-success mb-3"></i>
                            <h2 class="fw-bold text-success"><?= $courseAverage ?>%</h2>
                            <p class="text-muted mb-0">Course Average</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-tasks fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold"><?= count($assignmentGrades) ?></h2>
                            <p class="text-muted mb-0">Graded Assignments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm text-center">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-3x text-info mb-3"></i>
                            <h2 class="fw-bold"><?= count($quizGrades) ?></h2>
                            <p class="text-muted mb-0">Completed Quizzes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignment grades table with grade, graded date, and feedback columns -->
            <?php if (!empty($assignmentGrades)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-tasks text-primary me-2"></i>Assignment Grades</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Assignment</th>
                                        <th class="text-center">Grade</th>
                                        <th>Graded</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignmentGrades as $grade): ?>
                                        <tr>
                                            <td><strong><?= sanitize($grade['title']) ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $grade['grade'] >= 90 ? 'success' : ($grade['grade'] >= 80 ? 'info' : ($grade['grade'] >= 70 ? 'warning' : 'danger')) ?> fs-6">
                                                    <?= $grade['grade'] ?>%
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?= formatDateShort($grade['submitted_at']) ?></small></td>
                                            <td>
                                                <?php if ($grade['feedback']): ?>
                                                    <small><?= sanitize($grade['feedback']) ?></small>
                                                <?php else: ?>
                                                    <small class="text-muted">No feedback</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quiz grades table with best score, percentage, and attempts used -->
            <?php if (!empty($quizGrades)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0"><i class="fas fa-question-circle text-info me-2"></i>Quiz Grades</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Quiz</th>
                                        <th class="text-center">Best Score</th>
                                        <th class="text-center">Attempts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizGrades as $grade): ?>
                                        <?php
                                        $stmt = $pdo->prepare("SELECT total_points FROM quizzes WHERE id = ?");
                                        $stmt->execute([$grade['id']]);
                                        $quizData = $stmt->fetch();
                                        $percentage = ($grade['best_score'] / $quizData['total_points']) * 100;
                                        ?>
                                        <tr>
                                            <td><strong><?= sanitize($grade['title']) ?></strong></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $percentage >= 90 ? 'success' : ($percentage >= 80 ? 'info' : ($percentage >= 70 ? 'warning' : 'danger')) ?> fs-6">
                                                    <?= $grade['best_score'] ?> / <?= $quizData['total_points'] ?>
                                                </span>
                                                <br><small class="text-muted"><?= round($percentage, 1) ?>%</small>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($grade['max_attempts'] == -1): ?>
                                                    <span class="badge bg-secondary"><?= $grade['completed_attempts'] ?> attempts</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= $grade['completed_attempts'] ?> / <?= $grade['max_attempts'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($assignmentGrades) && empty($quizGrades)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No grades available yet</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
