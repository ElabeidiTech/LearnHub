<?php
/**
 * Language Configuration and Translation System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Available languages
define('AVAILABLE_LANGUAGES', [
    'en' => ['name' => 'English', 'flag' => ''],
    'fr' => ['name' => 'Français', 'flag' => ''],
    'ar' => ['name' => 'العربية', 'flag' => ''],
    'it' => ['name' => 'Italiano', 'flag' => ''],
    'de' => ['name' => 'Deutsch', 'flag' => '']
]);

// Set default language
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

// Change language if requested
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], AVAILABLE_LANGUAGES)) {
    $_SESSION['language'] = $_GET['lang'];
    // Redirect to remove lang parameter from URL
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

// Get current language
$currentLanguage = $_SESSION['language'];

// Load translation file
$translationFile = __DIR__ . '/../lang/' . $currentLanguage . '.php';
if (file_exists($translationFile)) {
    $translations = require $translationFile;
} else {
    $translations = require __DIR__ . '/../lang/en.php';
}

/**
 * Translate function
 */
function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'en';
}

/**
 * Get language direction (for RTL languages like Arabic)
 */
function getLanguageDirection() {
    $rtlLanguages = ['ar'];
    return in_array(getCurrentLanguage(), $rtlLanguages) ? 'rtl' : 'ltr';
}

/**
 * Display and clear flash message (wrapper for compatibility)
 */
function flashMessage() {
    $flash = getFlash();
    if (!$flash) {
        return '';
    }
    
    switch($flash['type']) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
        case 'danger':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
        default:
            $alertClass = 'alert-info';
    }
    
    return sprintf(
        '<div class="alert %s alert-dismissible fade show" role="alert">%s<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>',
        $alertClass,
        htmlspecialchars($flash['message'])
    );
}

