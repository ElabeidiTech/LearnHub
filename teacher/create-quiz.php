<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Create Quiz';
$user = getCurrentUser();

/** Retrieve all courses taught by current teacher for course selection dropdown */
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY course_code");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();

$error = '';

/** Process quiz creation form submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /** Extract quiz metadata and questions array from POST data */
    $courseId = $_POST['course_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $timeLimit = intval($_POST['time_limit'] ?? 30);
    $maxAttempts = intval($_POST['max_attempts'] ?? 1);
    $dueDate = $_POST['due_date'] ?? '';
    $dueTime = $_POST['due_time'] ?? '23:59';
    $questions = $_POST['questions'] ?? [];
    
    /** Validate required quiz fields and ensure at least one question */
    if (empty($courseId) || empty($title) || empty($dueDate)) {
        $error = 'Please fill in all required fields.';
    } elseif (empty($questions)) {
        $error = 'Please add at least one question.';
    } else {
        /** Verify teacher owns the selected course (security check) */
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$courseId, $user['id']]);
        
        if (!$stmt->fetch()) {
            $error = 'Invalid course selected.';
        } else {
            /** Combine date and time for due datetime */
            $dueDatetime = $dueDate . ' ' . $dueTime . ':00';
            
            /** Calculate total points by summing all question points */
            $totalPoints = 0;
            foreach ($questions as $q) {
                $totalPoints += intval($q['points'] ?? 10);
            }
            
            /** Insert quiz record into database */
            $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title, description, time_limit, max_attempts, total_points, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$courseId, $title, $description, $timeLimit, $maxAttempts, $totalPoints, $dueDatetime]);
            $quizId = $pdo->lastInsertId();
            
            /** Insert all questions for the quiz */
            $stmt = $pdo->prepare("INSERT INTO quiz_questions (quiz_id, question, option_a, option_b, option_c, option_d, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($questions as $q) {
                $stmt->execute([
                    $quizId,
                    $q['question'],
                    $q['option_a'],
                    $q['option_b'],
                    $q['option_c'],
                    $q['option_d'],
                    strtoupper($q['correct']),
                    intval($q['points'] ?? 10)
                ]);
            }
            
            /** Redirect to quizzes list with success message */
            setFlash('success', 'Quiz created successfully with ' . count($questions) . ' questions!');
            header('Location: quizzes.php');
            exit;
        }
    }
}

include '../includes/header.php';
?>

