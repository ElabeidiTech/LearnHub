<?php
require_once '../config/config.php';
requireApprovedTeacher();

$pageTitle = 'Students';
$user = getCurrentUser();

/** Retrieve all students enrolled in teacher's courses with submission and quiz statistics */
$stmt = $pdo->prepare("
    SELECT DISTINCT u.*, 
           GROUP_CONCAT(DISTINCT c.course_code ORDER BY c.course_code SEPARATOR ', ') as courses,
           (SELECT COUNT(*) FROM submissions s 
            JOIN assignments a ON s.assignment_id = a.id 
            JOIN courses c2 ON a.course_id = c2.id 
            WHERE s.student_id = u.id AND c2.teacher_id = ?) as submission_count,
           (SELECT COUNT(*) FROM quiz_attempts qa 
            JOIN quizzes q ON qa.quiz_id = q.id 
            JOIN courses c3 ON q.course_id = c3.id 
            WHERE qa.student_id = u.id AND c3.teacher_id = ? AND qa.completed_at IS NOT NULL) as quiz_count
    FROM users u
    JOIN enrollments e ON u.id = e.student_id
    JOIN courses c ON e.course_id = c.id
    WHERE c.teacher_id = ?
    GROUP BY u.id
    ORDER BY u.full_name
");
$stmt->execute([$user['id'], $user['id'], $user['id']]);
$students = $stmt->fetchAll();

include '../includes/header.php';
?>

<!-- Main container for students list page -->
<div class="container my-5">
    <!-- Page header with title and student count badge -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-user-graduate text-primary <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i>
            <?= __('S  tudents') ?>
        </h2>
        <span class="badge bg-primary fs-6"><?= count($students) ?> <?= __('students') ?></span>
    </div>

    <!-- Students table card with enrollment details and statistics -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
                    <p class="text-muted mb-2"><?= __('no_students_enrolled') ?></p>
                    <small class="text-muted"><?= __('share_course_code_hint') ?></small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th><?= __('student') ?></th>
                                <th><?= __('student_id') ?></th>
                                <th><?= __('courses') ?></th>
                                <th><?= __('submissions') ?></th>
                                <th><?= __('quizzes') ?></th>
                                <th><?= __('joined') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center icon-circle-md">
                                                <?= strtoupper(substr($student['full_name'], 0, 2)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= sanitize($student['full_name']) ?></div>
                                                <small class="text-muted"><?= sanitize($student['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($student['student_id']): ?>
                                            <span class="badge bg-info"><?= sanitize($student['student_id']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $courseList = explode(', ', $student['courses']);
                                        foreach ($courseList as $code): 
                                        ?>
                                            <span class="badge bg-primary"><?= sanitize($code) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?= $student['submission_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= $student['quiz_count'] ?></span>
                                    </td>
                                    <td class="text-muted"><?= formatDateShort($student['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>