<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Gradebook';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_submission'])) {
    $submissionId = (int)$_POST['submission_id'];
    
    $stmt = $pdo->prepare("
        SELECT s.id FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN courses c ON a.course_id = c.id
        WHERE s.id = ? AND c.teacher_id = ?
    ");
    $stmt->execute([$submissionId, $user['id']]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM submissions WHERE id = ?");
        $stmt->execute([$submissionId]);
        setFlash('success', 'Submission deleted successfully.');
    } else {
        setFlash('danger', 'Unauthorized action.');
    }
    
    header('Location: gradebook.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.*, a.title as assignment_title, a.total_points, c.course_code, c.course_name, 
           u.full_name as student_name, u.email as student_email
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON s.student_id = u.id
    WHERE c.teacher_id = ?
    ORDER BY s.grade IS NULL DESC, s.submitted_at DESC
");
$stmt->execute([$user['id']]);
$submissions = $stmt->fetchAll();

$pendingSubmissions = array_filter($submissions, fn($s) => $s['grade'] === null);
$gradedSubmissions = array_filter($submissions, fn($s) => $s['grade'] !== null);

include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4">
        <i class="fas fa-star text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
        <?= __('gradebook') ?>
    </h2>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-inbox text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                <?= __('pending_submissions') ?> 
                <span class="badge bg-warning text-dark"><?= count($pendingSubmissions) ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($pendingSubmissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <p class="text-muted"><?= __('all_caught_up') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= __('student') ?></th>
                                <th><?= __('assignment') ?></th>
                                <th><?= __('course') ?></th>
                                <th><?= __('submitted') ?></th>
                                <th><?= __('file') ?></th>
                                <th><?= __('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSubmissions as $submission): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center icon-circle-sm">
                                                <?= strtoupper(substr($submission['student_name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= sanitize($submission['student_name']) ?></div>
                                                <small class="text-muted"><?= sanitize($submission['student_email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= sanitize($submission['assignment_title']) ?></td>
                                    <td><span class="badge bg-primary"><?= sanitize($submission['course_code']) ?></span></td>
                                    <td class="text-muted small"><?= formatDate($submission['submitted_at']) ?></td>
                                    <td>
                                        <?php if ($submission['file_name']): ?>
                                            <a href="<?= SITE_URL ?>/api/download.php?type=submission&id=<?= $submission['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= sanitize($submission['file_name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="grade-submission.php?id=<?= $submission['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-star <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('grade') ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-check-circle text-success <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                <?= __('graded_submissions') ?> 
                <span class="badge bg-success"><?= count($gradedSubmissions) ?></span>
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($gradedSubmissions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?= __('no_graded_submissions') ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= __('student') ?></th>
                                <th><?= __('assignment') ?></th>
                                <th><?= __('course') ?></th>
                                <th><?= __('grade') ?></th>
                                <th><?= __('graded') ?></th>
                                <th><?= __('action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gradedSubmissions as $submission): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center icon-circle-sm">
                                                <?= strtoupper(substr($submission['student_name'], 0, 2)) ?>
                                            </div>
                                            <span class="fw-semibold"><?= sanitize($submission['student_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= sanitize($submission['assignment_title']) ?></td>
                                    <td><span class="badge bg-primary"><?= sanitize($submission['course_code']) ?></span></td>
                                    <td>
                                        <?php 
                                        $percentage = ($submission['grade'] / $submission['total_points'] * 100);
                                        $badgeClass = $percentage >= 90 ? 'bg-success' : ($percentage >= 80 ? 'bg-info' : ($percentage >= 70 ? 'bg-warning text-dark' : 'bg-danger'));
                                        ?>
                                        <span class="badge <?= $badgeClass ?> fs-6"><?= $submission['grade'] ?></span>
                                        <small class="text-muted">/ <?= $submission['total_points'] ?></small>
                                    </td>
                                    <td class="text-muted small"><?= formatDateShort($submission['graded_at']) ?></td>
                                    <td>
                                        <a href="grade-submission.php?id=<?= $submission['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('edit') ?>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?= $submission['id'] ?>)">
                                            <i class="fas fa-trash <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('delete') ?>
                                        </button>
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

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="delete_submission" value="1">
    <input type="hidden" name="submission_id" id="deleteSubmissionId">
</form>

<script>
function confirmDelete(submissionId) {
    if (confirm('<?= __('Are you sure you want to delete this submission?') ?>')) {
        document.getElementById('deleteSubmissionId').value = submissionId;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>