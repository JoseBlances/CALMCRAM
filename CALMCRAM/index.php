<?php
/**
 * CramCalm — Main Entry Point / Router
 */
require_once __DIR__ . '/config.php';

// Get requested page
$page = $_GET['page'] ?? 'dashboard';

// Sanitize page name (only alphanumeric and hyphens)
$page = preg_replace('/[^a-z0-9\-]/', '', strtolower($page));

// Check if page exists
$pagePath = BASE_DIR . '/pages/' . $page . '.php';

if (!file_exists($pagePath)) {
    $page = 'dashboard';
    $pagePath = BASE_DIR . '/pages/dashboard.php';
}

// Set page title
$pageTitle = PAGES[$page] ?? 'CramCalm';

// Determine if nav should be hidden (auth / onboarding)
$hideNav = in_array($page, ['onboarding', 'login']);

// Include shared header
require_once BASE_DIR . '/includes/header.php';

// Include the page content
require_once $pagePath;

// Include navigation (unless hidden)
if (!$hideNav) {
    require_once BASE_DIR . '/includes/nav.php';
}

// Include shared footer
require_once BASE_DIR . '/includes/footer.php';
