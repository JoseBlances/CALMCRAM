<?php
/**
 * Quick health check for DB connectivity
 * Visit: /CALMCRAM/api/health.php
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = cram_db();
    $pdo->query('SELECT 1 FROM users LIMIT 1');
    echo json_encode([
        'ok' => true,
        'database' => DB_NAME,
        'message' => 'Connected to MySQL',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'hint' => 'Import database/schema.sql via phpMyAdmin, then verify DB_* in config.php',
    ]);
}
