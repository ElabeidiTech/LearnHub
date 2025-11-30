<?php
function redirect($path) {
    $url = (strpos($path, 'http') === 0) ? $path : SITE_URL . $path;
    header('Location: ' . $url);
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateUniqueFilename($originalFilename) {
    $extension = getFileExtension($originalFilename);
    $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    $timestamp = time();
    $random = substr(bin2hex(random_bytes(4)), 0, 8);
    return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
}

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

function dd($var, $die = false) {
    if (defined('DEBUG') && DEBUG) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        if ($die) exit;
    }
}

function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) return true;
    return substr($haystack, -$length) === $needle;
}
?>
