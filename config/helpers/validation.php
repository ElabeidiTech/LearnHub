<?php
/**
 * Sanitize user input to prevent XSS attacks
 * @param string|array $input The input to sanitize
 * @return string|array Sanitized input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get the file extension from a filename
 * @param string $filename The filename
 * @return string Lowercase file extension
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Get array of allowed file types for uploads
 * @return array Allowed file extensions
 */
function getAllowedTypes() {
    return ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'gif', 'ppt', 'pptx', 'xls', 'xlsx'];
}

/**
 * Get maximum allowed file size in bytes (10MB)
 * @return int Maximum file size in bytes
 */
function getMaxFileSize() {
    return 10 * 1024 * 1024;
}

/**
 * Validate uploaded file for type and size
 * @param array $file The uploaded file from $_FILES
 * @param array|null $allowedTypes Optional custom allowed types
 * @param int|null $maxSize Optional custom max size
 * @return array Validation result with 'valid' and 'error' keys
 */
function validateFileUpload($file, $allowedTypes = null, $maxSize = null) {
    $allowedTypes = $allowedTypes ?? getAllowedTypes();
    $maxSize = $maxSize ?? getMaxFileSize();
    
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'File upload error: ' . $file['error']];
    }
    
    if ($file['size'] > $maxSize) {
        return ['valid' => false, 'error' => 'File size exceeds maximum allowed size'];
    }
    
    $extension = getFileExtension($file['name']);
    if (!in_array($extension, $allowedTypes)) {
        return ['valid' => false, 'error' => 'File type not allowed'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate email address format
 * @param string $email The email address to validate
 * @return bool True if valid email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength requirements
 * @param string $password The password to validate
 * @param int $minLength Minimum required length (default 8)
 * @return array Validation result with 'valid' boolean and 'errors' array
 */
function validatePassword($password, $minLength = 8) {
    $errors = [];
    
    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
