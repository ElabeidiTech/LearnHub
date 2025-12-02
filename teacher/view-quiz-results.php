<?php
require_once '../config/config.php';
requireApprovedTeacher();

$user = getCurrentUser();
$quizId = $_GET['id'] ?? 0;

/** Retrieve quiz details with course information (ownership verification) */
$stmt = $pdo->prepare("
    SELECT q.*, c.course_code, c.course_name
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    WHERE q.id = ? AND c.teacher_id = ?
");
$stmt->execute([$quizId, $user['id']]);
$quiz = $stmt->fetch();

/** Redirect if quiz not found or doesn't belong to current teacher */
if (!$quiz) {
    setFlash('danger', 'Quiz not found.');
    header('Location: quizzes.php');
    exit;
}

$pageTitle = 'Results: ' . $quiz['title'];

/** Retrieve all completed quiz attempts with student information ordered by score */
$stmt = $pdo->prepare("
    SELECT qa.*, u.full_name as student_name, u.email as student_email
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    WHERE qa.quiz_id = ? AND qa.completed_at IS NOT NULL
    ORDER BY qa.score DESC
");
$stmt->execute([$quizId]);
$attempts = $stmt->fetchAll();

/** Calculate quiz-wide statistics: total attempts, average, highest, and lowest scores */
$totalAttempts = count($attempts);
$avgScore = $totalAttempts > 0 ? array_sum(array_column($attempts, 'score')) / $totalAttempts : 0;
$highestScore = $totalAttempts > 0 ? max(array_column($attempts, 'score')) : 0;
$lowestScore = $totalAttempts > 0 ? min(array_column($attempts, 'score')) : 0;

/** Retrieve question-level analytics: correct answer count and total answers for each question */
$stmt = $pdo->prepare("
    SELECT qq.*, 
           (SELECT COUNT(*) FROM quiz_answers qa 
            JOIN quiz_attempts qat ON qa.attempt_id = qat.id 
            WHERE qa.question_id = qq.id AND qa.is_correct = 1) as correct_count,
           (SELECT COUNT(*) FROM quiz_answers qa 
            JOIN quiz_attempts qat ON qa.attempt_id = qat.id 
            WHERE qa.question_id = qq.id) as total_answers
    FROM quiz_questions qq
    WHERE qq.quiz_id = ?
    ORDER BY qq.id
");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Main container for quiz results analytics page -->
<div class="container my-5">
    <!-- Back navigation button to quizzes list -->
    <a href="quizzes.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('back_to_quizzes') ?>
    </a>

    <!-- Page header: quiz title and course code badge -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-chart-bar text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
            <?= sanitize($quiz['title']) ?>
        </h2>
        <span class="badge bg-primary fs-6"><?= sanitize($quiz['course_code']) ?></span>
    </div>

    <!-- Statistics cards: total attempts, average score, highest score, lowest score -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>
                    <h3 class="fw-bold"><?= $totalAttempts ?></h3>
                    <p class="text-muted mb-0"><?= __('total_attempts') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h3 class="fw-bold"><?= round($avgScore, 1) ?></h3>
                    <p class="text-muted mb-0"><?= __('average_score') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-arrow-up fa-3x text-info"></i>
                    </div>
                    <h3 class="fw-bold"><?= $highestScore ?></h3>
                    <p class="text-muted mb-0"><?= __('highest_score') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-arrow-down fa-3x text-danger"></i>
                    </div>
                    <h3 class="fw-bold"><?= $lowestScore ?></h3>
                    <p class="text-muted mb-0"><?= __('lowest_score') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Two-column layout: student results table and question analysis -->
    <div class="row g-4">
        <!-- Left column: Student results ranked by score -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-list text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('student_results') ?></h5>
                </div>
                <div class="card-body">
                    <!-- Empty state: displayed when no students have attempted the quiz -->
                    <?php if (empty($attempts)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                            <p class="text-muted"><?= __('no_attempts_yet') ?></p>
                        </div>
                    <?php else: ?>
                        <!-- Student results table: rank, name, score with color-coded badges, and completion date -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th><?= __('rank') ?></th>
                                        <th><?= __('student') ?></th>
                                        <th><?= __('score') ?></th>
                                        <th><?= __('completed') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attempts as $index => $attempt): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                /** Display trophy/medal icons for top 3 positions, rank number for others */
                                                if ($index === 0): ?>
                                                    <i class="fas fa-trophy text-warning fs-5"></i>
                                                <?php elseif ($index === 1): ?>
                                                    <i class="fas fa-medal text-secondary fs-5"></i>
                                                <?php elseif ($index === 2): ?>
                                                    <i class="fas fa-medal fs-5" style="color: #cd7f32;"></i>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= $index + 1 ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-semibold"><?= sanitize($attempt['student_name']) ?></td>
                                            <td>
                                                <?php 
                                                /** Calculate percentage and assign color-coded badge based on performance */
                                                $percentage = ($attempt['score'] / $attempt['total_points'] * 100);
                                                $badgeClass = $percentage >= 90 ? 'bg-success' : ($percentage >= 80 ? 'bg-info' : ($percentage >= 70 ? 'bg-warning' : 'bg-danger'));
                                                ?>
                                                <span class="badge <?= $badgeClass ?> fs-6"><?= $attempt['score'] ?></span>
                                                <small class="text-muted">/ <?= $attempt['total_points'] ?></small>
                                            </td>
                                            <td class="text-muted small"><?= formatDate($attempt['completed_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right column: Question-by-question analysis with correct answer rates -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-pie text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('question_analysis') ?></h5>
                </div>
                <div class="card-body">
                    <?php foreach ($questions as $index => $question): ?>
                        <?php 
                        /** Calculate correct answer rate and assign color-coded progress bar based on difficulty level */
                        $correctRate = $question['total_answers'] > 0 
                            ? round(($question['correct_count'] / $question['total_answers']) * 100) 
                            : 0;
                        $progressClass = $correctRate >= 70 ? 'bg-success' : ($correctRate >= 50 ? 'bg-warning' : 'bg-danger');
                        $textClass = $correctRate >= 70 ? 'text-success' : ($correctRate >= 50 ? 'text-warning' : 'text-danger');
                        ?>
                        <!-- Question item: question text, correct answer percentage, and color-coded progress bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small"><strong>Q<?= $index + 1 ?>:</strong> <?= sanitize(substr($question['question'], 0, 50)) ?>...</span>
                                <span class="badge <?= $progressClass ?> ms-2">
                                    <?= $correctRate ?>%
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar <?= $progressClass ?>" role="progressbar" style="width: <?= $correctRate ?>%;" aria-valuenow="<?= $correctRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>