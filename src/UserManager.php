<?php

declare(strict_types=1);

namespace App;

class UserManager
{
    private string $dataPath;
    private array $data;

    public function __construct(?string $dataPath = null)
    {
        $this->dataPath = $dataPath ?? dirname(__DIR__) . '/data/users.json';
        $this->load();
    }

    public function hasUsers(): bool
    {
        return count($this->data['users']) > 0;
    }

    private function load(): void
    {
        if (file_exists($this->dataPath)) {
            $content = file_get_contents($this->dataPath);
            $this->data = json_decode($content, true) ?? ['users' => [], 'meta' => ['version' => 1]];
        } else {
            $this->data = ['users' => [], 'meta' => ['version' => 1]];
        }
    }

    private function save(): bool
    {
        $dir = dirname($this->dataPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($this->dataPath, $json) !== false;
    }

    public function getAll(): array
    {
        return array_map(function ($user) {
            unset($user['password_hash']);
            return $user;
        }, $this->data['users']);
    }

    public function getById(string $id): ?array
    {
        foreach ($this->data['users'] as $user) {
            if ($user['id'] === $id) {
                $result = $user;
                unset($result['password_hash']);
                return $result;
            }
        }
        return null;
    }

    public function getByEmail(string $email): ?array
    {
        $email = strtolower(trim($email));
        foreach ($this->data['users'] as $user) {
            if (strtolower($user['email']) === $email) {
                return $user;
            }
        }
        return null;
    }

    public function create(string $name, string $email, string $password, string $role = 'readonly', bool $isSuperAdmin = false): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if (empty($name)) {
            throw new \RuntimeException('Name is required');
        }

        if ($this->getByEmail($email) !== null) {
            throw new \RuntimeException('A user with this email already exists');
        }

        if (!in_array($role, ['admin', 'editor', 'readonly'])) {
            $role = 'readonly';
        }

        $user = [
            'id' => $this->generateUuid(),
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'is_super_admin' => $isSuperAdmin,
            'permissions' => [
                'full_access' => ($role === 'admin' || $role === 'editor'),
                'sections' => [],
            ],
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'last_login_at' => null,
        ];

        $this->data['users'][] = $user;
        $this->save();

        unset($user['password_hash']);
        return $user;
    }

    public function update(string $id, array $data): ?array
    {
        foreach ($this->data['users'] as &$user) {
            if ($user['id'] === $id) {
                if (isset($data['name'])) {
                    $name = trim($data['name']);
                    if (empty($name)) {
                        throw new \RuntimeException('Name is required');
                    }
                    $user['name'] = $name;
                }

                if (isset($data['email'])) {
                    $newEmail = strtolower(trim($data['email']));
                    $existing = $this->getByEmail($newEmail);
                    if ($existing !== null && $existing['id'] !== $id) {
                        throw new \RuntimeException('A user with this email already exists');
                    }
                    $user['email'] = $newEmail;
                }

                if (isset($data['role']) && !$user['is_super_admin']) {
                    if (in_array($data['role'], ['admin', 'editor', 'readonly'])) {
                        $user['role'] = $data['role'];
                    }
                }

                if (isset($data['password']) && !empty($data['password'])) {
                    $user['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }

                $user['updated_at'] = date('c');
                $this->save();

                $result = $user;
                unset($result['password_hash']);
                return $result;
            }
        }
        return null;
    }

    public function delete(string $id): bool
    {
        foreach ($this->data['users'] as $key => $user) {
            if ($user['id'] === $id) {
                if ($user['is_super_admin']) {
                    throw new \RuntimeException('Cannot delete the super admin user');
                }
                array_splice($this->data['users'], $key, 1);
                return $this->save();
            }
        }
        return false;
    }

    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->getByEmail($email);
        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        foreach ($this->data['users'] as &$u) {
            if ($u['id'] === $user['id']) {
                $u['last_login_at'] = date('c');
                break;
            }
        }
        $this->save();

        unset($user['password_hash']);
        return $user;
    }

    public function changePassword(string $id, string $newPassword): bool
    {
        foreach ($this->data['users'] as &$user) {
            if ($user['id'] === $id) {
                $user['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
                $user['updated_at'] = date('c');
                return $this->save();
            }
        }
        return false;
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(string $id, array $permissions): ?array
    {
        foreach ($this->data['users'] as &$user) {
            if ($user['id'] === $id) {
                // Validate permissions structure
                $validPermissions = [
                    'full_access' => isset($permissions['full_access']) ? (bool)$permissions['full_access'] : false,
                    'sections' => [],
                ];

                // Validate sections array
                if (isset($permissions['sections']) && is_array($permissions['sections'])) {
                    foreach ($permissions['sections'] as $section) {
                        if (is_string($section) && !empty(trim($section))) {
                            $validPermissions['sections'][] = trim($section);
                        }
                    }
                }

                $user['permissions'] = $validPermissions;
                $user['updated_at'] = date('c');
                $this->save();

                $result = $user;
                unset($result['password_hash']);
                return $result;
            }
        }
        return null;
    }

    /**
     * Check if user has access to a specific content path
     */
    public function hasContentAccess(string $userId, string $contentPath): bool
    {
        $user = null;
        foreach ($this->data['users'] as $u) {
            if ($u['id'] === $userId) {
                $user = $u;
                break;
            }
        }

        if ($user === null) {
            return false;
        }

        // Super admins and admins always have full access
        if ($user['is_super_admin'] || $user['role'] === 'admin') {
            return true;
        }

        $permissions = $user['permissions'] ?? ['full_access' => false, 'sections' => []];

        if ($permissions['full_access']) {
            return true;
        }

        // Check if contentPath matches or is within any allowed section
        $contentPath = trim($contentPath, '/');
        foreach ($permissions['sections'] as $allowedPath) {
            $allowedPath = trim($allowedPath, '/');
            // Exact match or content is within the allowed path
            if ($contentPath === $allowedPath || str_starts_with($contentPath, $allowedPath . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all paths user can navigate (includes ancestors of accessible content)
     */
    public function getNavigationPaths(string $userId): array
    {
        $user = null;
        foreach ($this->data['users'] as $u) {
            if ($u['id'] === $userId) {
                $user = $u;
                break;
            }
        }

        if ($user === null) {
            return [];
        }

        // Super admins and admins can see everything
        if ($user['is_super_admin'] || $user['role'] === 'admin') {
            return ['*'];
        }

        $permissions = $user['permissions'] ?? ['full_access' => false, 'sections' => []];

        if ($permissions['full_access']) {
            return ['*'];
        }

        // Build list of accessible paths + their ancestors
        $navPaths = [];
        foreach ($permissions['sections'] as $section) {
            $section = trim($section, '/');
            $navPaths[$section] = true;

            // Add all ancestor paths for navigation
            $parts = explode('/', $section);
            $path = '';
            foreach ($parts as $part) {
                $path = $path ? $path . '/' . $part : $part;
                $navPaths[$path] = true;
            }
        }

        return array_keys($navPaths);
    }

    /**
     * Check if a path is nav-only (user can see it for navigation but not access content)
     */
    public function isNavOnly(string $userId, string $contentPath): bool
    {
        // If user has content access, it's not nav-only
        if ($this->hasContentAccess($userId, $contentPath)) {
            return false;
        }

        // If path is in navigation paths but not content accessible, it's nav-only
        $navPaths = $this->getNavigationPaths($userId);
        if (in_array('*', $navPaths)) {
            return false;
        }

        $contentPath = trim($contentPath, '/');
        return in_array($contentPath, $navPaths);
    }
}
