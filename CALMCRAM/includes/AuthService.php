<?php
/**
 * CramCalm — Email/password authentication
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/StateRepository.php';

class AuthService
{
    private PDO $db;
    private StateRepository $repo;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? cram_db();
        $this->repo = new StateRepository($this->db);
    }

    public function register(string $nickname, string $email, string $password): array
    {
        $nickname = mb_substr(trim($nickname), 0, 20);
        $email = $this->normalizeEmail($email);

        if ($nickname === '') {
            throw new InvalidArgumentException('Nickname is required.');
        }
        if (!$this->isValidEmail($email)) {
            throw new InvalidArgumentException('Please enter a valid email address.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        $existing = $this->findByEmail($email);
        if ($existing) {
            throw new InvalidArgumentException('An account with this email already exists.');
        }

        $token = bin2hex(random_bytes(32));
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            'INSERT INTO users (guest_token, nickname, email, password_hash, onboarded, last_login_at)
             VALUES (?, ?, ?, ?, 1, CURRENT_TIMESTAMP(3))'
        );
        $stmt->execute([$token, $nickname, $email, $hash]);
        $userId = (int) $this->db->lastInsertId();

        $this->repo->ensureRelatedRows($userId);

        $user = $this->findById($userId);
        return [
            'user' => $this->publicUser($user),
            'token' => $token,
            'state' => $this->repo->loadState($userId),
        ];
    }

    public function login(string $email, string $password): array
    {
        $email = $this->normalizeEmail($email);
        if (!$this->isValidEmail($email) || $password === '') {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        $user = $this->findByEmail($email);
        if (!$user || empty($user['password_hash'])) {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        if (!password_verify($password, $user['password_hash'])) {
            throw new InvalidArgumentException('Invalid email or password.');
        }

        // Rotate session token on login
        $token = bin2hex(random_bytes(32));
        $this->db->prepare(
            'UPDATE users SET guest_token = ?, last_login_at = CURRENT_TIMESTAMP(3), updated_at = CURRENT_TIMESTAMP(3) WHERE id = ?'
        )->execute([$token, $user['id']]);

        $user = $this->findById((int) $user['id']);

        return [
            'user' => $this->publicUser($user),
            'token' => $token,
            'state' => $this->repo->loadState((int) $user['id']),
        ];
    }

    public function logout(?string $token): void
    {
        $user = $this->findByToken($token);
        if (!$user) {
            return;
        }

        // Invalidate current session token
        $newToken = bin2hex(random_bytes(32));
        $this->db->prepare(
            'UPDATE users SET guest_token = ?, updated_at = CURRENT_TIMESTAMP(3) WHERE id = ?'
        )->execute([$newToken, $user['id']]);
    }

    public function findByToken(?string $token): ?array
    {
        if (!$token) {
            return null;
        }
        $token = preg_replace('/[^a-f0-9]/', '', strtolower($token)) ?: '';
        if (strlen($token) !== 64) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM users WHERE guest_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findAuthenticatedByToken(?string $token): ?array
    {
        $user = $this->findByToken($token);
        if (!$user || empty($user['password_hash']) || empty($user['email'])) {
            return null;
        }
        return $user;
    }

    public function findByEmail(string $email): ?array
    {
        $email = $this->normalizeEmail($email);
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function publicUser(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'nickname' => $user['nickname'] ?? '',
            'email' => $user['email'] ?? null,
            'onboarded' => (bool) ($user['onboarded'] ?? false),
            'createdAt' => $user['created_at'] ?? null,
            'lastLoginAt' => $user['last_login_at'] ?? null,
        ];
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
