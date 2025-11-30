<?php
require_once '../config/config.php';
requireRole('admin');

$pageTitle = 'Teacher Verification';
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved', verified_at = NOW(), verified_by = ? WHERE id = ?");
        $stmt->execute([$user['id'], $userId]);
        setFlash('success', __('teacher_approved'));
    } elseif ($action === 'reject') {
        $reason = trim($_POST['reason'] ?? '');
        $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$userId]);
        
        try {
            $stmt = $pdo->prepare("UPDATE teacher_verifications SET status = 'rejected', rejection_reason = ?, reviewed_by = ?, reviewed_at = NOW() WHERE user_id = ?");
            $stmt->execute([$reason, $user['id'], $userId]);
        } catch (PDOException $e) {
        }
        
        setFlash('warning', __('teacher_rejected'));
    } elseif ($action === 'suspend') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
        $stmt->execute([$userId]);
        setFlash('info', 'Teacher account suspended.');
    } elseif ($action === 'unsuspend') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
        $stmt->execute([$userId]);
        setFlash('success', 'Teacher account unsuspended and reactivated.');
    } elseif ($action === 'delete') {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE e FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE s FROM submissions s JOIN assignments a ON s.assignment_id = a.id JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE qa FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id JOIN courses c ON q.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE a FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE q FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE m FROM materials m JOIN courses c ON m.course_id = c.id WHERE c.teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE FROM courses WHERE teacher_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE FROM teacher_verifications WHERE user_id = ?")->execute([$userId]);
            
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            
            $pdo->commit();
            setFlash('success', 'Teacher account deleted successfully.');
        } catch (Exception $e) {
            $pdo->rollBack();
            setFlash('danger', 'Error deleting teacher account.');
        }
    }
    
    redirect('/admin/index.php');
}

