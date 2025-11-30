<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$type = $_GET['type'] ?? 'material';
$id = $_GET['id'] ?? 0;

$filePath = null;
$fileName = null;

switch ($type) {
    case 'assignment':
        if (hasRole('student')) {
            $stmt = $pdo->prepare("
                SELECT a.file_path, a.file_name
                FROM assignments a
                JOIN courses c ON a.course_id = c.id
                JOIN enrollments e ON c.id = e.course_id
                WHERE a.id = ? AND e.student_id = ? AND a.file_path IS NOT NULL
            ");
            $stmt->execute([$id, $user['id']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT a.file_path, a.file_name
                FROM assignments a
                JOIN courses c ON a.course_id = c.id
                WHERE a.id = ? AND c.teacher_id = ? AND a.file_path IS NOT NULL
            ");
            $stmt->execute([$id, $user['id']]);
        }
        
        $result = $stmt->fetch();
        if ($result) {
            $filePath = $result['file_path'];
            $fileName = $result['file_name'];
        }
        break;

    case 'submission':
        if (hasRole('teacher')) {
            $stmt = $pdo->prepare("
                SELECT s.file_path, s.file_name
                FROM submissions s
                JOIN assignments a ON s.assignment_id = a.id
                JOIN courses c ON a.course_id = c.id
                WHERE s.id = ? AND c.teacher_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT file_path, file_name
                FROM submissions
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
        }
        
        $result = $stmt->fetch();
        if ($result) {
            $filePath = $result['file_path'];
            $fileName = $result['file_name'];
        }
        break;

    case 'material':
    default:
        if (hasRole('teacher')) {
            $stmt = $pdo->prepare("
                SELECT m.file_path, m.file_name
                FROM materials m
                JOIN courses c ON m.course_id = c.id
                WHERE m.id = ? AND c.teacher_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
        } else {
            $stmt = $pdo->prepare("
                SELECT m.file_path, m.file_name
                FROM materials m
                JOIN courses c ON m.course_id = c.id
                JOIN enrollments e ON c.id = e.course_id
                WHERE m.id = ? AND e.student_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
        }
        
        $result = $stmt->fetch();
        if ($result) {
            $filePath = $result['file_path'];
            $fileName = $result['file_name'];
        }
        break;
}

if (!$filePath || !$fileName) {
    http_response_code(404);
    die('File not found or access denied.');
}

$fullPath = UPLOAD_PATH . $filePath;

if (!file_exists($fullPath)) {
    http_response_code(404);
    die('File not found on server.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($fullPath);
exit;
?>