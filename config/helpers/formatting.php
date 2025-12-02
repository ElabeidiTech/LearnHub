<?php
/**
 * Format a date string to a readable format
 * @param string $date The date to format
 * @param string $format The desired format (default: 'M d, Y h:i A')
 * @return string Formatted date string
 */
function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format a date string to short format (no time)
 * @param string $date The date to format
 * @return string Formatted date string (M d, Y)
 */
function formatDateShort($date) {
    if (empty($date)) return '';
    return date('M d, Y', strtotime($date));
}

/**
 * Check if a due date has passed
 * @param string $dueDate The due date to check
 * @return bool True if overdue, false otherwise
 */
function isOverdue($dueDate) {
    if (empty($dueDate)) return false;
    return strtotime($dueDate) < time();
}

/**
 * Calculate and format time remaining until a due date
 * @param string $dueDate The due date
 * @return string Human-readable time remaining (e.g., '2 days', '3 hours')
 */
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

/**
 * Convert bytes to human-readable file size format
 * @param int $bytes File size in bytes
 * @param int $decimals Number of decimal places (default 2)
 * @return string Formatted file size (e.g., '2.5 MB')
 */
function formatFileSize($bytes, $decimals = 2) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, $decimals) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, $decimals) . ' MB';
    return round($bytes / 1073741824, $decimals) . ' GB';
}

/**
 * Calculate and format percentage
 * @param float $value The value
 * @param float $total The total
 * @param int $decimals Number of decimal places (default 1)
 * @return string Formatted percentage with % sign
 */
function formatPercentage($value, $total, $decimals = 1) {
    if ($total == 0) return '0%';
    return round(($value / $total) * 100, $decimals) . '%';
}

/**
 * Convert percentage to letter grade (A-F)
 * @param float $percentage The percentage score
 * @return string Letter grade
 */
function getGradeLetter($percentage) {
    if ($percentage >= 90) return 'A';
    if ($percentage >= 80) return 'B';
    if ($percentage >= 70) return 'C';
    if ($percentage >= 60) return 'D';
    return 'F';
}

/**
 * Get Bootstrap color class based on grade percentage
 * @param float $percentage The percentage score
 * @return string Bootstrap color class (success, info, warning, danger)
 */
function getGradeColorClass($percentage) {
    if ($percentage >= 90) return 'success';
    if ($percentage >= 80) return 'info';
    if ($percentage >= 70) return 'warning';
    return 'danger';
}

/**
 * Truncate text to specified length and add suffix
 * @param string $text The text to truncate
 * @param int $length Maximum length (default 100)
 * @param string $suffix Suffix to add if truncated (default '...')
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Safely convert newlines to <br> tags with XSS protection
 * @param string $text The text to convert
 * @return string Text with <br> tags and sanitized content
 */
function nl2br_safe($text) {
    return nl2br(sanitize($text));
}
?>
