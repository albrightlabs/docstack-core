<?php

declare(strict_types=1);

namespace App;

class UserApi
{
    private UserManager $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    public function handle(string $method, string $path): void
    {
        header('Content-Type: application/json');

        // Remove /api prefix
        $path = preg_replace('#^/api#', '', $path);

        try {
            // Auth endpoints (no authentication required for login)
            if (preg_match('#^/auth/(.+)$#', $path, $matches)) {
                $this->handleAuth($method, $matches[1]);
                return;
            }

            // User management endpoints (require admin)
            if (preg_match('#^/users(?:/(.*))?$#', $path, $matches)) {
                $this->handleUsers($method, $matches[1] ?? '');
                return;
            }

            // Content tree endpoint (for permission management)
            if ($path === '/content/tree') {
                $this->handleContentTree($method);
                return;
            }

            $this->error('Not found', 404);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    private function handleAuth(string $method, string $action): void
    {
        switch ($action) {
            case 'login':
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                $data = $this->getJsonInput();
                $email = $data['email'] ?? '';
                $password = $data['password'] ?? '';

                $user = Auth::login($email, $password);
                if ($user !== null) {
                    $this->json([
                        'success' => true,
                        'user' => $user,
                        'csrf_token' => Auth::getCsrfToken(),
                    ]);
                } else {
                    $this->error('Invalid email or password', 401);
                }
                break;

            case 'logout':
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                Auth::logout();
                $this->json(['success' => true]);
                break;

            case 'status':
                if ($method !== 'GET') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                $this->json(Auth::getStatus());
                break;

            case 'me':
                if ($method !== 'GET') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                Auth::requireAuth();
                $this->json([
                    'success' => true,
                    'user' => Auth::getCurrentUser(),
                ]);
                break;

            case 'password':
                // Allow current user to change their own password
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                Auth::requireAuth();

                $data = $this->getJsonInput();

                // Validate CSRF
                if (!Auth::validateCsrf($data['csrf_token'] ?? null)) {
                    $this->error('Invalid CSRF token', 403);
                    return;
                }

                $currentPassword = $data['current_password'] ?? '';
                $newPassword = $data['new_password'] ?? '';

                if (empty($currentPassword) || empty($newPassword)) {
                    $this->error('Current password and new password are required');
                    return;
                }

                if (strlen($newPassword) < 8) {
                    $this->error('New password must be at least 8 characters');
                    return;
                }

                // Verify current password
                $userId = Auth::getCurrentUserId();
                $user = $this->userManager->getById($userId);
                if ($user === null) {
                    $this->error('User not found', 404);
                    return;
                }

                // Check current password
                $verifiedUser = $this->userManager->verifyPassword($user['email'], $currentPassword);
                if ($verifiedUser === null) {
                    $this->error('Current password is incorrect', 401);
                    return;
                }

                // Update password
                if ($this->userManager->changePassword($userId, $newPassword)) {
                    $this->json(['success' => true, 'message' => 'Password changed successfully']);
                } else {
                    $this->error('Failed to change password', 500);
                }
                break;

            default:
                $this->error('Unknown auth action', 404);
        }
    }

    private function handleUsers(string $method, string $userId): void
    {
        // All user management requires admin
        Auth::requireAdmin();

        // Validate CSRF for mutation methods
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $data = $this->getJsonInput();
            if (!Auth::validateCsrf($data['csrf_token'] ?? null)) {
                $this->error('Invalid CSRF token', 403);
                return;
            }
        }

        if (empty($userId)) {
            // Collection endpoints
            switch ($method) {
                case 'GET':
                    $users = $this->userManager->getAll();
                    $this->json(['success' => true, 'data' => $users]);
                    break;

                case 'POST':
                    $data = $this->getJsonInput();
                    $name = $data['name'] ?? '';
                    $email = $data['email'] ?? '';
                    $password = $data['password'] ?? '';
                    $role = $data['role'] ?? 'readonly';

                    if (empty(trim($name))) {
                        $this->error('Name is required');
                        return;
                    }

                    if (empty($email) || empty($password)) {
                        $this->error('Email and password are required');
                        return;
                    }

                    if (strlen($password) < 8) {
                        $this->error('Password must be at least 8 characters');
                        return;
                    }

                    try {
                        $user = $this->userManager->create($name, $email, $password, $role);
                        $this->json(['success' => true, 'data' => $user], 201);
                    } catch (\RuntimeException $e) {
                        $this->error($e->getMessage());
                    }
                    break;

                default:
                    $this->error('Method not allowed', 405);
            }
        } else {
            // Password change endpoint
            if (str_ends_with($userId, '/password')) {
                $userId = substr($userId, 0, -9);
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }

                $data = $this->getJsonInput();
                $password = $data['password'] ?? '';

                if (strlen($password) < 8) {
                    $this->error('Password must be at least 8 characters');
                    return;
                }

                if ($this->userManager->changePassword($userId, $password)) {
                    $this->json(['success' => true]);
                } else {
                    $this->error('User not found', 404);
                }
                return;
            }

            // Permissions endpoint
            if (str_ends_with($userId, '/permissions')) {
                $userId = substr($userId, 0, -12);
                $this->handleUserPermissions($method, $userId);
                return;
            }

            // Single user endpoints
            switch ($method) {
                case 'GET':
                    $user = $this->userManager->getById($userId);
                    if ($user === null) {
                        $this->error('User not found', 404);
                        return;
                    }
                    $this->json(['success' => true, 'data' => $user]);
                    break;

                case 'PUT':
                    $data = $this->getJsonInput();
                    try {
                        $user = $this->userManager->update($userId, $data);
                        if ($user === null) {
                            $this->error('User not found', 404);
                            return;
                        }
                        $this->json(['success' => true, 'data' => $user]);
                    } catch (\RuntimeException $e) {
                        $this->error($e->getMessage());
                    }
                    break;

                case 'DELETE':
                    try {
                        if ($this->userManager->delete($userId)) {
                            $this->json(['success' => true]);
                        } else {
                            $this->error('User not found', 404);
                        }
                    } catch (\RuntimeException $e) {
                        $this->error($e->getMessage());
                    }
                    break;

                default:
                    $this->error('Method not allowed', 405);
            }
        }
    }

