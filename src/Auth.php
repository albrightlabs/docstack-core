<?php

declare(strict_types=1);

namespace App;

class Auth
{
    private const SESSION_LIFETIME = 86400; // 24 hours
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 900; // 15 minutes
    private const SESSION_KEYS = [
        'user_id',
        'user_name',
        'user_email',
        'user_role',
        'is_super_admin',
        'auth_time',
        'csrf_token',
    ];

    private static ?UserManager $userManager = null;

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Detect HTTPS for secure cookie flag
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => self::SESSION_LIFETIME,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

        // Check session timeout
        if (isset($_SESSION['auth_time'])) {
            $elapsed = time() - $_SESSION['auth_time'];
            if ($elapsed > self::SESSION_LIFETIME) {
                self::logout();
            }
        }
    }

    public static function getUserManager(): UserManager
    {
        if (self::$userManager === null) {
            self::$userManager = new UserManager();
        }
        return self::$userManager;
    }

    public static function hasAnyUsers(): bool
    {
        return self::getUserManager()->hasUsers();
    }

    public static function login(string $email, string $password): ?array
    {
        self::init();

        // Check rate limiting
        $ip = self::getClientIp();
        if (self::isRateLimited($ip)) {
            return null;
        }

        $userManager = self::getUserManager();
        $user = $userManager->verifyPassword($email, $password);

        if ($user === null) {
            self::recordFailedAttempt($ip);
            return null;
        }

        // Clear failed attempts on successful login
        self::clearFailedAttempts($ip);

        // Regenerate session ID on login for security
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_super_admin'] = $user['is_super_admin'];
        $_SESSION['auth_time'] = time();

        // Generate new CSRF token
        self::regenerateCsrfToken();

        return $user;
    }

    public static function isRateLimited(?string $ip = null): bool
    {
        $ip = $ip ?? self::getClientIp();
        $attempts = self::getFailedAttempts($ip);

        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            $elapsed = time() - $attempts['last_attempt'];
            if ($elapsed < self::LOCKOUT_DURATION) {
                return true;
            }
            // Lockout expired, clear attempts
            self::clearFailedAttempts($ip);
        }

        return false;
    }

    public static function getRateLimitRemainingTime(?string $ip = null): int
    {
        $ip = $ip ?? self::getClientIp();
        $attempts = self::getFailedAttempts($ip);

        if ($attempts['count'] >= self::MAX_LOGIN_ATTEMPTS) {
            $elapsed = time() - $attempts['last_attempt'];
            $remaining = self::LOCKOUT_DURATION - $elapsed;
            return max(0, $remaining);
        }

        return 0;
    }

    private static function getClientIp(): string
    {
        // Check for forwarded IP (behind proxy/load balancer)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private static function getRateLimitFile(): string
    {
        return Config::get('data_path', dirname(__DIR__) . '/data') . '/rate_limits.json';
    }

    private static function getFailedAttempts(string $ip): array
    {
        $file = self::getRateLimitFile();

        if (!file_exists($file)) {
            return ['count' => 0, 'last_attempt' => 0];
        }

        // Use shared lock for reading to prevent race conditions
        $fp = fopen($file, 'r');
        if ($fp === false) {
            return ['count' => 0, 'last_attempt' => 0];
        }

        flock($fp, LOCK_SH);
        $content = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        $data = json_decode($content, true) ?? [];

        // Clean up old entries while we're here
        $data = self::cleanupRateLimits($data);

        return $data[$ip] ?? ['count' => 0, 'last_attempt' => 0];
    }

    private static function recordFailedAttempt(string $ip): void
    {
        $file = self::getRateLimitFile();

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Use exclusive lock for atomic read-modify-write
        $fp = fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        flock($fp, LOCK_EX);

        $content = stream_get_contents($fp);
        $data = $content ? (json_decode($content, true) ?? []) : [];

        $data = self::cleanupRateLimits($data);

        if (!isset($data[$ip])) {
            $data[$ip] = ['count' => 0, 'last_attempt' => 0];
        }

        $data[$ip]['count']++;
        $data[$ip]['last_attempt'] = time();

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private static function clearFailedAttempts(string $ip): void
    {
        $file = self::getRateLimitFile();

        if (!file_exists($file)) {
            return;
        }

        // Use exclusive lock for atomic read-modify-write
        $fp = fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        flock($fp, LOCK_EX);

        $content = stream_get_contents($fp);
        $data = $content ? (json_decode($content, true) ?? []) : [];

        unset($data[$ip]);

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private static function cleanupRateLimits(array $data): array
    {
        $now = time();
        foreach ($data as $ip => $attempts) {
            // Remove entries older than lockout duration
            if ($now - $attempts['last_attempt'] > self::LOCKOUT_DURATION) {
                unset($data[$ip]);
            }
        }
        return $data;
    }

    public static function logout(): void
    {
        self::init();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        self::init();

        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['auth_time'])) {
            $elapsed = time() - $_SESSION['auth_time'];
            if ($elapsed > self::SESSION_LIFETIME) {
                self::logout();
                return false;
            }

            // Update last activity time
            $_SESSION['auth_time'] = time();
        }

        // Verify user still exists in database (prevents stale session issues)
        $user = self::getUserManager()->getById($_SESSION['user_id']);
        if ($user === null) {
            // User no longer exists - clear the invalid session
            self::logout();
            return false;
        }

        return true;
    }

    /**
     * Alias for check() - commonly used method name
     */
    public static function isAuthenticated(): bool
    {
        return self::check();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            self::jsonError('Unauthorized', 401);
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (!self::isAdmin()) {
            self::jsonError('Admin access required', 403);
        }
    }

    public static function canWrite(): bool
    {
        return self::canEditContent(); // Admin or Editor can write
    }

    public static function isAdmin(): bool
    {
        self::init();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public static function isReadOnly(): bool
    {
        self::init();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'readonly';
    }

    public static function isEditor(): bool
    {
        self::init();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
    }

    /**
     * Check if current user can edit content (admin or editor)
     */
    public static function canEditContent(): bool
    {
        self::init();
        return self::isAdmin() || self::isEditor();
    }

    public static function isSuperAdmin(): bool
    {
        self::init();
        return isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin'] === true;
    }

    /**
     * Check if current user can access specific content path
     */
    public static function canAccessContent(string $path): bool
    {
        if (!self::check()) {
            return false;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return false;
        }

        return self::getUserManager()->hasContentAccess($userId, $path);
    }

    /**
     * Get navigation paths for current user
     */
    public static function getNavigationPaths(): array
    {
        if (!self::check()) {
            return [];
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return [];
        }

        return self::getUserManager()->getNavigationPaths($userId);
    }

    /**
     * Check if path is navigation-only (can see but not access content)
     */
    public static function isNavOnly(string $path): bool
    {
        if (!self::check()) {
            return false;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if ($userId === null) {
            return false;
        }

        return self::getUserManager()->isNavOnly($userId, $path);
    }

    public static function getCurrentUser(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null,
            'is_super_admin' => $_SESSION['is_super_admin'] ?? false,
        ];
    }

    public static function getCurrentUserId(): ?string
    {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getCurrentUserRole(): ?string
    {
        self::init();
        return $_SESSION['user_role'] ?? null;
    }

    public static function getCsrfToken(): ?string
    {
        self::init();

        if (!isset($_SESSION['csrf_token'])) {
            self::regenerateCsrfToken();
        }

        return $_SESSION['csrf_token'] ?? null;
    }

    public static function regenerateCsrfToken(): string
    {
        self::init();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token = null): bool
    {
        self::init();

        if ($token === null) {
            // Check header first, then POST data
            $token = $_SERVER['HTTP_X_CSRF_TOKEN']
                ?? $_POST['csrf_token']
                ?? null;
        }

        if ($token === null || !isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Alias for validateCsrf()
     */
    public static function validateCsrfToken(?string $token): bool
    {
        return self::validateCsrf($token);
    }

    public static function requireCsrf(): void
    {
        if (!self::validateCsrf()) {
            self::jsonError('Invalid CSRF token', 403);
        }
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getCsrfToken() ?? '', ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function csrfMeta(): string
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(self::getCsrfToken() ?? '', ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Get authentication status for API response
     */
    public static function getStatus(): array
    {
        $authenticated = self::check();
        $user = $authenticated ? self::getCurrentUser() : null;

        return [
            'authenticated' => $authenticated,
            'user' => $user,
            'csrf_token' => $authenticated ? self::getCsrfToken() : null,
        ];
    }

    private static function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
