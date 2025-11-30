<?php
/**
 * Language Configuration and Translation System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('AVAILABLE_LANGUAGES', [
    'en' => ['name' => 'English', 'flag' => ''],
    'fr' => ['name' => 'Français', 'flag' => ''],
    'ar' => ['name' => 'العربية', 'flag' => ''],
    'it' => ['name' => 'Italiano', 'flag' => ''],
    'de' => ['name' => 'Deutsch', 'flag' => '']
]);

if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en';
}

if (isset($_GET['lang']) && array_key_exists($_GET['lang'], AVAILABLE_LANGUAGES)) {
    $_SESSION['language'] = $_GET['lang'];
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$currentLanguage = $_SESSION['language'];

$translationFile = __DIR__ . '/../lang/' . $currentLanguage . '.php';
if (file_exists($translationFile)) {
    $translations = require $translationFile;
} else {
    $translations = require __DIR__ . '/../lang/en.php';
}

/**
 * Translate a key to the current language
 * 
 * @param string $key The translation key
 * @return string The translated string or the key if translation not found
 */
function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

/**
 * Get the current active language code
 * 
 * @return string Language code (e.g., 'en', 'fr', 'ar')
 */
function getCurrentLanguage() {
    return $_SESSION['language'] ?? 'en';
}

/**
 * Get language direction for current language
 * Used for RTL (Right-to-Left) language support like Arabic
 * 
 * @return string 'rtl' for RTL languages, 'ltr' for LTR languages
 */
function getLanguageDirection() {
    $rtlLanguages = ['ar'];
    return in_array(getCurrentLanguage(), $rtlLanguages) ? 'rtl' : 'ltr';
}

/**
 * Display and clear flash message
 * Returns HTML for Bootstrap alert component
 * 
 * @return string HTML for flash message or empty string if no message
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