<!-- Main container for create quiz page -->
<div class="container my-5">
    <!-- Back to Quizzes Link -->
    <a href="quizzes.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('back_to_quizzes') ?>
    </a>

    <h2 class="mb-4">
        <i class="fas fa-plus-circle text-success <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
        <?= __('create_quiz') ?>
    </h2>

    <?php if (empty($courses)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-3"><?= __('need_course_first') ?></p>
                    <a href="courses.php" class="btn btn-primary">
                        <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('create_course') ?>
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>

    <?php if ($error): ?>
        <!-- Error alert for validation failures -->
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
            <?= sanitize($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" id="quizForm">
        <!-- Quiz settings card with course, title, time limit, attempts, and due date -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="fas fa-cog text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('quiz_settings') ?></h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= __('course') ?> *</label>
                        <select name="course_id" class="form-select" required>
                            <option value=""><?= __('select_course') ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= sanitize($course['course_code']) ?> - <?= sanitize($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label"><?= __('quiz_title') ?> *</label>
                        <input type="text" name="title" class="form-control" placeholder="<?= __('quiz_title_placeholder') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= __('description') ?> (<?= __('optional') ?>)</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="<?= __('quiz_instructions') ?>"></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><?= __('time_limit') ?> (<?= __('minutes') ?>)</label>
                        <input type="number" name="time_limit" class="form-control" value="30" min="5" max="180">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-redo text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            Max Attempts
                        </label>
                        <select name="max_attempts" class="form-select">
                            <option value="1">1 Attempt</option>
                            <option value="2">2 Attempts</option>
                            <option value="3">3 Attempts</option>
                            <option value="5">5 Attempts</option>
                            <option value="-1">Unlimited</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label"><?= __('due_date') ?> *</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label"><?= __('due_time') ?></label>
                        <input type="time" name="due_time" class="form-control" value="23:59">
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions container card with dynamic question addition -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('questions') ?></h5>
                <span class="badge bg-primary" id="questionCount">0 <?= __('questions') ?> • 0 <?= __('points') ?></span>
            </div>
            <div class="card-body" id="questionsContainer">
                
            </div>
        </div>

        <!-- Add question button card with dashed border -->
        <div class="card mb-4 border-2 border-dashed" style="border-color: #cbd5e1; background: #f8fafc;">
            <div class="card-body text-center py-4">
                <button type="button" class="btn btn-primary btn-lg" onclick="addQuestion()">
                    <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('add_question') ?>
                </button>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="quizzes.php" class="btn btn-outline-secondary"><?= __('cancel') ?></a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('create_quiz') ?>
            </button>
        </div>
    </form>

    <!-- Question card template for cloning when adding new questions -->
    <template id="questionTemplate">
        <div class="card mb-3 border-0 shadow-sm question-card" data-question-id="">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <span class="fw-bold"><?= __('question') ?> <span class="q-number"></span></span>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="" class="form-control form-control-sm points-input" style="width: 80px;" value="10" min="1" max="100" placeholder="<?= __('points') ?>">
                    <span class="text-muted"><?= __('pts') ?></span>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeQuestion(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label"><?= __('question') ?> *</label>
                    <textarea name="" class="form-control question-text" rows="2" placeholder="<?= __('enter_question') ?>" required></textarea>
                </div>
                
                <label class="form-label"><?= __('answer_options') ?> *</label>
                <div class="row g-2">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="" value="A" class="form-check-input correct-radio" required>
                            <strong>A.</strong>
                            <input type="text" name="" class="form-control option-a" placeholder="<?= __('option') ?> A" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="" value="B" class="form-check-input correct-radio">
                            <strong>B.</strong>
                            <input type="text" name="" class="form-control option-b" placeholder="<?= __('option') ?> B" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="" value="C" class="form-check-input correct-radio">
                            <strong>C.</strong>
                            <input type="text" name="" class="form-control option-c" placeholder="<?= __('option') ?> C" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="" value="D" class="form-check-input correct-radio">
                            <strong>D.</strong>
                            <input type="text" name="" class="form-control option-d" placeholder="<?= __('option') ?> D" required>
                        </div>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    <i class="fas fa-info-circle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('select_correct_answer') ?>
                </small>
            </div>
        </div>
    </template>

<script>
let questionCount = 0;

/**
 * Add a new question card to the quiz
 * Creates a new question from template with unique field names
 */
function addQuestion() {
    questionCount++;
    const template = document.getElementById('questionTemplate').content.cloneNode(true);
    const card = template.querySelector('.question-card');
    
    card.querySelector('.q-number').textContent = questionCount;
    card.dataset.questionId = questionCount;
    
    const idx = questionCount - 1;
    card.querySelector('.question-text').name = `questions[${idx}][question]`;
    card.querySelector('.points-input').name = `questions[${idx}][points]`;
    card.querySelector('.option-a').name = `questions[${idx}][option_a]`;
    card.querySelector('.option-b').name = `questions[${idx}][option_b]`;
    card.querySelector('.option-c').name = `questions[${idx}][option_c]`;
    card.querySelector('.option-d').name = `questions[${idx}][option_d]`;
    
    const radios = card.querySelectorAll('.correct-radio');
    const radioName = `correct_${questionCount}`;
    radios.forEach(radio => {
        radio.name = radioName;
        radio.addEventListener('change', function() {
            card.querySelector('input[name="questions[' + idx + '][correct]"]')?.remove();
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `questions[${idx}][correct]`;
            hidden.value = this.value;
            card.appendChild(hidden);
        });
    });
    
    document.getElementById('questionsContainer').appendChild(card);
    updateQuestionCount();
}

/**
 * Remove a question card from the quiz
 * Renumbers remaining questions after deletion
 * @param {HTMLElement} btn - The remove button element
 */
function removeQuestion(btn) {
    btn.closest('.question-card').remove();
    document.querySelectorAll('.question-card').forEach((card, index) => {
        card.querySelector('.q-number').textContent = index + 1;
    });
    questionCount = document.querySelectorAll('.question-card').length;
    updateQuestionCount();
}

/**
 * Update the question count and total points display
 * Calculates total points from all question point inputs
 */
function updateQuestionCount() {
    let totalPoints = 0;
    document.querySelectorAll('.points-input').forEach(input => {
        totalPoints += parseInt(input.value) || 0;
    });
    document.getElementById('questionCount').textContent = `${questionCount} <?= __('questions') ?> • ${totalPoints} <?= __('points') ?>`;
}

document.getElementById('questionsContainer').addEventListener('input', function(e) {
    if (e.target.classList.contains('points-input')) {
        updateQuestionCount();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    if (typeof addQuestion === 'function') {
        addQuestion();
    }
});
</script>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>