<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Quizzes';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ? AND course_id IN (SELECT id FROM courses WHERE teacher_id = ?)");
    $stmt->execute([$_POST['delete_id'], $user['id']]);
    setFlash('success', 'Quiz deleted successfully.');
    header('Location: quizzes.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT q.*, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count,
           (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND completed_at IS NOT NULL) as attempt_count,
           (SELECT AVG(score) FROM quiz_attempts WHERE quiz_id = q.id AND completed_at IS NOT NULL) as avg_score
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE c.teacher_id = ?
    ORDER BY q.due_date DESC
");
$stmt->execute([$user['id']]);
$quizzes = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-question-circle text-info <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
            <?= __('quizzes') ?>
        </h2>
        <a href="create-quiz.php" class="btn btn-primary">
            <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('create_quiz') ?>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($quizzes)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-question-circle fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-3"><?= __('no_quizzes_yet') ?></p>
                    <a href="create-quiz.php" class="btn btn-primary">
                        <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('create_first_quiz') ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php $isOverdue = isOverdue($quiz['due_date']); ?>
                        <div class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="flex-shrink-0">
                                <div class="rounded p-3 <?= $isOverdue ? 'bg-danger' : 'bg-info' ?> bg-opacity-10">
                                    <i class="fas fa-question-circle fa-2x <?= $isOverdue ? 'text-danger' : 'text-info' ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= sanitize($quiz['title']) ?></h5>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary"><?= sanitize($quiz['course_code']) ?></span>
                                    <small class="text-muted">
                                        <?= $quiz['question_count'] ?> <?= __('questions') ?>
                                        • <?= $quiz['time_limit'] ?> <?= __('min') ?>
                                        • <?= $quiz['total_points'] ?> <?= __('pts') ?>
                                        • <?= __('due') ?>: <?= formatDateShort($quiz['due_date']) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0 flex-wrap">
                                <span class="badge bg-success"><?= $quiz['attempt_count'] ?> <?= __('attempts') ?></span>
                                <?php if ($quiz['avg_score']): ?>
                                    <span class="badge bg-info"><?= __('avg') ?>: <?= round($quiz['avg_score'], 1) ?></span>
                                <?php endif; ?>
                                <a href="view-quiz-results.php?id=<?= $quiz['id'] ?>" class="btn btn-outline-primary btn-sm" title="<?= __('view_results') ?>">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                                <form method="POST" style="display: inline;" id="deleteQuizForm<?= $quiz['id'] ?>">
                                    <input type="hidden" name="delete_id" value="<?= $quiz['id'] ?>">
                                    <button type="button" class="btn btn-outline-danger btn-sm" title="<?= __('delete') ?>" onclick="showConfirm('<?= __('confirm_delete') ?>', function() { document.getElementById('deleteQuizForm<?= $quiz['id'] ?>').submit(); }, 'Delete Quiz')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>