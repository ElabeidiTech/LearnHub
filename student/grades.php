<?php
require_once '../config/config.php';
requireRole('student');

$pageTitle = 'My Grades';
$user = getCurrentUser();

$stmt = $pdo->prepare("
    SELECT s.grade, s.graded_at, s.feedback, a.title, a.total_points, c.course_code, c.course_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    WHERE s.student_id = ? AND s.grade IS NOT NULL
    ORDER BY s.graded_at DESC
");
$stmt->execute([$user['id']]);
$assignmentGrades = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT qa.score, qa.total_points, qa.completed_at, q.title, c.course_code, c.course_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN courses c ON q.course_id = c.id
    WHERE qa.student_id = ? AND qa.completed_at IS NOT NULL
    ORDER BY qa.completed_at DESC
");
$stmt->execute([$user['id']]);
$quizGrades = $stmt->fetchAll();

$totalEarned = 0;
$totalPossible = 0;

foreach ($assignmentGrades as $g) {
    $totalEarned += $g['grade'];
    $totalPossible += $g['total_points'];
}
foreach ($quizGrades as $g) {
    $totalEarned += $g['score'];
    $totalPossible += $g['total_points'];
}

$overallPercent = $totalPossible > 0 ? round(($totalEarned / $totalPossible) * 100, 1) : 0;

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-chart-line text-success <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('my_grades') ?></h2>

    
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-percentage fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold"><?= $overallPercent ?>%</h3>
                    <p class="text-muted mb-0"><?= __('overall_average') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-tasks fa-3x text-primary"></i>
                    </div>
                    <h3 class="fw-bold"><?= count($assignmentGrades) ?></h3>
                    <p class="text-muted mb-0"><?= __('graded_assignments') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-question-circle fa-3x text-info"></i>
                    </div>
                    <h3 class="fw-bold"><?= count($quizGrades) ?></h3>
                    <p class="text-muted mb-0"><?= __('completed_quizzes') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-star fa-3x text-warning"></i>
                    </div>
                    <h3 class="fw-bold"><?= $totalEarned ?>/<?= $totalPossible ?></h3>
                    <p class="text-muted mb-0"><?= __('total_points') ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-tasks text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('assignment_grades') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assignmentGrades)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                            <p class="text-muted"><?= __('no_graded_assignments') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('assignment') ?></th>
                                        <th><?= __('course') ?></th>
                                        <th class="text-center"><?= __('score') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignmentGrades as $grade): ?>
                                        <tr>
                                            <td>
                                                <strong><?= sanitize($grade['title']) ?></strong>
                                                <br><small class="text-muted"><i class="fas fa-book me-1"></i><?= sanitize($grade['course_name']) ?></small>
                                                <?php if ($grade['feedback']): ?>
                                                    <br><small class="text-muted"><i class="fas fa-comment me-1"></i><?= sanitize($grade['feedback']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-primary"><?= sanitize($grade['course_code']) ?></span></td>
                                            <td class="text-center">
                                                <div class="bg-<?= ($grade['grade'] / $grade['total_points'] * 100) >= 90 ? 'success' : (($grade['grade'] / $grade['total_points'] * 100) >= 80 ? 'info' : (($grade['grade'] / $grade['total_points'] * 100) >= 70 ? 'warning' : 'danger')) ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center grade-circle-sm">
                                                    <?= $grade['grade'] ?>
                                                </div>
                                                <br><small class="text-muted">/ <?= $grade['total_points'] ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-question-circle text-info <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('quiz_grades') ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($quizGrades)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                            <p class="text-muted"><?= __('no_completed_quizzes') ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('quiz') ?></th>
                                        <th><?= __('course') ?></th>
                                        <th class="text-center"><?= __('score') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($quizGrades as $grade): ?>
                                        <tr>
                                            <td>
                                                <strong><?= sanitize($grade['title']) ?></strong>
                                                <br><small class="text-muted"><i class="fas fa-book me-1"></i><?= sanitize($grade['course_name']) ?></small>
                                                <br><small class="text-muted"><i class="fas fa-calendar me-1"></i><?= formatDateShort($grade['completed_at']) ?></small>
                                            </td>
                                            <td><span class="badge bg-primary"><?= sanitize($grade['course_code']) ?></span></td>
                                            <td class="text-center">
                                                <div class="bg-<?= ($grade['score'] / $grade['total_points'] * 100) >= 90 ? 'success' : (($grade['score'] / $grade['total_points'] * 100) >= 80 ? 'info' : (($grade['score'] / $grade['total_points'] * 100) >= 70 ? 'warning' : 'danger')) ?> text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-weight: bold; font-size: 0.9rem;">
                                                    <?= $grade['score'] ?>
                                                </div>
                                                <br><small class="text-muted">/ <?= $grade['total_points'] ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>