    private function handleUserPermissions(string $method, string $userId): void
    {
        $user = $this->userManager->getById($userId);
        if ($user === null) {
            $this->error('User not found', 404);
            return;
        }

        switch ($method) {
            case 'GET':
                $permissions = $user['permissions'] ?? ['full_access' => false, 'sections' => []];
                $this->json(['success' => true, 'data' => $permissions]);
                break;

            case 'PUT':
                $data = $this->getJsonInput();

                // Validate CSRF
                if (!Auth::validateCsrf($data['csrf_token'] ?? null)) {
                    $this->error('Invalid CSRF token', 403);
                    return;
                }

                $permissions = $data['permissions'] ?? [];
                try {
                    $updatedUser = $this->userManager->updatePermissions($userId, $permissions);
                    if ($updatedUser === null) {
                        $this->error('User not found', 404);
                        return;
                    }
                    $this->json(['success' => true, 'data' => $updatedUser]);
                } catch (\RuntimeException $e) {
                    $this->error($e->getMessage());
                }
                break;

            default:
                $this->error('Method not allowed', 405);
        }
    }

    private function handleContentTree(string $method): void
    {
        Auth::requireAdmin();

        if ($method !== 'GET') {
            $this->error('Method not allowed', 405);
            return;
        }

        $contentDir = dirname(__DIR__) . '/' . Config::get('content_dir', 'content');
        $content = new Content($contentDir);
        $tree = $content->getFullTree();

        $this->json(['success' => true, 'data' => $tree]);
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }
        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    private function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
