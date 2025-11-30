<?php
require_once '../config/config.php';
requireRole('student');

$user = getCurrentUser();
$quizId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT q.*, c.course_code, c.course_name
    FROM quizzes q
    JOIN courses c ON q.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE q.id = ? AND e.student_id = ?
");
$stmt->execute([$quizId, $user['id']]);
$quiz = $stmt->fetch();

if (!$quiz) {
    setFlash('danger', 'Quiz not found.');
    header('Location: quizzes.php');
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) as attempt_count FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? AND completed_at IS NOT NULL");
$stmt->execute([$quizId, $user['id']]);
$attemptData = $stmt->fetch();
$completedAttempts = $attemptData['attempt_count'];

if ($quiz['max_attempts'] > 0 && $completedAttempts >= $quiz['max_attempts']) {
    setFlash('info', 'You have used all ' . $quiz['max_attempts'] . ' attempt(s) for this quiz.');
    header('Location: quizzes.php');
    exit;
}

if (isOverdue($quiz['due_date'])) {
    setFlash('danger', 'This quiz has expired.');
    header('Location: quizzes.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE quiz_id = ? AND student_id = ? AND completed_at IS NULL");
$stmt->execute([$quizId, $user['id']]);
$attempt = $stmt->fetch();

$isNewAttempt = false;

if (!$attempt) {
    $stmt = $pdo->prepare("INSERT INTO quiz_attempts (quiz_id, student_id) VALUES (?, ?)");
    $stmt->execute([$quizId, $user['id']]);
    $attemptId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("SELECT * FROM quiz_attempts WHERE id = ?");
    $stmt->execute([$attemptId]);
    $attempt = $stmt->fetch();
    $isNewAttempt = true;
}

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id");
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $totalPoints = 0;
    
    foreach ($questions as $question) {
        $answer = $_POST['q' . $question['id']] ?? '';
        $isCorrect = strtoupper($answer) === strtoupper($question['correct_answer']);
        
        if ($isCorrect) {
            $score += $question['points'];
        }
        $totalPoints += $question['points'];
        
        $stmt = $pdo->prepare("INSERT INTO quiz_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, ?, ?)");
        $stmt->execute([$attempt['id'], $question['id'], $answer, $isCorrect ? 1 : 0]);
    }
    
    $stmt = $pdo->prepare("UPDATE quiz_attempts SET score = ?, total_points = ?, completed_at = NOW() WHERE id = ?");
    $stmt->execute([$score, $totalPoints, $attempt['id']]);
    
    setFlash('success', "Quiz completed! Your score: $score / $totalPoints");
    header('Location: quizzes.php');
    exit;
}

$startTime = strtotime($attempt['started_at']);
$currentTime = time();
$elapsedSeconds = $currentTime - $startTime;
$totalTimeSeconds = $quiz['time_limit'] * 60;

if ($isNewAttempt) {
    $timeRemaining = $totalTimeSeconds;
} else {
    $timeRemaining = max(0, $totalTimeSeconds - $elapsedSeconds);
}

if (!$isNewAttempt && $elapsedSeconds > $totalTimeSeconds + 10) {
    $score = 0;
    $totalPoints = 0;
    
    foreach ($questions as $question) {
        $totalPoints += $question['points'];
        $checkStmt = $pdo->prepare("SELECT id FROM quiz_answers WHERE attempt_id = ? AND question_id = ?");
        $checkStmt->execute([$attempt['id'], $question['id']]);
        if (!$checkStmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO quiz_answers (attempt_id, question_id, selected_answer, is_correct) VALUES (?, ?, '', 0)");
            $stmt->execute([$attempt['id'], $question['id']]);
        }
    }
    
    $stmt = $pdo->prepare("UPDATE quiz_attempts SET score = 0, total_points = ?, completed_at = NOW() WHERE id = ?");
    $stmt->execute([$totalPoints, $attempt['id']]);
    
    setFlash('warning', 'Time expired! Quiz auto-submitted with score: 0 / ' . $totalPoints);
    header('Location: quizzes.php');
    exit;
}

$pageTitle = 'Quiz: ' . $quiz['title'];
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1"><?= sanitize($quiz['title']) ?></h4>
                    <p class="mb-0 text-muted">
                        <span class="badge bg-primary"><?= sanitize($quiz['course_code']) ?></span>
                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>"><?= count($questions) ?> <?= __('questions') ?></span>
                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• <?= $quiz['total_points'] ?> <?= __('points') ?></span>
                    </p>
                </div>
                <div class="alert alert-<?= $timeRemaining <= 60 ? 'danger' : ($timeRemaining <= 300 ? 'warning' : 'info') ?> mb-0 py-2 px-3" id="timer">
                    <i class="fas fa-clock <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                    <strong id="time"><?= floor($timeRemaining / 60) ?>:<?= str_pad($timeRemaining % 60, 2, '0', STR_PAD_LEFT) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="quizForm">
        <?php foreach ($questions as $index => $question): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center icon-circle-sm">
                            <?= $index + 1 ?>
                        </span>
                        <div class="flex-grow-1">
                            <strong><?= sanitize($question['question']) ?></strong>
                            <span class="text-muted float-end"><?= $question['points'] ?> <?= __('pts') ?></span>
                        </div>
                    </div>
                    
                    <div class="list-group">
                        <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="list-group-item list-group-item-action cursor-pointer">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="q<?= $question['id'] ?>" value="<?= $opt ?>" class="form-check-input <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>" <?= $opt === 'A' ? 'required' : '' ?>>
                                    <span><strong><?= $opt ?>.</strong> <?= sanitize($question['option_'.strtolower($opt)]) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between gap-2 mt-4">
            <a href="quizzes.php" class="btn btn-outline-secondary" onclick="return confirm('<?= __('confirm_cancel') ?>');">
                <i class="fas fa-times <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('cancel') ?>
            </a>
            <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('<?= __('confirm_submit') ?>');">
                <i class="fas fa-paper-plane <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('submit_quiz') ?>
            </button>
        </div>
    </form>
</div>

<script>
let timeRemaining = <?= $timeRemaining ?>;
const timerElement = document.getElementById('timer');
const timeDisplay = document.getElementById('time');

const timerInterval = setInterval(() => {
    timeRemaining--;
    
    if (timeRemaining <= 0) {
        clearInterval(timerInterval);
        alert('<?= __('time_up') ?>');
        document.getElementById('quizForm').submit();
        return;
    }
    
    const minutes = Math.floor(timeRemaining / 60);
    const seconds = timeRemaining % 60;
    timeDisplay.textContent = minutes + ':' + seconds.toString().padStart(2, '0');
    
    if (timeRemaining <= 60) {
        timerElement.classList.remove('alert-warning');
        timerElement.classList.add('alert-danger');
    } else if (timeRemaining <= 300) {
        timerElement.classList.remove('alert-danger', 'alert-info');
        timerElement.classList.add('alert-warning');
    } else {
        timerElement.classList.remove('alert-danger', 'alert-warning');
        timerElement.classList.add('alert-info');
    }
}, 1000);

window.onbeforeunload = function() {
    return '<?= __('confirm_leave') ?>';
};

document.getElementById('quizForm').onsubmit = function() {
    window.onbeforeunload = null;
};
</script>

<?php include '../includes/footer.php'; ?>
