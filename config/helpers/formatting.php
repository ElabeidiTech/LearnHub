<?php
function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

function formatDateShort($date) {
    if (empty($date)) return '';
    return date('M d, Y', strtotime($date));
}

function isOverdue($dueDate) {
    if (empty($dueDate)) return false;
    return strtotime($dueDate) < time();
}

function getTimeRemaining($dueDate) {
    if (empty($dueDate)) return '';
    
    $diff = strtotime($dueDate) - time();
    
    if ($diff < 0) return 'Overdue';
    if ($diff < 60) return 'Less than a minute';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '');
    
    $days = floor($diff / 86400);
    return $days . ' day' . ($days > 1 ? 's' : '');
}

function formatFileSize($bytes, $decimals = 2) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, $decimals) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, $decimals) . ' MB';
    return round($bytes / 1073741824, $decimals) . ' GB';
}

function formatPercentage($value, $total, $decimals = 1) {
    if ($total == 0) return '0%';
    return round(($value / $total) * 100, $decimals) . '%';
}

function getGradeLetter($percentage) {
    if ($percentage >= 90) return 'A';
    if ($percentage >= 80) return 'B';
    if ($percentage >= 70) return 'C';
    if ($percentage >= 60) return 'D';
    return 'F';
}

function getGradeColorClass($percentage) {
    if ($percentage >= 90) return 'success';
    if ($percentage >= 80) return 'info';
    if ($percentage >= 70) return 'warning';
    return 'danger';
}

function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

function nl2br_safe($text) {
    return nl2br(sanitize($text));
}
?>
