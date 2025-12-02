<?php
require_once '../config/config.php';
requireRole('student');

$pageTitle = 'Assignments';
$user = getCurrentUser();

/** Retrieve all assignments from enrolled courses with submission and grade status */
$stmt = $pdo->prepare("
    SELECT a.*, c.course_code, c.course_name,
           s.id as submission_id, s.grade, s.submitted_at, s.feedback
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ?
    WHERE e.student_id = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$user['id'], $user['id']]);
$assignments = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-tasks text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('assignments') ?></h2>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($assignments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?= __('no_assignments') ?></p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assignments as $assignment): ?>
                        <?php
                        $isSubmitted = !empty($assignment['submission_id']);
                        $isGraded = $assignment['grade'] !== null;
                        $isOverdue = isOverdue($assignment['due_date']) && !$isSubmitted;
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start py-3 cursor-pointer" onclick="window.location.href='assignment-details.php?id=<?= $assignment['id'] ?>'">
                            <div class="d-flex align-items-center gap-3 flex-grow-1">
                                <div class="bg-<?= $isGraded ? 'success' : ($isSubmitted ? 'info' : ($isOverdue ? 'danger' : 'warning')) ?> bg-opacity-10 text-<?= $isGraded ? 'success' : ($isSubmitted ? 'info' : ($isOverdue ? 'danger' : 'warning')) ?> rounded-circle d-flex align-items-center justify-content-center assignment-status-icon">
                                    <i class="fas fa-<?= $isGraded ? 'check-circle' : ($isSubmitted ? 'clock' : 'file-alt') ?> fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= sanitize($assignment['title']) ?></h6>
                                    <p class="mb-0 text-muted small">
                                        <span class="badge bg-primary"><?= sanitize($assignment['course_code']) ?></span>
                                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">Due: <?= formatDate($assignment['due_date']) ?></span>
                                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• <?= $assignment['total_points'] ?> <?= __('points') ?></span>
                                        <?php if (!empty($assignment['file_name'])): ?>
                                            <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• <i class="fas fa-paperclip"></i> <?= sanitize($assignment['file_name']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-end <?= getLanguageDirection() === 'rtl' ? 'me-3' : 'ms-3' ?>">
                                <?php if ($isGraded): ?>
                                    <div class="bg-<?= $assignment['grade'] >= 90 ? 'success' : ($assignment['grade'] >= 80 ? 'info' : ($assignment['grade'] >= 70 ? 'warning' : 'danger')) ?> text-white rounded-circle d-flex align-items-center justify-content-center grade-circle">
                                        <?= $assignment['grade'] ?>
                                    </div>
                                <?php elseif ($isSubmitted): ?>
                                    <span class="badge bg-info"><?= __('submitted') ?></span>
                                    <br><small class="text-muted"><?= __('awaiting_grade') ?></small>
                                <?php elseif ($isOverdue): ?>
                                    <span class="badge bg-danger"><?= __('overdue') ?></span>
                                <?php else: ?>
                                    <a href="submit-assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">
                                        <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('submit') ?>
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