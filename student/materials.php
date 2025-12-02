<?php
require_once '../config/config.php';
requireRole('student');

$pageTitle = 'Course Materials';
$user = getCurrentUser();

/** Retrieve all course materials from enrolled courses */
$stmt = $pdo->prepare("
    SELECT m.*, c.course_code, c.course_name
    FROM materials m
    JOIN courses c ON m.course_id = c.id
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY m.uploaded_at DESC
");
$stmt->execute([$user['id']]);
$materials = $stmt->fetchAll();

/** Group materials by course code for organized display */
$materialsByCourse = [];
foreach ($materials as $material) {
    $materialsByCourse[$material['course_code']][] = $material;
}

include '../includes/header.php';
?>

<!-- Main container for course materials page -->
<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-folder text-secondary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('course_materials') ?></h2>

    <?php if (empty($materials)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <p class="text-muted"><?= __('no_materials') ?></p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Materials grouped by course code with collapsible cards -->
        <?php foreach ($materialsByCourse as $courseCode => $courseMaterials): ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary"><?= sanitize($courseCode) ?></span>
                        <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>"><?= sanitize($courseMaterials[0]['course_name']) ?></span>
                    </div>
                    <span class="text-muted"><?= count($courseMaterials) ?> <?= __('files') ?></span>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($courseMaterials as $material): ?>
                            <?php
                            $ext = getFileExtension($material['file_name']);
                            $iconClass = 'fa-file';
                            $iconColor = '#6c757d';
                            
                            if (in_array($ext, ['pdf'])) {
                                $iconClass = 'fa-file-pdf';
                                $iconColor = '#dc3545';
                            } elseif (in_array($ext, ['doc', 'docx'])) {
                                $iconClass = 'fa-file-word';
                                $iconColor = '#2563eb';
                            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                $iconClass = 'fa-file-excel';
                                $iconColor = '#10b981';
                            } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                $iconClass = 'fa-file-powerpoint';
                                $iconColor = '#ea580c';
                            } elseif (in_array($ext, ['zip', 'rar'])) {
                                $iconClass = 'fa-file-archive';
                                $iconColor = '#f59e0b';
                            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $iconClass = 'fa-file-image';
                                $iconColor = '#0ea5e9';
                            }
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start py-3">
                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; min-width: 48px; background: <?= $iconColor ?>20; color: <?= $iconColor ?>;">
                                        <i class="fas <?= $iconClass ?> fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= sanitize($material['title']) ?></h6>
                                        <p class="mb-0 text-muted small">
                                            <?= sanitize($material['file_name']) ?>
                                            <span class="<?= getLanguageDirection() === 'rtl' ? 'me-2' : 'ms-2' ?>">• Uploaded <?= formatDateShort($material['uploaded_at']) ?></span>
                                        </p>
                                        <?php if ($material['description']): ?>
                                            <p class="mb-0 text-muted small mt-1"><?= sanitize($material['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <a href="<?= SITE_URL ?>/api/download.php?id=<?= $material['id'] ?>" class="btn btn-primary btn-sm <?= getLanguageDirection() === 'rtl' ? 'me-3' : 'ms-3' ?>">
                                    <i class="fas fa-download <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i><?= __('download') ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>