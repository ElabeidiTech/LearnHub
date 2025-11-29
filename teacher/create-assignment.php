<?php
require_once '../config/config.php';
requireRole('teacher');

$pageTitle = 'Create Assignment';
$user = getCurrentUser();

// Get teacher's courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ? ORDER BY course_code");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = $_POST['course_id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dueDate = $_POST['due_date'] ?? '';
    $dueTime = $_POST['due_time'] ?? '23:59';
    $totalPoints = intval($_POST['total_points'] ?? 100);
    
    if (empty($courseId) || empty($title) || empty($dueDate)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Verify course belongs to teacher
        $stmt = $pdo->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$courseId, $user['id']]);
        
        if (!$stmt->fetch()) {
            $error = 'Invalid course selected.';
        } else {
            // Handle file upload
            $fileName = null;
            $filePath = null;
            
            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $ext = getFileExtension($file['name']);
                
                if ($file['size'] > getMaxFileSize()) {
                    $error = 'File too large. Maximum size: 10MB';
                } else {
                    // Create upload directory
                    $uploadDir = UPLOAD_PATH . 'assignments/' . $courseId . '/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $fileName = $file['name'];
                    $uniqueName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                    $filePath = 'assignments/' . $courseId . '/' . $uniqueName;
                    
                    if (!move_uploaded_file($file['tmp_name'], UPLOAD_PATH . $filePath)) {
                        $error = 'Failed to upload file. Please try again.';
                        $filePath = null;
                        $fileName = null;
                    }
                }
            }
            
            if (!$error) {
                $dueDatetime = $dueDate . ' ' . $dueTime . ':00';
                
                $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date, total_points, file_name, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$courseId, $title, $description, $dueDatetime, $totalPoints, $fileName, $filePath])) {
                    setFlash('success', 'Assignment created successfully!');
                    header('Location: assignments.php');
                    exit;
                } else {
                    $error = 'Failed to create assignment. Please try again.';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="container my-5">
    <a href="assignments.php" class="btn btn-outline-primary mb-3">
        <i class="fas fa-arrow-left <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i> Back to Assignments
    </a>

    <h2 class="mb-4"><i class="fas fa-plus-circle text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>Create Assignment</h2>

    <?php if (empty($courses)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                <p class="text-muted mb-3">You need to create a course first.</p>
                <a href="courses.php" class="btn btn-primary">Create Course</a>
            </div>
        </div>
    <?php else: ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
            <?= sanitize($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course *</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select a course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>" <?= ($_POST['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>>
                                    <?= sanitize($course['course_code']) ?> - <?= sanitize($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="Assignment title" 
                               value="<?= sanitize($_POST['title'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description / Instructions</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Enter assignment instructions..."><?= sanitize($_POST['description'] ?? '') ?></textarea>
                </div>

                <!-- File Upload Section -->
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="fas fa-paperclip <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Attach File (Optional)</label>
                    <p class="text-muted small mb-2">
                        Upload a file for students to download (PDF, DOC, images, etc.)
                    </p>
                    <div class="border border-2 border-dashed rounded p-4 text-center" 
                         style="cursor: pointer; transition: all 0.3s;" 
                         onclick="document.getElementById('fileInput').click();" 
                         id="uploadArea">
                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
                        <p class="mb-1">Click to select a file or drag and drop</p>
                        <small class="text-muted">PDF, DOC, DOCX, TXT, ZIP, Images (Max 10MB)</small>
                        <input type="file" name="file" id="fileInput" class="d-none" 
                               accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.xls,.xlsx,.ppt,.pptx">
                    </div>
                    <p id="selectedFile" class="mt-2 text-success fw-semibold"></p>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Due Date *</label>
                        <input type="date" name="due_date" class="form-control" 
                               value="<?= $_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days')) ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Due Time</label>
                        <input type="time" name="due_time" class="form-control" 
                               value="<?= $_POST['due_time'] ?? '23:59' ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Points</label>
                        <input type="number" name="total_points" class="form-control" 
                               value="<?= $_POST['total_points'] ?? 100 ?>" min="1" max="1000">
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-2 mt-4">
                    <a href="assignments.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('fileInput').addEventListener('change', function(e) {
        if (this.files.length > 0) {
            document.getElementById('selectedFile').innerHTML = '<i class="fas fa-check-circle"></i> Selected: ' + this.files[0].name;
        }
    });

    // Drag and drop
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '#f0f7ff';
        uploadArea.style.borderColor = '#0d6efd';
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.style.backgroundColor = '';
        uploadArea.style.borderColor = '';
    });
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.style.backgroundColor = '';
        uploadArea.style.borderColor = '';
        document.getElementById('fileInput').files = e.dataTransfer.files;
        document.getElementById('selectedFile').innerHTML = '<i class="fas fa-check-circle"></i> Selected: ' + e.dataTransfer.files[0].name;
    });
    </script>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>