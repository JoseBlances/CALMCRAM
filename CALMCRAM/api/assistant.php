<?php
/**
 * CramCalm Study Buddy — Google Gemini tutoring API
 *
 * POST JSON:
 * {
 *   "message": "user text",
 *   "history": [{ "role": "user"|"ai", "text": "..." }],
 *   "context": { nickname, tasks, stats, streak, academicState, ... },
 *   "mode": "chat" | "document"   (optional)
 * }
 *
 * Model: GEMINI_MODEL (default gemini-2.0-flash)
 * Free key: https://aistudio.google.com/apikey
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Guest-Token');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/AuthService.php';

function json_out(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$body = json_decode($raw ?: '', true);
if (!is_array($body)) {
    json_out(['ok' => false, 'error' => 'Invalid JSON body'], 400);
}

$token = $_SERVER['HTTP_X_GUEST_TOKEN'] ?? ($body['token'] ?? '');
$token = preg_replace('/[^a-f0-9]/', '', strtolower((string) $token)) ?: '';

$auth = new AuthService();
$user = $auth->findAuthenticatedByToken($token ?: null);
if (!$user) {
    json_out(['ok' => false, 'error' => 'Please log in to use Study Buddy.', 'needsAuth' => true], 401);
}

$apiKey = defined('GEMINI_API_KEY') ? trim((string) GEMINI_API_KEY) : '';
if ($apiKey === '' || str_starts_with($apiKey, 'your-gemini') || str_starts_with($apiKey, 'AIza-your')) {
    json_out([
        'ok' => false,
        'error' => 'Google AI is not configured. Add GEMINI_API_KEY to config.local.php (see config.local.php.example).',
        'needsKey' => true,
    ], 503);
}

$message = trim((string) ($body['message'] ?? ''));
if ($message === '') {
    json_out(['ok' => false, 'error' => 'Message is required'], 400);
}

$mode = ($body['mode'] ?? 'chat') === 'document' ? 'document' : 'chat';
$history = is_array($body['history'] ?? null) ? $body['history'] : [];
$context = is_array($body['context'] ?? null) ? $body['context'] : [];

$system = build_tutor_system_prompt($context, $mode);

$contents = [];
$recent = array_slice($history, -12);
foreach ($recent as $msg) {
    $text = trim((string) ($msg['text'] ?? ''));
    if ($text === '') {
        continue;
    }
    if (str_starts_with($text, '📄 Analyzing') || str_starts_with($text, '🔍') || str_starts_with($text, '⚙️')) {
        continue;
    }
    $role = ($msg['role'] ?? '') === 'ai' ? 'model' : 'user';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => mb_substr($text, 0, 4000)]],
    ];
}

$userText = $mode === 'document'
    ? "Please analyze this syllabus / study document and help me plan:\n\n" . mb_substr($message, 0, 12000)
    : mb_substr($message, 0, 4000);

// Gemini requires alternating user/model; merge consecutive same-role turns
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $userText]],
];
$contents = normalize_gemini_contents($contents);

$model = defined('GEMINI_MODEL') ? (string) GEMINI_MODEL : 'gemini-2.0-flash';
$url = 'https://generativelanguage.googleapis.com/v1beta/models/'
    . rawurlencode($model)
    . ':generateContent?key=' . rawurlencode($apiKey);

$payload = [
    'systemInstruction' => [
        'parts' => [['text' => $system]],
    ],
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 900,
    ],
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 60,
]);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$error = curl_error($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($errno) {
    json_out(['ok' => false, 'error' => 'Could not reach Google AI: ' . $error], 502);
}

$decoded = json_decode($response ?: '', true);
if ($status >= 400 || !is_array($decoded)) {
    $msg = $decoded['error']['message'] ?? ('Google AI error (HTTP ' . $status . ')');
    json_out(['ok' => false, 'error' => $msg, 'status' => $status], 502);
}

$reply = '';
$parts = $decoded['candidates'][0]['content']['parts'] ?? [];
if (is_array($parts)) {
    foreach ($parts as $part) {
        if (!empty($part['text'])) {
            $reply .= $part['text'];
        }
    }
}
$reply = trim($reply);

if ($reply === '') {
    $block = $decoded['candidates'][0]['finishReason'] ?? 'unknown';
    json_out(['ok' => false, 'error' => 'Empty response from Gemini (reason: ' . $block . ')'], 502);
}

json_out([
    'ok' => true,
    'reply' => $reply,
    'model' => $model,
    'provider' => 'google',
]);

function normalize_gemini_contents(array $contents): array
{
    $normalized = [];
    foreach ($contents as $item) {
        $role = $item['role'] === 'model' ? 'model' : 'user';
        $text = trim((string) ($item['parts'][0]['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $last = $normalized[count($normalized) - 1] ?? null;
        if ($last && $last['role'] === $role) {
            $normalized[count($normalized) - 1]['parts'][0]['text'] .= "\n" . $text;
        } else {
            $normalized[] = [
                'role' => $role,
                'parts' => [['text' => $text]],
            ];
        }
    }

    // First content must be user role for Gemini
    if ($normalized && $normalized[0]['role'] !== 'user') {
        array_unshift($normalized, [
            'role' => 'user',
            'parts' => [['text' => '(continuing our study session)']],
        ]);
    }

    return $normalized;
}

function build_tutor_system_prompt(array $context, string $mode): string
{
    $nickname = trim((string) ($context['nickname'] ?? 'Scholar'));
    if ($nickname === '') {
        $nickname = 'Scholar';
    }

    $tasks = is_array($context['tasks'] ?? null) ? $context['tasks'] : [];
    $incomplete = array_values(array_filter($tasks, static fn($t) => empty($t['completed'])));
    $taskLines = [];
    foreach (array_slice($incomplete, 0, 12) as $t) {
        $title = (string) ($t['title'] ?? 'Untitled');
        $priority = (string) ($t['priority'] ?? 'chill');
        $deadline = !empty($t['deadline']) ? (string) $t['deadline'] : 'no deadline';
        $cat = (string) ($t['category'] ?? 'general');
        $taskLines[] = "- {$title} [{$priority}] ({$cat}) due: {$deadline}";
    }
    $taskBlock = $taskLines ? implode("\n", $taskLines) : '- (no open tasks)';

    $stats = is_array($context['stats'] ?? null) ? $context['stats'] : [];
    $streak = is_array($context['streak'] ?? null) ? $context['streak'] : [];
    $academic = (string) ($context['academicState'] ?? 'stable');
    $focusMins = (int) ($stats['totalFocusMinutes'] ?? 0);
    $done = (int) ($stats['totalTasksCompleted'] ?? 0);
    $sessions = (int) ($stats['totalSessions'] ?? 0);
    $streakCur = (int) ($streak['current'] ?? 0);

    $base = <<<PROMPT
You are CramCalm Study Buddy — a warm, emotionally intelligent tutoring agent for students.
Student nickname: {$nickname}
Academic mood state: {$academic}
Streak: {$streakCur} days | Focus minutes total: {$focusMins} | Tasks completed: {$done} | Focus sessions: {$sessions}

Open tasks:
{$taskBlock}

Guidelines:
- Be a supportive tutor: explain concepts clearly, ask guiding questions, and break work into small steps.
- Prefer calm, practical advice over guilt or hustle culture.
- Use the student's real tasks/deadlines when relevant.
- Keep replies concise (usually under 180 words) unless the student asks for depth.
- Use light emoji sparingly when it helps tone.
- If asked to make a study plan, prioritize panic → serious → chill tasks and suggest Focus Mode (Pomodoro-style).
- Never invent grades, school policies, or personal facts you weren't given.
- If you lack enough subject detail, ask one clarifying question, then still give a useful starter answer.
PROMPT;

    if ($mode === 'document') {
        $base .= "\n\nThe user pasted syllabus/document text. Extract important dates, assignments, exams, and topics. Suggest a simple milestone study plan. Mark uncertainty clearly if dates are ambiguous.";
    }

    return $base;
}
