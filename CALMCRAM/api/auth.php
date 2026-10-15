<?php
/**
 * CramCalm Auth API
 *
 * POST action=register — { nickname, email, password }
 * POST action=login    — { email, password }
 * POST action=logout   — requires X-Guest-Token
 * GET  action=me       — requires X-Guest-Token
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Guest-Token');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/AuthService.php';

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

function token_from_request(array $body = []): ?string
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
    $auth = new AuthService();
    $method = $_SERVER['REQUEST_METHOD'];
    $body = $method === 'POST' ? read_json_body() : [];
    $action = $body['action'] ?? ($_GET['action'] ?? '');

    if ($method === 'GET' && ($action === 'me' || $action === '')) {
        $user = $auth->findAuthenticatedByToken(token_from_request());
        if (!$user) {
            json_response(['ok' => false, 'error' => 'Not authenticated', 'needsAuth' => true], 401);
        }
        $repo = new StateRepository();
        json_response([
            'ok' => true,
            'user' => $auth->publicUser($user),
            'token' => $user['guest_token'],
            'state' => $repo->loadState((int) $user['id']),
        ]);
    }

    if ($method !== 'POST') {
        json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
    }

    if ($action === 'register') {
        $result = $auth->register(
            (string) ($body['nickname'] ?? ''),
            (string) ($body['email'] ?? ''),
            (string) ($body['password'] ?? '')
        );
        json_response(['ok' => true] + $result);
    }

    if ($action === 'login') {
        $result = $auth->login(
            (string) ($body['email'] ?? ''),
            (string) ($body['password'] ?? '')
        );
        json_response(['ok' => true] + $result);
    }

    if ($action === 'logout') {
        $auth->logout(token_from_request($body));
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'Unknown action. Use register, login, logout, or me.'], 400);
} catch (InvalidArgumentException $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (PDOException $e) {
    json_response([
        'ok' => false,
        'error' => 'Database error. Run database/migration_auth.sql if you have not yet.',
        'detail' => $e->getMessage(),
    ], 500);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 500);
}