$pendingTeachers = $pdo->query("
    SELECT u.*, tv.institution, tv.qualification, tv.experience_years, tv.subject_expertise, tv.verification_document
    FROM users u
    LEFT JOIN teacher_verifications tv ON u.id = tv.user_id
    WHERE u.role = 'teacher' AND u.status = 'pending'
    ORDER BY u.created_at ASC
")->fetchAll();

$allTeachers = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM courses WHERE teacher_id = u.id) as courses_count,
           (SELECT COUNT(DISTINCT e.student_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.teacher_id = u.id) as students_count
    FROM users u
    WHERE u.role = 'teacher'
    ORDER BY u.status ASC, u.created_at DESC
")->fetchAll();

include '../includes/header.php';
?>

<link href="<?= SITE_URL ?>/assets/css/tailwind.min.css" rel="stylesheet">

<div class="container mx-auto px-4 my-12">
    <h2 class="mb-4">
        <i class="fas fa-user-check text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
        <?= __('Dashboard') ?>
    </h2>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                    <h3 class="fw-bold"><?= count($pendingTeachers) ?></h3>
                    <p class="text-muted mb-0"><?= __('pending approval') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                    <h3 class="fw-bold"><?= count(array_filter($allTeachers, fn($t) => $t['status'] === 'approved')) ?></h3>
                    <p class="text-muted mb-0"><?= __('approved') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-3x text-danger mb-2"></i>
                    <h3 class="fw-bold"><?= count(array_filter($allTeachers, fn($t) => $t['status'] === 'rejected')) ?></h3>
                    <p class="text-muted mb-0"><?= __('rejected') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    <i class="fas fa-ban fa-3x text-secondary mb-2"></i>
                    <h3 class="fw-bold"><?= count(array_filter($allTeachers, fn($t) => $t['status'] === 'suspended')) ?></h3>
                    <p class="text-muted mb-0"><?= __('suspended') ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <?php if (count($pendingTeachers) > 0): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                <h5 class="mb-0">
                    <i class="fas fa-clock text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                    <?= __('pending_teachers') ?> (<?= count($pendingTeachers) ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($pendingTeachers as $teacher): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <?php if (!empty($teacher['profile_picture'])): ?>
                                        <img src="<?= SITE_URL ?>/uploads/<?= sanitize($teacher['profile_picture']) ?>" 
                                             class="rounded-circle" 
                                             class="rounded-circle avatar-lg" 
                                             alt="<?= sanitize($teacher['full_name']) ?>">
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center avatar-lg">
                                            <?= strtoupper(substr($teacher['full_name'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h5 class="mb-1"><?= sanitize($teacher['full_name']) ?></h5>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-envelope <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                            <?= sanitize($teacher['email']) ?>
                                        </p>
                                        <p class="mb-0 text-muted small">
                                            <i class="fas fa-calendar <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                            <?= __('applied') ?>: <?= formatDate($teacher['created_at']) ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <?php if ($teacher['institution'] || $teacher['qualification']): ?>
                                    <div class="bg-light p-2 rounded mb-2">
                                        <?php if ($teacher['institution']): ?>
                                            <p class="mb-1 small">
                                                <i class="fas fa-university text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                                <strong><?= __('institution') ?>:</strong> <?= sanitize($teacher['institution']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($teacher['qualification']): ?>
                                            <p class="mb-1 small">
                                                <i class="fas fa-graduation-cap text-success <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                                <strong><?= __('qualification') ?>:</strong> <?= sanitize($teacher['qualification']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($teacher['experience_years']): ?>
                                            <p class="mb-1 small">
                                                <i class="fas fa-briefcase text-info <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                                <strong><?= __('experience') ?>:</strong> <?= $teacher['experience_years'] ?> <?= __('years') ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($teacher['subject_expertise']): ?>
                                            <p class="mb-0 small">
                                                <i class="fas fa-book text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                                <strong><?= __('subjects') ?>:</strong> <?= sanitize($teacher['subject_expertise']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($teacher['bio']): ?>
                                    <p class="mb-0 small text-muted"><?= sanitize($teacher['bio']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 d-flex flex-column gap-2">
                                <?php if ($teacher['verification_document']): ?>
                                    <a href="<?= SITE_URL ?>/uploads/<?= sanitize($teacher['verification_document']) ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-file-pdf <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                        <?= __('view_document') ?>
                                    </a>
                                <?php endif; ?>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $teacher['id'] ?>">
                                    <button type="button"
                                            class="btn btn-success w-100 btn-sm approve-teacher-btn"
                                            data-teacher-id="<?= $teacher['id'] ?>"
                                            data-teacher-name="<?= sanitize($teacher['full_name']) ?>">
                                        <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                        <?= __('approve') ?>
                                    </button>
                                </form>
                                
                                <button type="button" 
                                        class="btn btn-danger w-100 btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal<?= $teacher['id'] ?>">
                                    <i class="fas fa-times <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                    <?= __('reject') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="modal fade" id="rejectModal<?= $teacher['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?= __('reject_teacher') ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?= $teacher['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label"><?= __('rejection_reason') ?></label>
                                            <textarea name="reason" class="form-control" rows="3" 
                                                      placeholder="<?= __('provide_reason') ?>..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <?= __('cancel') ?>
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                                            <i class="fas fa-times <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                            <?= __('reject_teacher') ?>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0">
                <i class="fas fa-users text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                <?= __('Teachers') ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?= __('teacher') ?></th>
                            <th><?= __('email') ?></th>
                            <th><?= __('status') ?></th>
                            <th><?= __('courses') ?></th>
                            <th><?= __('students') ?></th>
                            <th><?= __('joined') ?></th>
                            <th><?= __('actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allTeachers as $teacher): ?>
                            <tr class="<?= $teacher['status'] === 'suspended' ? 'suspended-row' : '' ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($teacher['profile_picture'])): ?>
                                            <img src="<?= SITE_URL ?>/uploads/<?= sanitize($teacher['profile_picture']) ?>" 
                                                 class="rounded-circle" 
                                                 class="rounded-circle avatar-md" 
                                                 alt="<?= sanitize($teacher['full_name']) ?>">
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                 class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center avatar-md">
                                                <?= strtoupper(substr($teacher['full_name'], 0, 2)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <span class="<?= $teacher['status'] === 'suspended' ? 'suspended-teacher' : '' ?>"><?= sanitize($teacher['full_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= sanitize($teacher['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $teacher['status'] === 'approved' ? 'success' : ($teacher['status'] === 'pending' ? 'warning' : ($teacher['status'] === 'rejected' ? 'danger' : 'secondary')) ?>">
                                        <?= ucfirst($teacher['status']) ?>
                                    </span>
                                </td>
                                <td><?= $teacher['courses_count'] ?></td>
                                <td><?= $teacher['students_count'] ?></td>
                                <td><?= formatDateShort($teacher['created_at']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ($teacher['status'] === 'suspended'): ?>
                                            <form method="POST" class="d-inline" id="unsuspendForm<?= $teacher['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $teacher['id'] ?>">
                                                <input type="hidden" name="action" value="unsuspend">
                                                <button type="button" 
                                                        class="btn btn-success btn-sm"
                                                        onclick="showConfirm('Remove suspension and reactivate this teacher?', function() { document.getElementById('unsuspendForm<?= $teacher['id'] ?>').submit(); }, 'Unsuspend Teacher')"
                                                        title="Unsuspend">
                                                    <i class="fas fa-check"></i> Unsuspend
                                                </button>
                                            </form>
                                        <?php elseif ($teacher['status'] === 'approved'): ?>
                                            <form method="POST" class="d-inline" id="suspendForm<?= $teacher['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $teacher['id'] ?>">
                                                <input type="hidden" name="action" value="suspend" id="suspendAction<?= $teacher['id'] ?>">
                                                <button type="button" 
                                                        class="btn btn-warning btn-sm"
                                                        onclick="showConfirm('Suspend this teacher? They will not be able to access their account.', function() { document.getElementById('suspendForm<?= $teacher['id'] ?>').submit(); }, 'Suspend Teacher')"
                                                        title="<?= __('suspend') ?>">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="d-inline" id="deleteForm<?= $teacher['id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $teacher['id'] ?>">
                                            <input type="hidden" name="action" value="delete" id="deleteAction<?= $teacher['id'] ?>">
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="showConfirm('Delete this teacher account permanently? This will remove all their courses, materials, and student enrollments. This action cannot be undone!', function() { document.getElementById('deleteForm<?= $teacher['id'] ?>').submit(); }, 'Delete Account')"
                                                    title="Delete Account">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle text-success <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
                    <?= __('approve_teacher') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="approveForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="approveTeacherId">
                    <input type="hidden" name="action" value="approve">
                    
                    <p class="mb-0">Are you sure you want to approve <strong id="approveTeacherName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <?= __('cancel') ?>
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                        <?= __('approve') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
