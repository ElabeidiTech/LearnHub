<?php
require_once '../config/config.php';
requireRole('student');

$user = getCurrentUser();
$assignmentId = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT a.*, c.course_code, c.course_name, c.id as course_id
    FROM assignments a
    JOIN courses c ON a.course_id = c.id
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
$existingSubmission = $stmt->fetch();

$pageTitle = 'Submit: ' . $assignment['title'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = trim($_POST['comment'] ?? '');
    
    $fileName = null;
    $filePath = null;
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $ext = getFileExtension($file['name']);
        
        if (!in_array($ext, getAllowedTypes())) {
            $error = 'Invalid file type. Allowed: ' . implode(', ', getAllowedTypes());
        } elseif ($file['size'] > getMaxFileSize()) {
            $error = 'File too large. Maximum size: 10MB';
        } else {
            $uploadDir = UPLOAD_PATH . 'submissions/' . $user['id'] . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = $file['name'];
            $uniqueName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
            $filePath = 'submissions/' . $user['id'] . '/' . $uniqueName;
            
            if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filePath)) {
                $error = 'Failed to upload file. Please try again.';
                $filePath = null;
            }
        }
    } elseif (!$existingSubmission) {
        $error = 'Please upload a file.';
    }
    
    if (!$error) {
        if ($existingSubmission) {
            if ($filePath) {
                $stmt = $pdo->prepare("UPDATE submissions SET file_name = ?, file_path = ?, comment = ?, submitted_at = NOW() WHERE id = ?");
                $stmt->execute([$fileName, $filePath, $comment, $existingSubmission['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE submissions SET comment = ?, submitted_at = NOW() WHERE id = ?");
                $stmt->execute([$comment, $existingSubmission['id']]);
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_name, file_path, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$assignmentId, $user['id'], $fileName, $filePath, $comment]);
        }
        
        setFlash('success', 'Assignment submitted successfully!');
        header('Location: assignments.php');
        exit;
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <a href="assignments.php" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('back_to_assignments') ?>
    </a>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt text-warning <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('assignment_details') ?></h5>
                    <span class="badge bg-primary"><?= sanitize($assignment['course_code']) ?></span>
                </div>
                <div class="card-body">
                    <h4><?= sanitize($assignment['title']) ?></h4>
                    <p class="text-muted"><?= sanitize($assignment['course_name']) ?></p>
                    
                    <hr>
                    
                    <div class="bg-light p-3 rounded mb-3">
                        <h6 class="mb-2"><?= __('instructions') ?></h6>
                        <p class="mb-0 text-pre-wrap"><?= sanitize($assignment['description']) ?></p>
                    </div>
            
            
            <?php if (!empty($assignment['file_name']) && !empty($assignment['file_path'])): ?>
                <div class="alert alert-info border-0">
                    <h6 class="alert-heading"><i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('assignment_file') ?></h6>
                    <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center icon-circle-md">
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
                                <i class="fas <?= $iconClass ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= sanitize($assignment['file_name']) ?></h6>
                                <small class="text-muted"><?= __('download_to_complete') ?></small>
                            </div>
                        </div>
                        <a href="<?= SITE_URL ?>/api/download.php?type=assignment&id=<?= $assignment['id'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('download') ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded text-center">
                        <i class="fas fa-calendar text-primary mb-2"></i>
                        <p class="text-muted mb-1 small"><?= __('due_date') ?></p>
                        <strong class="small"><?= formatDate($assignment['due_date']) ?></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded text-center">
                        <i class="fas fa-star text-warning mb-2"></i>
                        <p class="text-muted mb-1 small"><?= __('points') ?></p>
                        <strong class="small"><?= $assignment['total_points'] ?></strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded text-center">
                        <i class="fas fa-clock text-info mb-2"></i>
                        <p class="text-muted mb-1 small"><?= __('time_left') ?></p>
                        <strong class="small <?= isOverdue($assignment['due_date']) ? 'text-danger' : '' ?>">
                            <?= getTimeRemaining($assignment['due_date']) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0"><i class="fas fa-upload text-success <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('submit_work') ?></h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <?= sanitize($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($existingSubmission): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <?= __('already_submitted') ?> <?= formatDate($existingSubmission['submitted_at']) ?>.
                            <?php if ($existingSubmission['grade'] !== null): ?>
                                <br><strong><?= __('grade') ?>: <?= $existingSubmission['grade'] ?>/<?= $assignment['total_points'] ?></strong>
                                <?php if ($existingSubmission['feedback']): ?>
                                    <br><?= __('feedback') ?>: <?= sanitize($existingSubmission['feedback']) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= __('can_resubmit') ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($existingSubmission['file_name']): ?>
                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center icon-circle-md">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?= sanitize($existingSubmission['file_name']) ?></h6>
                                        <small class="text-muted"><?= __('current_submission') ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($existingSubmission && $existingSubmission['grade'] !== null): ?>
                        <p class="text-muted"><?= __('cannot_resubmit_graded') ?></p>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label"><?= __('upload_work') ?> <?= $existingSubmission ? '('.__('optional_keep_current').')' : '*' ?></label>
                                <div class="border rounded p-4 text-center file-upload-area" onclick="document.getElementById('fileInput').click();" id="uploadArea">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                                    <p class="mb-1"><?= __('click_or_drag') ?></p>
                                    <small class="text-muted"><?= __('allowed_formats') ?></small>
                                    <input type="file" name="file" id="fileInput" class="d-none" 
                                           accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png">
                                </div>
                                <p id="selectedFile" class="mt-2 text-success mb-0"></p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><?= __('comments') ?> (<?= __('optional') ?>)</label>
                                <textarea name="comment" class="form-control" rows="3" placeholder="<?= __('add_comments') ?>..."><?= sanitize($existingSubmission['comment'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-paper-plane <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                                <?= $existingSubmission ? __('resubmit_assignment') : __('submit_assignment') ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof setupDragAndDrop === 'function') {
    setupDragAndDrop('uploadArea', 'fileInput', 'selectedFile');
}
</script>

<?php include '../includes/footer.php'; ?>