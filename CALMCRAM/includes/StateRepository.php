<?php
/**
 * CramCalm — Load / save full client state to normalized MySQL tables
 */
require_once __DIR__ . '/db.php';

class StateRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? cram_db();
    }

    public function findOrCreateUser(?string $guestToken): array
    {
        if ($guestToken) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE guest_token = ? LIMIT 1');
            $stmt->execute([$guestToken]);
            $user = $stmt->fetch();
            if ($user) {
                return $user;
            }
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare(
            'INSERT INTO users (guest_token, nickname, onboarded) VALUES (?, ?, 0)'
        );
        $stmt->execute([$token, '']);
        $userId = (int) $this->db->lastInsertId();

        $this->ensureRelatedRows($userId);

        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function ensureRelatedRows(int $userId): void
    {
        $this->db->prepare(
            'INSERT IGNORE INTO user_settings (user_id, bypass_contacts) VALUES (?, ?)'
        )->execute([$userId, json_encode(['Mom', 'Emergency'])]);

        $this->db->prepare(
            'INSERT IGNORE INTO wisdom_trees (user_id) VALUES (?)'
        )->execute([$userId]);

        $this->db->prepare(
            'INSERT IGNORE INTO streaks (user_id) VALUES (?)'
        )->execute([$userId]);

        $this->db->prepare(
            'INSERT IGNORE INTO user_stats (user_id) VALUES (?)'
        )->execute([$userId]);

        $this->db->prepare(
            'INSERT IGNORE INTO personalities (user_id, scores) VALUES (?, ?)'
        )->execute([$userId, json_encode([
            'lastMinute' => 0,
            'grinder' => 0,
            'overplanner' => 0,
            'ghost' => 0,
        ])]);

        $this->db->prepare(
            'INSERT IGNORE INTO academic_states (user_id, state) VALUES (?, ?)'
        )->execute([$userId, 'stable']);

        $this->db->prepare(
            'INSERT IGNORE INTO ui_flags (user_id) VALUES (?)'
        )->execute([$userId]);
    }

    public function loadState(int $userId): array
    {
        $this->ensureRelatedRows($userId);

        $user = $this->fetchOne('SELECT * FROM users WHERE id = ?', [$userId]);
        $settings = $this->fetchOne('SELECT * FROM user_settings WHERE user_id = ?', [$userId]);
        $tree = $this->fetchOne('SELECT * FROM wisdom_trees WHERE user_id = ?', [$userId]);
        $streak = $this->fetchOne('SELECT * FROM streaks WHERE user_id = ?', [$userId]);
        $stats = $this->fetchOne('SELECT * FROM user_stats WHERE user_id = ?', [$userId]);
        $personality = $this->fetchOne('SELECT * FROM personalities WHERE user_id = ?', [$userId]);
        $academic = $this->fetchOne('SELECT * FROM academic_states WHERE user_id = ?', [$userId]);
        $ui = $this->fetchOne('SELECT * FROM ui_flags WHERE user_id = ?', [$userId]);

        $tasks = $this->fetchAll(
            'SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC',
            [$userId]
        );
        $sessions = $this->fetchAll(
            'SELECT * FROM focus_sessions WHERE user_id = ? ORDER BY ended_at ASC',
            [$userId]
        );
        $heatmapRows = $this->fetchAll(
            'SELECT day, focus_minutes FROM heatmap_days WHERE user_id = ?',
            [$userId]
        );
        $chat = $this->fetchAll(
            'SELECT role, text, created_at FROM chat_messages WHERE user_id = ? ORDER BY created_at ASC',
            [$userId]
        );

        $heatmapData = [];
        foreach ($heatmapRows as $row) {
            $heatmapData[$row['day']] = (int) $row['focus_minutes'];
        }

        $bypass = $this->decodeJson($settings['bypass_contacts'] ?? null, ['Mom', 'Emergency']);
        $extra = $this->decodeJson($settings['extra'] ?? null, []);
        $scores = $this->decodeJson($personality['scores'] ?? null, [
            'lastMinute' => 0,
            'grinder' => 0,
            'overplanner' => 0,
            'ghost' => 0,
        ]);

        $settingsOut = array_merge([
            'darkMode' => (bool) ($settings['dark_mode'] ?? false),
            'theme' => $settings['theme'] ?? 'auto',
            'reducedMotion' => (bool) ($settings['reduced_motion'] ?? false),
            'notifications' => (bool) ($settings['notifications'] ?? true),
            'motivation' => (bool) ($settings['motivation'] ?? true),
            'focusDuration' => (int) ($settings['focus_duration'] ?? 25),
            'breakDuration' => (int) ($settings['break_duration'] ?? 5),
            'longBreakDuration' => (int) ($settings['long_break_duration'] ?? 15),
            'fontSize' => $settings['font_size'] ?? 'normal',
            'accentColor' => $settings['accent_color'] ?? null,
            'greetingStyle' => $settings['greeting_style'] ?? 'casual',
            'iconStyle' => $settings['icon_style'] ?? 'default',
            'highContrast' => (bool) ($settings['high_contrast'] ?? false),
            'dyslexiaFont' => (bool) ($settings['dyslexia_font'] ?? false),
            'largeTouches' => (bool) ($settings['large_touches'] ?? false),
            'screenReader' => (bool) ($settings['screen_reader'] ?? false),
            'calGoogle' => (bool) ($settings['cal_google'] ?? false),
            'calApple' => (bool) ($settings['cal_apple'] ?? false),
            'autoSync' => (bool) ($settings['auto_sync'] ?? false),
            'bypassContacts' => $bypass,
        ], is_array($extra) ? $extra : []);

        return [
            'user' => [
                'nickname' => $user['nickname'] ?? '',
                'onboarded' => (bool) ($user['onboarded'] ?? false),
                'createdAt' => $this->toIso($user['created_at'] ?? null),
            ],
            'settings' => $settingsOut,
            'tasks' => array_map([$this, 'mapTask'], $tasks),
            'focusSessions' => array_map([$this, 'mapSession'], $sessions),
            'wisdomTree' => [
                'stage' => (int) ($tree['stage'] ?? 0),
                'xp' => (int) ($tree['xp'] ?? 0),
                'health' => (int) ($tree['health'] ?? 100),
                'lastWatered' => $this->toIso($tree['last_watered'] ?? null),
            ],
            'streak' => [
                'current' => (int) ($streak['current'] ?? 0),
                'best' => (int) ($streak['best'] ?? 0),
                'lastDate' => $streak['last_date'] ?? null,
            ],
            'heatmapData' => $heatmapData,
            'personality' => [
                'type' => $personality['type'] ?? null,
                'scores' => $scores,
            ],
            'academicState' => $academic['state'] ?? 'stable',
            'stats' => [
                'totalFocusMinutes' => (int) ($stats['total_focus_minutes'] ?? 0),
                'totalTasksCompleted' => (int) ($stats['total_tasks_completed'] ?? 0),
                'totalSessions' => (int) ($stats['total_sessions'] ?? 0),
            ],
            'chatHistory' => array_map(static function ($row) {
                return [
                    'role' => $row['role'],
                    'text' => $row['text'],
                    'time' => $row['created_at']
                        ? (new DateTimeImmutable($row['created_at']))->format(DateTimeInterface::ATOM)
                        : null,
                ];
            }, $chat),
            'ui' => [
                'swipeHintShown' => (bool) ($ui['swipe_hint_shown'] ?? false),
            ],
        ];
    }

    public function saveState(int $userId, array $state): void
    {
        $this->db->beginTransaction();

        try {
            $this->ensureRelatedRows($userId);

            $user = $state['user'] ?? [];
            $this->db->prepare(
                'UPDATE users SET nickname = ?, onboarded = ?, updated_at = CURRENT_TIMESTAMP(3) WHERE id = ?'
            )->execute([
                mb_substr((string) ($user['nickname'] ?? ''), 0, 20),
                !empty($user['onboarded']) ? 1 : 0,
                $userId,
            ]);

            $settings = $state['settings'] ?? [];
            $knownKeys = [
                'darkMode', 'theme', 'reducedMotion', 'notifications', 'motivation',
                'focusDuration', 'breakDuration', 'longBreakDuration', 'fontSize',
                'accentColor', 'greetingStyle', 'iconStyle', 'highContrast',
                'dyslexiaFont', 'largeTouches', 'screenReader', 'calGoogle',
                'calApple', 'autoSync', 'bypassContacts',
            ];
            $extra = [];
            foreach ($settings as $key => $value) {
                if (!in_array($key, $knownKeys, true)) {
                    $extra[$key] = $value;
                }
            }

            $this->db->prepare(
                'UPDATE user_settings SET
                    dark_mode = ?, theme = ?, reduced_motion = ?, notifications = ?, motivation = ?,
                    focus_duration = ?, break_duration = ?, long_break_duration = ?, font_size = ?,
                    accent_color = ?, greeting_style = ?, icon_style = ?, high_contrast = ?,
                    dyslexia_font = ?, large_touches = ?, screen_reader = ?, cal_google = ?,
                    cal_apple = ?, auto_sync = ?, bypass_contacts = ?, extra = ?
                 WHERE user_id = ?'
            )->execute([
                !empty($settings['darkMode']) ? 1 : 0,
                $this->enumOr($settings['theme'] ?? 'auto', ['auto', 'light', 'dark'], 'auto'),
                !empty($settings['reducedMotion']) ? 1 : 0,
                array_key_exists('notifications', $settings) ? (!empty($settings['notifications']) ? 1 : 0) : 1,
                array_key_exists('motivation', $settings) ? (!empty($settings['motivation']) ? 1 : 0) : 1,
                (int) ($settings['focusDuration'] ?? 25),
                (int) ($settings['breakDuration'] ?? 5),
                (int) ($settings['longBreakDuration'] ?? 15),
                $this->enumOr($settings['fontSize'] ?? 'normal', ['small', 'normal', 'large', 'xlarge'], 'normal'),
                $settings['accentColor'] ?? null,
                $this->enumOr($settings['greetingStyle'] ?? 'casual', ['casual', 'formal', 'fun'], 'casual'),
                $this->enumOr($settings['iconStyle'] ?? 'default', ['default', 'minimal', 'fun'], 'default'),
                !empty($settings['highContrast']) ? 1 : 0,
                !empty($settings['dyslexiaFont']) ? 1 : 0,
                !empty($settings['largeTouches']) ? 1 : 0,
                !empty($settings['screenReader']) ? 1 : 0,
                !empty($settings['calGoogle']) ? 1 : 0,
                !empty($settings['calApple']) ? 1 : 0,
                !empty($settings['autoSync']) ? 1 : 0,
                json_encode($settings['bypassContacts'] ?? ['Mom', 'Emergency']),
                $extra ? json_encode($extra) : null,
                $userId,
            ]);

            $tree = $state['wisdomTree'] ?? [];
            $this->db->prepare(
                'UPDATE wisdom_trees SET stage = ?, xp = ?, health = ?, last_watered = ? WHERE user_id = ?'
            )->execute([
                max(0, min(4, (int) ($tree['stage'] ?? 0))),
                max(0, (int) ($tree['xp'] ?? 0)),
                max(0, min(100, (int) ($tree['health'] ?? 100))),
                $this->fromIso($tree['lastWatered'] ?? null),
                $userId,
            ]);

            $streak = $state['streak'] ?? [];
            $this->db->prepare(
                'UPDATE streaks SET current = ?, best = ?, last_date = ? WHERE user_id = ?'
            )->execute([
                max(0, (int) ($streak['current'] ?? 0)),
                max(0, (int) ($streak['best'] ?? 0)),
                $this->dateOnly($streak['lastDate'] ?? null),
                $userId,
            ]);

            $stats = $state['stats'] ?? [];
            $this->db->prepare(
                'UPDATE user_stats SET total_focus_minutes = ?, total_tasks_completed = ?, total_sessions = ? WHERE user_id = ?'
            )->execute([
                max(0, (int) ($stats['totalFocusMinutes'] ?? 0)),
                max(0, (int) ($stats['totalTasksCompleted'] ?? 0)),
                max(0, (int) ($stats['totalSessions'] ?? 0)),
                $userId,
            ]);

            $personality = $state['personality'] ?? [];
            $this->db->prepare(
                'UPDATE personalities SET type = ?, scores = ?, evaluated_at = CURRENT_TIMESTAMP(3) WHERE user_id = ?'
            )->execute([
                $personality['type'] ?? null,
                json_encode($personality['scores'] ?? [
                    'lastMinute' => 0,
                    'grinder' => 0,
                    'overplanner' => 0,
                    'ghost' => 0,
                ]),
                $userId,
            ]);

            $academicState = $state['academicState'] ?? 'stable';
            $this->db->prepare(
                'UPDATE academic_states SET state = ? WHERE user_id = ?'
            )->execute([
                $this->enumOr($academicState, ['stable', 'pressure', 'crisis', 'burnout'], 'stable'),
                $userId,
            ]);

            $ui = $state['ui'] ?? [];
            $this->db->prepare(
                'UPDATE ui_flags SET swipe_hint_shown = ? WHERE user_id = ?'
            )->execute([
                !empty($ui['swipeHintShown']) ? 1 : 0,
                $userId,
            ]);

            // Replace collection tables
            $this->db->prepare('DELETE FROM chat_messages WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM heatmap_days WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM focus_sessions WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM tasks WHERE user_id = ?')->execute([$userId]);

            $taskInsert = $this->db->prepare(
                'INSERT INTO tasks
                    (id, user_id, title, description, priority, deadline, completed, completed_at, category, subtasks, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $taskIds = [];
            foreach (($state['tasks'] ?? []) as $task) {
                $id = (string) ($task['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $taskIds[$id] = true;
                $priority = $this->enumOr($task['priority'] ?? 'chill', ['chill', 'serious', 'panic'], 'chill');
                $taskInsert->execute([
                    mb_substr($id, 0, 40),
                    $userId,
                    mb_substr((string) ($task['title'] ?? ''), 0, 120),
                    isset($task['description']) ? mb_substr((string) $task['description'], 0, 500) : null,
                    $priority,
                    $this->fromIso($task['deadline'] ?? null),
                    !empty($task['completed']) ? 1 : 0,
                    $this->fromIso($task['completedAt'] ?? null),
                    mb_substr((string) ($task['category'] ?? 'general'), 0, 32),
                    json_encode($task['subtasks'] ?? []),
                    $this->fromIso($task['createdAt'] ?? null) ?? date('Y-m-d H:i:s.v'),
                ]);
            }

            $sessionInsert = $this->db->prepare(
                'INSERT INTO focus_sessions
                    (id, user_id, task_id, duration_minutes, completed_minutes, started_at, ended_at, completed)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            foreach (($state['focusSessions'] ?? []) as $session) {
                $id = (string) ($session['id'] ?? '');
                if ($id === '') {
                    continue;
                }
                $taskId = $session['taskId'] ?? null;
                if ($taskId !== null && !isset($taskIds[$taskId])) {
                    $taskId = null;
                }
                $started = $this->fromIso($session['startedAt'] ?? null) ?? date('Y-m-d H:i:s.v');
                $ended = $this->fromIso($session['endedAt'] ?? null) ?? $started;
                $sessionInsert->execute([
                    mb_substr($id, 0, 40),
                    $userId,
                    $taskId,
                    max(0, (int) ($session['duration'] ?? 0)),
                    max(0, (int) ($session['completedMinutes'] ?? 0)),
                    $started,
                    $ended,
                    !empty($session['completed']) ? 1 : 0,
                ]);
            }

            $heatmapInsert = $this->db->prepare(
                'INSERT INTO heatmap_days (user_id, day, focus_minutes) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE focus_minutes = VALUES(focus_minutes)'
            );
            foreach (($state['heatmapData'] ?? []) as $day => $minutes) {
                $dayOnly = $this->dateOnly((string) $day);
                if (!$dayOnly) {
                    continue;
                }
                $heatmapInsert->execute([$userId, $dayOnly, max(0, (int) $minutes)]);
            }

            $chatInsert = $this->db->prepare(
                'INSERT INTO chat_messages (user_id, role, text, created_at) VALUES (?, ?, ?, ?)'
            );
            $history = array_slice($state['chatHistory'] ?? [], -100);
            foreach ($history as $msg) {
                $role = ($msg['role'] ?? '') === 'ai' ? 'ai' : 'user';
                $text = (string) ($msg['text'] ?? '');
                if ($text === '') {
                    continue;
                }
                $chatInsert->execute([
                    $userId,
                    $role,
                    $text,
                    $this->fromIso($msg['time'] ?? null) ?? date('Y-m-d H:i:s.v'),
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function clearUserData(int $userId): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('DELETE FROM chat_messages WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM heatmap_days WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM focus_sessions WHERE user_id = ?')->execute([$userId]);
            $this->db->prepare('DELETE FROM tasks WHERE user_id = ?')->execute([$userId]);

            $this->db->prepare(
                'UPDATE users SET nickname = \'\', onboarded = 0, updated_at = CURRENT_TIMESTAMP(3) WHERE id = ?'
            )->execute([$userId]);

            $this->db->prepare(
                'UPDATE user_settings SET
                    dark_mode = 0, theme = \'auto\', reduced_motion = 0, notifications = 1, motivation = 1,
                    focus_duration = 25, break_duration = 5, long_break_duration = 15, font_size = \'normal\',
                    accent_color = NULL, greeting_style = \'casual\', icon_style = \'default\',
                    high_contrast = 0, dyslexia_font = 0, large_touches = 0, screen_reader = 0,
                    cal_google = 0, cal_apple = 0, auto_sync = 0,
                    bypass_contacts = ?, extra = NULL
                 WHERE user_id = ?'
            )->execute([json_encode(['Mom', 'Emergency']), $userId]);

            $this->db->prepare(
                'UPDATE wisdom_trees SET stage = 0, xp = 0, health = 100, last_watered = NULL WHERE user_id = ?'
            )->execute([$userId]);

            $this->db->prepare(
                'UPDATE streaks SET current = 0, best = 0, last_date = NULL WHERE user_id = ?'
            )->execute([$userId]);

            $this->db->prepare(
                'UPDATE user_stats SET total_focus_minutes = 0, total_tasks_completed = 0, total_sessions = 0 WHERE user_id = ?'
            )->execute([$userId]);

            $this->db->prepare(
                'UPDATE personalities SET type = NULL, scores = ?, evaluated_at = NULL WHERE user_id = ?'
            )->execute([json_encode([
                'lastMinute' => 0,
                'grinder' => 0,
                'overplanner' => 0,
                'ghost' => 0,
            ]), $userId]);

            $this->db->prepare(
                'UPDATE academic_states SET state = \'stable\' WHERE user_id = ?'
            )->execute([$userId]);

            $this->db->prepare(
                'UPDATE ui_flags SET swipe_hint_shown = 0 WHERE user_id = ?'
            )->execute([$userId]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function mapTask(array $row): array
    {
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'] ?? '',
            'priority' => $row['priority'],
            'deadline' => $this->toIso($row['deadline'] ?? null),
            'completed' => (bool) $row['completed'],
            'completedAt' => $this->toIso($row['completed_at'] ?? null),
            'createdAt' => $this->toIso($row['created_at'] ?? null),
            'category' => $row['category'] ?? 'general',
            'subtasks' => $this->decodeJson($row['subtasks'] ?? null, []),
        ];
    }

    private function mapSession(array $row): array
    {
        return [
            'id' => $row['id'],
            'duration' => (int) $row['duration_minutes'],
            'completedMinutes' => (int) $row['completed_minutes'],
            'taskId' => $row['task_id'],
            'startedAt' => $this->toIso($row['started_at'] ?? null),
            'endedAt' => $this->toIso($row['ended_at'] ?? null),
            'completed' => (bool) $row['completed'],
        ];
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function decodeJson($value, $default)
    {
        if ($value === null || $value === '') {
            return $default;
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode((string) $value, true);
        return $decoded === null ? $default : $decoded;
    }

    private function toIso(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return (new DateTimeImmutable($value))->format('Y-m-d\TH:i:s.v\Z');
        } catch (Exception $e) {
            return null;
        }
    }

    private function fromIso($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            $dt = new DateTimeImmutable((string) $value);
            return $dt->format('Y-m-d H:i:s.v');
        } catch (Exception $e) {
            return null;
        }
    }

    private function dateOnly($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return (new DateTimeImmutable((string) $value))->format('Y-m-d');
        } catch (Exception $e) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value, $m)) {
                return substr($m[0], 0, 10);
            }
            return null;
        }
    }

    private function enumOr($value, array $allowed, string $default): string
    {
        $value = (string) $value;
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
