<?php
require_once '../config/config.php';
requireRole('teacher');

$user = getCurrentUser();
$submissionId = $_GET['id'] ?? 0;

// Get submission details
$stmt = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, a.description as assignment_desc, a.total_points, a.due_date,
           c.course_code, c.course_name, u.full_name as student_name, u.email as student_email
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE s.id = ? AND c.teacher_id = ?
");
$stmt->execute([$submissionId, $user['id']]);
$submission = $stmt->fetch();

if (!$submission) {
    setFlash('danger', 'Submission not found.');
    header('Location: gradebook.php');
    exit;
}

$pageTitle = 'Grade: ' . $submission['assignment_title'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = $_POST['grade'] ?? '';
    $feedback = trim($_POST['feedback'] ?? '');
    
    if ($grade === '' || !is_numeric($grade)) {
        $error = 'Please enter a valid grade.';
    } elseif ($grade < 0 || $grade > $submission['total_points']) {
        $error = 'Grade must be between 0 and ' . $submission['total_points'] . '.';
    } else {
        $stmt = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ?, graded_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([intval($grade), $feedback, $submissionId])) {
            setFlash('success', 'Grade saved successfully!');
            header('Location: gradebook.php');
            exit;
        } else {
            $error = 'Failed to save grade. Please try again.';
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <a href="gradebook.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('back_to_gradebook') ?>
    </a>

    <div class="row g-4">
        <!-- Submission Details -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('submission_details') ?></h5>
                    <span class="badge bg-primary"><?= sanitize($submission['course_code']) ?></span>
                </div>
                <div class="card-body">
                    <h4 class="mb-3"><?= sanitize($submission['assignment_title']) ?></h4>
                    
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <?= strtoupper(substr($submission['student_name'], 0, 2)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= sanitize($submission['student_name']) ?></div>
                            <small class="text-muted"><?= sanitize($submission['student_email']) ?></small>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mb-3">
                        <h6 class="text-primary mb-2"><?= __('assignment_instructions') ?></h6>
                        <p class="mb-0 small" style="white-space: pre-wrap;"><?= sanitize($submission['assignment_desc']) ?></p>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted d-block"><?= __('due_date') ?></small>
                                <strong class="small"><?= formatDate($submission['due_date']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted d-block"><?= __('submitted') ?></small>
                                <strong class="small"><?= formatDate($submission['submitted_at']) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded text-center">
                                <small class="text-muted d-block"><?= __('total_points') ?></small>
                                <strong class="small"><?= $submission['total_points'] ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if (strtotime($submission['submitted_at']) > strtotime($submission['due_date'])): ?>
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <?= __('late_submission') ?>: <?= round((strtotime($submission['submitted_at']) - strtotime($submission['due_date'])) / 3600, 1) ?> <?= __('hours') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Student's File -->
                    <?php if ($submission['file_name']): ?>
                        <h6 class="mt-4 mb-3"><?= __('submitted_file') ?></h6>
                        <div class="list-group-item d-flex align-items-center gap-3 p-3 rounded">
                            <div class="flex-shrink-0">
                                <div class="rounded p-3 bg-danger bg-opacity-10">
                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= sanitize($submission['file_name']) ?></h6>
                                <small class="text-muted"><?= __('click_to_download') ?></small>
                            </div>
                            <a href="<?= SITE_URL ?>/api/download.php?type=submission&id=<?= $submission['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('download') ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission['comment']): ?>
                        <h6 class="mt-4 mb-3"><?= __('student_comment') ?></h6>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0 small"><?= sanitize($submission['comment']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Grading Form -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-star text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('grade_submission') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                            <?= sanitize($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-semibold"><?= __('grade') ?> (<?= __('out_of') ?> <?= $submission['total_points'] ?>) *</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="number" name="grade" class="form-control form-control-lg text-center" style="width: 120px; font-size: 1.5rem;"
                                       value="<?= $submission['grade'] ?? '' ?>" min="0" max="<?= $submission['total_points'] ?>" required>
                                <span class="fs-4 text-muted">/ <?= $submission['total_points'] ?></span>
                            </div>
                        </div>

                        <!-- Quick Grade Buttons -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold"><?= __('quick_grade') ?></label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php foreach ([100, 90, 80, 70, 60, 50, 0] as $percent): ?>
                                    <?php $pts = round($submission['total_points'] * $percent / 100); ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                            onclick="document.querySelector('input[name=grade]').value = <?= $pts ?>">
                                        <?= $percent ?>% (<?= $pts ?>)
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold"><?= __('feedback') ?> (<?= __('optional') ?>)</label>
                            <textarea name="feedback" class="form-control" rows="6" placeholder="<?= __('provide_feedback') ?>"><?= sanitize($submission['feedback'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between gap-2">
                            <a href="gradebook.php" class="btn btn-outline-secondary"><?= __('cancel') ?></a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('save_grade') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>