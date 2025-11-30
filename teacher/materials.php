<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Course Materials';
$user = getCurrentUser();

$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY course_code");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $materialId = $_POST['material_id'] ?? 0;
        
        $stmt = $pdo->prepare("SELECT m.file_path FROM materials m JOIN courses c ON m.course_id = c.id WHERE m.id = ? AND c.teacher_id = ?");
        $stmt->execute([$materialId, $user['id']]);
        $material = $stmt->fetch();
        
        if ($material) {
            if (file_exists(UPLOAD_PATH . $material['file_path'])) {
                unlink(UPLOAD_PATH . $material['file_path']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
            $stmt->execute([$materialId]);
            setFlash('success', 'Material deleted successfully.');
        }
    } else {
        $courseId = $_POST['course_id'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($courseId) || empty($title)) {
            setFlash('danger', 'Please fill in all required fields.');
        } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('danger', 'Please select a file to upload.');
        } else {
            $file = $_FILES['file'];
            $ext = getFileExtension($file['name']);
            
            if ($file['size'] > getMaxFileSize()) {
                setFlash('danger', 'File too large. Maximum size: 10MB');
            } else {
                $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
                $stmt->execute([$courseId, $user['id']]);
                
                if ($stmt->fetch()) {
                    $uploadDir = UPLOAD_PATH . 'materials/' . $courseId . '/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = $file['name'];
                    $uniqueName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                    $filePath = 'materials/' . $courseId . '/' . $uniqueName;
                    
                    if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filePath)) {
                        $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, description, file_name, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$courseId, $title, $description, $fileName, $filePath, $ext]);
                        setFlash('success', 'Material uploaded successfully!');
                    } else {
                        setFlash('danger', 'Failed to upload file.');
                    }
                }
            }
        }
    }
    
    header('Location: materials.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT m.*, c.course_code, c.course_name
    FROM materials m
    JOIN courses c ON m.course_id = c.id
    WHERE c.teacher_id = ?
    ORDER BY m.uploaded_at DESC
");
$stmt->execute([$user['id']]);
$materials = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-upload text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('course_materials') ?></h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('upload_material') ?>
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($materials)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-3"><?= __('no_materials_yet') ?></p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('upload_first_material') ?>
                    </button>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($materials as $material): ?>
                        <?php
                        $ext = getFileExtension($material['file_name']);
                        $iconClass = 'fa-file';
                        $iconColor = 'text-secondary';
                        
                        if ($ext === 'pdf') { $iconClass = 'fa-file-pdf'; $iconColor = 'text-danger'; }
                        elseif (in_array($ext, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $iconColor = 'text-primary'; }
                        elseif (in_array($ext, ['xls', 'xlsx'])) { $iconClass = 'fa-file-excel'; $iconColor = 'text-success'; }
                        elseif (in_array($ext, ['ppt', 'pptx'])) { $iconClass = 'fa-file-powerpoint'; $iconColor = 'text-warning'; }
                        elseif (in_array($ext, ['zip', 'rar'])) { $iconClass = 'fa-file-archive'; $iconColor = 'text-info'; }
                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) { $iconClass = 'fa-file-image'; $iconColor = 'text-info'; }
                        ?>
                        <div class="list-group-item d-flex align-items-center gap-3 py-3">
                            <div class="flex-shrink-0">
                                <div class="rounded p-3 bg-light">
                                    <i class="fas <?= $iconClass ?> fa-2x <?= $iconColor ?>"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= sanitize($material['title']) ?></h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary"><?= sanitize($material['course_code']) ?></span>
                                    <small class="text-muted"><?= sanitize($material['file_name']) ?></small>
                                    <small class="text-muted">• <?= __('uploaded') ?> <?= formatDateShort($material['uploaded_at']) ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-shrink-0">
                                <a href="<?= SITE_URL ?>/api/download.php?id=<?= $material['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form method="POST" style="display: inline;" id="deleteMaterialForm<?= $material['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="material_id" value="<?= $material['id'] ?>">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="showConfirm('<?= __('confirm_delete') ?>', function() { document.getElementById('deleteMaterialForm<?= $material['id'] ?>').submit(); }, 'Delete Material')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('upload_material') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= __('course') ?> *</label>
                        <select name="course_id" class="form-select" required>
                            <option value=""><?= __('select_course') ?></option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>"><?= sanitize($course['course_code']) ?> - <?= sanitize($course['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('title') ?> *</label>
                        <input type="text" name="title" class="form-control" placeholder="<?= __('material_title_placeholder') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('description') ?></label>
                        <textarea name="description" class="form-control" rows="2" placeholder="<?= __('optional_description') ?>"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('file') ?> *</label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted"><?= __('max_size') ?>: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('cancel') ?></button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('upload') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>