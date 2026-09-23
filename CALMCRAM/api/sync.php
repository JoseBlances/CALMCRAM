<?php
/**
 * CramCalm Sync API
 *
 * GET    — load (or create) guest user state
 * POST   — save full client state
 * DELETE — reset user data (Settings → Clear)
 *
 * Identity: X-Guest-Token header or ?token= query / JSON body
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Guest-Token');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/StateRepository.php';

function json_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function guest_token_from_request(array $body = []): ?string
{
    $header = $_SERVER['HTTP_X_GUEST_TOKEN'] ?? '';
    if ($header !== '') {
        return preg_replace('/[^a-f0-9]/', '', strtolower($header)) ?: null;
    }
    if (!empty($_GET['token'])) {
        return preg_replace('/[^a-f0-9]/', '', strtolower((string) $_GET['token'])) ?: null;
    }
    if (!empty($body['token'])) {
        return preg_replace('/[^a-f0-9]/', '', strtolower((string) $body['token'])) ?: null;
    }
    return null;
}

try {
    $repo = new StateRepository();
    $method = $_SERVER['REQUEST_METHOD'];
    $body = in_array($method, ['POST', 'DELETE'], true) ? read_json_body() : [];
    $token = guest_token_from_request($body);
    $user = $repo->findOrCreateUser($token);

    if ($method === 'GET') {
        $state = $repo->loadState((int) $user['id']);
        json_response([
            'ok' => true,
            'token' => $user['guest_token'],
            'userId' => (int) $user['id'],
            'state' => $state,
        ]);
    }

    if ($method === 'POST') {
        $state = $body['state'] ?? null;
        if (!is_array($state)) {
            json_response(['ok' => false, 'error' => 'Missing state object'], 400);
        }
        $repo->saveState((int) $user['id'], $state);
        json_response([
            'ok' => true,
            'token' => $user['guest_token'],
            'userId' => (int) $user['id'],
            'savedAt' => gmdate('c'),
        ]);
    }

    if ($method === 'DELETE') {
        $repo->clearUserData((int) $user['id']);
        json_response([
            'ok' => true,
            'token' => $user['guest_token'],
            'userId' => (int) $user['id'],
        ]);
    }

    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
} catch (PDOException $e) {
    json_response([
        'ok' => false,
        'error' => 'Database error. Import database/schema.sql in phpMyAdmin and check config.php credentials.',
        'detail' => $e->getMessage(),
    ], 500);
} catch (Throwable $e) {
    json_response([
        'ok' => false,
        'error' => $e->getMessage(),
    ], 500);
}
