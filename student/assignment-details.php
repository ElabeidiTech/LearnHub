<?php
require_once '../config/config.php';
requireRole('student');

$user = getCurrentUser();
$assignmentId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT a.*, c.course_code, c.course_name, c.id as course_id,
           u.full_name as teacher_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON c.teacher_id = u.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE a.id = ? AND e.student_id = ?
");
$stmt->execute([$assignmentId, $user['id']]);
$assignment = $stmt->fetch();

if (!$assignment) {
    setFlash('danger', 'Assignment not found.');
    header('Location: assignments.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->execute([$assignmentId, $user['id']]);
$submission = $stmt->fetch();

$pageTitle = $assignment['title'];

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="assignments.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('back_to_assignments') ?>
        </a>
        <?php if (!$submission && !isOverdue($assignment['due_date'])): ?>
            <a href="submit-assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary">
                <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('submit_assignment') ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-2"><?= sanitize($assignment['title']) ?></h3>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-primary"><?= sanitize($assignment['course_code']) ?></span>
                                <span class="text-muted">•</span>
                                <span class="text-muted"><?= sanitize($assignment['course_name']) ?></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-<?= isOverdue($assignment['due_date']) ? 'danger' : 'success' ?> fs-6 px-3 py-2">
                                <?= $assignment['total_points'] ?> <?= __('points') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="fas fa-info-circle text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('instructions') ?></h5>
                        <div class="bg-light p-4 rounded">
                            <p class="text-pre-wrap"><?= sanitize($assignment['description']) ?></p>
                        </div>
                    </div>

                    
                    <?php if (!empty($assignment['file_name']) && !empty($assignment['file_path'])): ?>
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-paperclip text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('attached_file') ?></h5>
                            <div class="card border-2 border-primary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center icon-circle-xl">
                                                <?php 
                                                $ext = getFileExtension($assignment['file_name']);
                                                $iconClass = 'fa-file';
                                                if ($ext === 'pdf') $iconClass = 'fa-file-pdf';
                                                elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'fa-file-word';
                                                elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'fa-file-excel';
                                                elseif (in_array($ext, ['ppt', 'pptx'])) $iconClass = 'fa-file-powerpoint';
                                                elseif (in_array($ext, ['zip', 'rar'])) $iconClass = 'fa-file-archive';
                                                elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'fa-file-image';
                                                ?>
                                                <i class="fas <?= $iconClass ?> fa-2x"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?= sanitize($assignment['file_name']) ?></h6>
                                                <p class="text-muted mb-0 small"><?= strtoupper($ext) ?> File</p>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="<?= SITE_URL ?>/api/download.php?type=assignment&id=<?= $assignment['id'] ?>" class="btn btn-primary" download>
                                                <i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('download') ?>
                                            </a>
                                            <?php if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'])): ?>
                                                <a href="<?= SITE_URL ?>/api/download.php?type=assignment&id=<?= $assignment['id'] ?>" class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('view') ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if ($submission): ?>
                        <div class="alert alert-<?= $submission['grade'] !== null ? 'success' : 'info' ?> border-0">
                            <h5 class="alert-heading">
                                <i class="fas fa-<?= $submission['grade'] !== null ? 'check-circle' : 'clock' ?> <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                <?= $submission['grade'] !== null ? __('graded') : __('submitted') ?>
                            </h5>
                            <p class="mb-2"><strong><?= __('submitted_at') ?>:</strong> <?= formatDate($submission['submitted_at']) ?></p>
                            <?php if ($submission['file_name']): ?>
                                <p class="mb-2"><strong><?= __('file') ?>:</strong> <?= sanitize($submission['file_name']) ?></p>
                            <?php endif; ?>
                            <?php if ($submission['comment']): ?>
                                <p class="mb-2"><strong><?= __('comment') ?>:</strong> <?= sanitize($submission['comment']) ?></p>
                            <?php endif; ?>
                            <?php if ($submission['grade'] !== null): ?>
                                <hr>
                                <p class="mb-2"><strong><?= __('grade') ?>:</strong> <span class="badge bg-<?= $submission['grade'] >= 90 ? 'success' : ($submission['grade'] >= 80 ? 'info' : ($submission['grade'] >= 70 ? 'warning' : 'danger')) ?> fs-5"><?= $submission['grade'] ?> / <?= $assignment['total_points'] ?></span></p>
                                <?php if ($submission['feedback']): ?>
                                    <p class="mb-0"><strong><?= __('feedback') ?>:</strong><br><?= nl2br(sanitize($submission['feedback'])) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php elseif (isOverdue($assignment['due_date'])): ?>
                        <div class="alert alert-danger border-0">
                            <i class="fas fa-exclamation-triangle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <strong><?= __('overdue') ?></strong> - <?= __('submission_closed') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('assignment_info') ?></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= __('teacher') ?></small>
                        <strong><?= sanitize($assignment['teacher_name']) ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= __('assigned_on') ?></small>
                        <strong><?= formatDate($assignment['created_at']) ?></strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= __('due_date') ?></small>
                        <strong class="<?= isOverdue($assignment['due_date']) ? 'text-danger' : 'text-success' ?>">
                            <?= formatDate($assignment['due_date']) ?>
                        </strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><?= __('time_remaining') ?></small>
                        <strong class="<?= isOverdue($assignment['due_date']) ? 'text-danger' : '' ?>">
                            <?= getTimeRemaining($assignment['due_date']) ?>
                        </strong>
                    </div>
                    <div>
                        <small class="text-muted d-block mb-1"><?= __('total_points') ?></small>
                        <strong class="text-primary fs-4"><?= $assignment['total_points'] ?></strong>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <?php if ($submission && $submission['grade'] !== null): ?>
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5><?= __('completed') ?></h5>
                        <p class="text-muted"><?= __('graded_and_reviewed') ?></p>
                    <?php elseif ($submission): ?>
                        <i class="fas fa-clock fa-4x text-info mb-3"></i>
                        <h5><?= __('submitted') ?></h5>
                        <p class="text-muted"><?= __('awaiting_grade') ?></p>
                    <?php elseif (isOverdue($assignment['due_date'])): ?>
                        <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                        <h5><?= __('overdue') ?></h5>
                        <p class="text-muted"><?= __('no_submission') ?></p>
                    <?php else: ?>
                        <i class="fas fa-pencil-alt fa-4x text-warning mb-3"></i>
                        <h5><?= __('pending') ?></h5>
                        <p class="text-muted mb-3"><?= __('submit_before_due') ?></p>
                        <a href="submit-assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary w-100">
                            <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('submit_now') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
