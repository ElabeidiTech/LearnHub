<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Assignments';
$user = getCurrentUser();

/** Handle assignment deletion request with ownership verification */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    /** Delete assignment only if it belongs to teacher's course (security check) */
    $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ? AND course_id IN (SELECT id FROM courses WHERE teacher_id = ?)");
    $stmt->execute([$_POST['delete_id'], $user['id']]);
    setFlash('success', 'Assignment deleted successfully.');
    header('Location: assignments.php');
    exit;
}

/** Retrieve all assignments for teacher's courses with submission counts and pending grades */
$stmt = $pdo->prepare("
    SELECT a.*, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id AND grade IS NULL) as pending_count
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
    WHERE c.teacher_id = ?
    ORDER BY a.due_date DESC
");
$stmt->execute([$user['id']]);
$assignments = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tasks text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>Assignments</h2>
        <a href="create-assignment.php" class="btn btn-primary">
            <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Assignment
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($assignments)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-3">No assignments created yet.</p>
                    <a href="create-assignment.php" class="btn btn-primary">
                        <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Your First Assignment
                    </a>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($assignments as $assignment): ?>
                        <?php $isOverdue = isOverdue($assignment['due_date']); ?>
                        <div class="list-group-item px-0 py-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 48px; height: 48px; background: rgba(<?= $isOverdue ? '220, 53, 69' : '255, 193, 7' ?>, 0.1); color: <?= $isOverdue ? '#dc3545' : '#ffc107' ?>;">
                                        <i class="fas fa-file-alt fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2"><?= sanitize($assignment['title']) ?></h5>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge bg-primary"><?= sanitize($assignment['course_code']) ?></span>
                                        <span class="text-muted small">
                                            <i class="far fa-calendar <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                            Due: <?= formatDate($assignment['due_date']) ?>
                                        </span>
                                        <span class="text-muted small">
                                            <i class="fas fa-star <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                            <?= $assignment['total_points'] ?> points
                                        </span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 align-items-end">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if ($assignment['pending_count'] > 0): ?>
                                            <span class="badge bg-warning text-dark"><?= $assignment['pending_count'] ?> to grade</span>
                                        <?php endif; ?>
                                        <span class="badge bg-info text-dark"><?= $assignment['submission_count'] ?> submitted</span>
                                    </div>
                                    <form method="POST" class="d-inline" id="deleteAssignmentForm<?= $assignment['id'] ?>">
                                        <input type="hidden" name="delete_id" value="<?= $assignment['id'] ?>">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="showConfirm('Are you sure you want to delete this assignment? This action cannot be undone.', function() { document.getElementById('deleteAssignmentForm<?= $assignment['id'] ?>').submit(); }, 'Delete Assignment')">
                                            <i class="fas fa-trash <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>