<?php
/**
 * CramCalm Configuration
 * Emotionally Intelligent Academic Survival System
 */

// App Meta
define('APP_NAME', 'CramCalm');
define('APP_VERSION', '1.0.0');
define('APP_TAGLINE', 'Your emotionally intelligent study companion');

// Base path for URL generation
define('BASE_PATH', '/CALMCRAM');
define('BASE_DIR', __DIR__);

// MySQL (XAMPP defaults — change if your phpMyAdmin user differs)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cramcalm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// API path used by the frontend sync layer
define('API_PATH', BASE_PATH . '/api');

// Load local overrides first (API keys, etc.)
$localConfig = BASE_DIR . '/config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

// Google Gemini Study Buddy defaults (override in config.local.php)
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', '');
}
if (!defined('GEMINI_MODEL')) {
    define('GEMINI_MODEL', 'gemini-2.0-flash');
}

// Available pages
define('PAGES', [
    'dashboard'    => 'Dashboard',
    'tasks'        => 'Tasks',
    'focus'        => 'Focus',
    'calendar'     => 'Calendar',
    'analytics'    => 'Analytics',
    'assistant'    => 'Study AI',
    'wisdom-tree'  => 'Wisdom Tree',
    'profile'      => 'Profile',
    'settings'     => 'Settings',
    'onboarding'   => 'Welcome',
    'login'        => 'Log in',
]);

// Navigation tabs (bottom bar)
define('NAV_TABS', [
    ['page' => 'dashboard',  'label' => 'Home',     'icon' => 'home'],
    ['page' => 'tasks',      'label' => 'Tasks',    'icon' => 'tasks'],
    ['page' => 'focus',      'label' => 'Focus',    'icon' => 'focus'],
    ['page' => 'assistant',  'label' => 'AI',       'icon' => 'assistant'],
    ['page' => 'profile',    'label' => 'Me',       'icon' => 'profile'],
]);

// Academic states
define('ACADEMIC_STATES', [
    'stable'   => ['label' => 'Stable',   'emoji' => '☀️'],
    'pressure' => ['label' => 'Pressure', 'emoji' => '⚡'],
    'crisis'   => ['label' => 'Crisis',   'emoji' => '🌊'],
    'burnout'  => ['label' => 'Burnout',  'emoji' => '🌙'],
]);

// Helper function
function asset_url($path) {
    $path = ltrim($path, '/');
    // The file's change time in the URL makes browsers fetch an edited CSS/JS file at once
    $modified = @filemtime(BASE_DIR . '/' . $path);
    return BASE_PATH . '/' . $path . ($modified ? '?v=' . $modified : '');
}

function page_url($page) {
    return BASE_PATH . '/?page=' . urlencode($page);
}

function is_current_page($page) {
    $current = $_GET['page'] ?? 'dashboard';
    return $current === $page;
}
