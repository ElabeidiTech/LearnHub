<?php
/**
 * Redirect user to a specified path or URL
 * @param string $path The path or full URL to redirect to
 */
function redirect($path) {
    $url = (strpos($path, 'http') === 0) ? $path : SITE_URL . $path;
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message to be displayed on next page load
 * @param string $type The message type (success, danger, warning, info)
 * @param string $message The message text
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message from session
 * @return array|null Flash message array with 'type' and 'message' keys, or null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Generate a random security token
 * @param int $length Token length in bytes (default 32)
 * @return string Random hexadecimal token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate a unique filename to prevent overwrites and conflicts
 * @param string $originalFilename The original uploaded filename
 * @return string Unique filename with timestamp and random string
 */
function generateUniqueFilename($originalFilename) {
    $extension = getFileExtension($originalFilename);
    $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    $timestamp = time();
    $random = substr(bin2hex(random_bytes(4)), 0, 8);
    return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * Get user avatar - either profile picture or initials
 * @param array $user User data array with 'profile_picture' and 'full_name'
 * @return array Avatar data with 'type' (image/initials) and 'value' (URL/initials)
 */
function getUserAvatar($user) {
    if (!empty($user['profile_picture']) && file_exists(UPLOAD_PATH . $user['profile_picture'])) {
        return [
            'type' => 'image',
            'value' => SITE_URL . '/uploads/' . $user['profile_picture']
        ];
    }
    
    $initials = '';
    $nameParts = explode(' ', trim($user['full_name'] ?? 'U'));
    $initials = strtoupper(substr($nameParts[0], 0, 1));
    if (isset($nameParts[1])) {
        $initials .= strtoupper(substr($nameParts[1], 0, 1));
    }
    
    return [
        'type' => 'initials',
        'value' => $initials
    ];
}

/**
 * Get a random color from predefined palette
 * @param string|null $seed Optional seed for consistent color selection
 * @return string Hex color code
 */
function getRandomColor($seed = null) {
    $colors = [
        '#667eea', '#764ba2', '#f093fb', '#4facfe',
        '#43e97b', '#fa709a', '#fee140', '#30cfd0',
        '#a8edea', '#fed6e3', '#c471f5', '#ff6b6b'
    ];
    
    if ($seed !== null) {
        $index = abs(crc32($seed)) % count($colors);
        return $colors[$index];
    }
    
    return $colors[array_rand($colors)];
}

/**
 * Debug dump variable (only works when DEBUG is enabled)
 * @param mixed $var Variable to dump
 * @param bool $die Whether to exit after dumping (default false)
 */
function dd($var, $die = false) {
    if (defined('DEBUG') && DEBUG) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        if ($die) exit;
    }
}

/**
 * Check if current request is an AJAX request
 * @return bool True if AJAX request
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get the current page URL
 * @return string Full current URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if a string starts with a specific substring
 * @param string $haystack The string to check
 * @param string $needle The substring to look for
 * @return bool True if string starts with substring
 */
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Check if a string ends with a specific substring
 * @param string $haystack The string to check
 * @param string $needle The substring to look for
 * @return bool True if string ends with substring
 */
function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) return true;
    return substr($haystack, -$length) === $needle;
}
?>
