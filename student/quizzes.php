<?php
require_once '../config/config.php';
requireRole('student');

$pageTitle = 'Quizzes';
$user = getCurrentUser();

/** Retrieve all quizzes from enrolled courses with attempt statistics and best scores */
$stmt = $pdo->prepare("
    SELECT q.*, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND completed_at IS NOT NULL) as completed_attempts,
           (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND completed_at IS NOT NULL ORDER BY score DESC LIMIT 1) as best_score,
           (SELECT total_points FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ? AND completed_at IS NOT NULL ORDER BY score DESC LIMIT 1) as attempt_total
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY q.due_date ASC
");
$stmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
$quizzes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-question-circle text-info <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('quizzes') ?></h2>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($quizzes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?= __('no_quizzes') ?></p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php
                        $hasCompleted = $quiz['completed_attempts'] > 0;
                        $maxAttemptsReached = ($quiz['max_attempts'] > 0 && $quiz['completed_attempts'] >= $quiz['max_attempts']);
                        $isOverdue = isOverdue($quiz['due_date']);
                        $remainingAttempts = $quiz['max_attempts'] > 0 ? ($quiz['max_attempts'] - $quiz['completed_attempts']) : -1;
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start py-3">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div class="bg-<?= $maxAttemptsReached ? 'success' : ($isOverdue ? 'danger' : 'info') ?> bg-opacity-10 text-<?= $maxAttemptsReached ? 'success' : ($isOverdue ? 'danger' : 'info') ?> rounded-circle d-flex align-items-center justify-content-center assignment-status-icon">
                                    <i class="fas fa-<?= $maxAttemptsReached ? 'check-circle' : 'question-circle' ?> fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= sanitize($quiz['title']) ?></h6>
                                    <p class="mb-0 text-muted small">
                                        <span class="badge bg-primary"><?= sanitize($quiz['course_code']) ?></span>
                                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>"><?= $quiz['time_limit'] ?> <?= __('minutes') ?></span>
                                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• <?= $quiz['total_points'] ?> <?= __('points') ?></span>
                                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• Due: <?= formatDateShort($quiz['due_date']) ?></span>
                                        <?php if ($quiz['max_attempts'] > 0): ?>
                                            <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">
                                                • <i class="fas fa-redo"></i> <?= $quiz['completed_attempts'] ?>/<?= $quiz['max_attempts'] ?> attempts
                                            </span>
                                        <?php else: ?>
                                            <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">
                                                • <i class="fas fa-infinity"></i> Unlimited attempts
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-end <?= getLanguageDirection() === 'rtl' ? 'me-3' : 'ms-3' ?>">
                                <?php if ($hasCompleted): ?>
                                    <div class="bg-<?= ($quiz['best_score'] / $quiz['attempt_total'] * 100) >= 90 ? 'success' : (($quiz['best_score'] / $quiz['attempt_total'] * 100) >= 80 ? 'info' : (($quiz['best_score'] / $quiz['attempt_total'] * 100) >= 70 ? 'warning' : 'danger')) ?> text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 grade-circle">
                                        <?= $quiz['best_score'] ?>
                                    </div>
                                    <small class="text-muted d-block">Best: <?= $quiz['best_score'] ?>/<?= $quiz['attempt_total'] ?></small>
                                    <?php if (!$maxAttemptsReached && !$isOverdue): ?>
                                        <a href="take-quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-outline-primary btn-sm mt-2">
                                            <i class="fas fa-redo <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Retry (<?= $remainingAttempts ?> left)
                                        </a>
                                    <?php endif; ?>
                                <?php elseif ($isOverdue): ?>
                                    <span class="badge bg-danger"><?= __('expired') ?></span>
                                <?php else: ?>
                                    <a href="take-quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-play <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('start_quiz') ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>