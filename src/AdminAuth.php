<?php

declare(strict_types=1);

namespace App;

use App\Config;

class AdminAuth
{
    private const SESSION_KEY = 'admin_authenticated';
    private const CSRF_KEY = 'admin_csrf_token';
    private const LAST_ACTIVITY_KEY = 'admin_last_activity';
    private const SESSION_TIMEOUT = 7200; // 2 hours

    /**
     * Check if admin is currently authenticated
     */
    public static function isAuthenticated(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || $_SESSION[self::SESSION_KEY] !== true) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION[self::LAST_ACTIVITY_KEY])) {
            if (time() - $_SESSION[self::LAST_ACTIVITY_KEY] > self::SESSION_TIMEOUT) {
                self::logout();
                return false;
            }
        }

        // Update last activity
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
        return true;
    }

    /**
     * Attempt to login with password
     */
    public static function login(string $password): bool
    {
        $adminPassword = Config::get('admin_password', '');

        if (empty($adminPassword)) {
            return false;
        }

        if ($password === $adminPassword) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION[self::LAST_ACTIVITY_KEY] = time();
            $_SESSION[self::CSRF_KEY] = self::generateCsrfToken();

            return true;
        }

        return false;
    }

    /**
     * Logout admin user
     */
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        unset($_SESSION[self::CSRF_KEY]);
        unset($_SESSION[self::LAST_ACTIVITY_KEY]);
    }

    /**
     * Get current CSRF token
     */
    public static function getCsrfToken(): ?string
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        if (!isset($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = self::generateCsrfToken();
        }

        return $_SESSION[self::CSRF_KEY];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if ($token === null || !isset($_SESSION[self::CSRF_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::CSRF_KEY], $token);
    }

    /**
     * Generate a new CSRF token
     */
    private static function generateCsrfToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get authentication status for API response
     */
    public static function getStatus(): array
    {
        $authenticated = self::isAuthenticated();

        return [
            'authenticated' => $authenticated,
            'csrf_token' => $authenticated ? self::getCsrfToken() : null,
        ];
    }
}
