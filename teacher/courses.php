<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'My Courses';
$user = getCurrentUser();

/** Handle course management POST actions (create or delete) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    /** Process course creation request */
    if ($_POST['action'] === 'create') {
        /** Extract and normalize course data - uppercase for consistency */
        $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
        $courseName = strtoupper(trim($_POST['course_name'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        
        /** Validate required fields */
        if (empty($courseCode) || empty($courseName)) {
            setFlash('danger', 'Please fill in all required fields.');
        } else {
            /** Check if course code already exists to prevent duplicates */
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE course_code = ?");
            $stmt->execute([$courseCode]);
            
            if ($stmt->fetch()) {
                setFlash('danger', 'Course code already exists.');
            } else {
                /** Create new course linked to current teacher */
                $stmt = $pdo->prepare("INSERT INTO courses (teacher_id, course_code, course_name, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$user['id'], $courseCode, $courseName, $description]);
                setFlash('success', 'Course created successfully!');
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        /** Process course deletion request */
        $courseId = $_POST['course_id'] ?? 0;
        /** Delete course only if it belongs to current teacher (security check) */
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$courseId, $user['id']]);
        setFlash('success', 'Course deleted successfully.');
    }
    
    header('Location: courses.php');
    exit;
}

/** Retrieve all courses for current teacher with student count, assignment count, and quiz count */
$stmt = $pdo->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as student_count,
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as assignment_count,
           (SELECT COUNT(*) FROM quizzes WHERE course_id = c.id) as quiz_count
    FROM courses c
    WHERE c.teacher_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$user['id']]);
$courses = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Main container for courses management page -->
<div class="container my-5">
    <!-- Page header with title and create course button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>My Courses</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Course
        </button>
    </div>

    <!-- Empty state card: shown when teacher has no courses yet -->
    <?php if (empty($courses)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-book fa-4x text-muted mb-3"></i>
                <p class="text-muted mb-3">You haven't created any courses yet.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="fas fa-plus <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Your First Course
                </button>
            </div>
        </div>
    <?php else: ?>
        <!-- Course cards grid: displays all courses with statistics and actions -->
        <div class="row g-4">
            <?php 
            $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6'];
            foreach ($courses as $index => $course): 
                $color = $colors[$index % count($colors)];
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100 course-card-hover" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <a href="course-details.php?id=<?= $course['id'] ?>" class="text-decoration-none text-dark">
                            <div class="p-4 text-white" style="background: <?= $color ?>;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <i class="fas fa-book fa-2x"></i>
                                    <form method="POST" class="d-inline" id="deleteCourseForm<?= $course['id'] ?>" onclick="event.stopPropagation();">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                        <button type="button" class="btn btn-sm text-white" style="background: rgba(255,255,255,0.2); border: none;" onclick="event.preventDefault(); event.stopPropagation(); showConfirm('Delete this course? All assignments, quizzes, and enrollments will be removed. This action cannot be undone.', function() { document.getElementById('deleteCourseForm<?= $course['id'] ?>').submit(); }, 'Delete Course')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                <h3 class="mb-0"><?= sanitize($course['course_code']) ?></h3>
                            </div>
                            <div class="card-body">
                                <h5 class="mb-2"><?= sanitize($course['course_name']) ?></h5>
                                <p class="text-muted small mb-3">
                                    <?= $course['description'] ? sanitize(substr($course['description'], 0, 100)) . '...' : 'No description' ?>
                                </p>
                                
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <span class="badge bg-primary"><?= $course['student_count'] ?> Students</span>
                                    <span class="badge bg-warning text-dark"><?= $course['assignment_count'] ?> Assignments</span>
                                    <span class="badge bg-info text-dark"><?= $course['quiz_count'] ?> Quizzes</span>
                                </div>
                                
                                <div class="bg-light p-3 rounded text-center">
                                    <small class="text-muted d-block mb-1">Share this code with students:</small>
                                <strong class="fs-5 text-primary"><?= sanitize($course['course_code']) ?></strong>
                            </div>
                        </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<!-- Create course modal: form for creating new course with code, name, and description -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="fas fa-plus-circle <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>Create New Course
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="createCourseForm">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Code *</label>
                        <input type="text" name="course_code" class="form-control" placeholder="e.g., CS301" required>
                        <small class="text-muted">Students will use this code to enroll</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course Name *</label>
                        <input type="text" name="course_name" class="form-control" placeholder="e.g., Database Systems" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Course description..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>Create Course
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>