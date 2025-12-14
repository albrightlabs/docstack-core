<?php

declare(strict_types=1);

namespace App;

class Api
{
    private Content $content;
    private FileOperations $fileOps;

    public function __construct(Content $content, FileOperations $fileOps)
    {
        $this->content = $content;
        $this->fileOps = $fileOps;
    }

    /**
     * Handle API request
     */
    public function handleRequest(string $method, string $path): void
    {
        // Set JSON content type
        header('Content-Type: application/json');

        // Parse the path
        $parts = explode('/', trim($path, '/'));
        $endpoint = $parts[0] ?? '';

        try {
            switch ($endpoint) {
                case 'auth':
                    $this->handleAuth($method, $parts[1] ?? '');
                    break;

                case 'files':
                    $this->handleFiles($method, array_slice($parts, 1));
                    break;

                default:
                    $this->error('Unknown endpoint', 404);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Handle authentication endpoints
     */
    private function handleAuth(string $method, string $action): void
    {
        switch ($action) {
            case 'status':
                if ($method !== 'GET') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                $this->json(AdminAuth::getStatus());
                break;

            case 'login':
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                $data = $this->getJsonInput();
                $password = $data['password'] ?? '';

                if (AdminAuth::login($password)) {
                    $this->json([
                        'success' => true,
                        'csrf_token' => AdminAuth::getCsrfToken(),
                    ]);
                } else {
                    $this->error('Invalid password', 401);
                }
                break;

            case 'logout':
                if ($method !== 'POST') {
                    $this->error('Method not allowed', 405);
                    return;
                }
                AdminAuth::logout();
                $this->json(['success' => true]);
                break;

            default:
                $this->error('Unknown auth action', 404);
        }
    }

    /**
     * Handle file operations endpoints
     */
    private function handleFiles(string $method, array $pathParts): void
    {
        // Check for special actions
        $lastPart = end($pathParts);
        $isMove = $lastPart === 'move';

        if ($isMove) {
            array_pop($pathParts);
        }

        $filePath = implode('/', $pathParts);

        switch ($method) {
            case 'GET':
                if (empty($filePath)) {
                    $this->getFileTree();
                } else {
                    $this->getFile($filePath);
                }
                break;

            case 'POST':
                $this->requireAuth();
                if ($isMove) {
                    $this->moveFile($filePath);
                } else {
                    $this->createFile($filePath);
                }
                break;

            case 'PUT':
                $this->requireAuth();
                $this->updateFile($filePath);
                break;

            case 'DELETE':
                $this->requireAuth();
                $this->deleteFile($filePath);
                break;

            default:
                $this->error('Method not allowed', 405);
        }
    }

    /**
     * Get file tree
     */
    private function getFileTree(): void
    {
        $sections = $this->content->getSections();
        $tree = [];

        foreach ($sections as $section) {
            $tree[] = [
                'type' => 'section',
                'name' => $section['name'],
                'slug' => $section['slug'],
                'children' => $this->content->getFileTree($section['slug']),
            ];
        }

        $this->json([
            'success' => true,
            'data' => $tree,
        ]);
    }

    /**
     * Get file content
     */
    private function getFile(string $path): void
    {
        // Resolve the path
        $resolved = $this->content->resolveWritePath($path);

        if ($resolved === null) {
            // Try getting the doc normally
            $doc = $this->content->getDoc($path);
            if ($doc === null) {
                $this->error('File not found', 404);
                return;
            }

            $this->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'content' => $doc['markdown'],
                    'title' => $doc['title'],
                    'isIndex' => $doc['isIndex'] ?? false,
                ],
            ]);
            return;
        }

        if (!$resolved['exists']) {
            $this->error('File not found', 404);
            return;
        }

        $content = file_get_contents($resolved['path']);
        if ($content === false) {
            $this->error('Failed to read file', 500);
            return;
        }

        $this->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'realPath' => $resolved['realPath'],
                'content' => $content,
                'isIndex' => $resolved['isIndex'],
                'lastModified' => date('c', filemtime($resolved['path'])),
            ],
        ]);
    }

    /**
     * Create a new file or directory
     */
    private function createFile(string $path): void
    {
        $data = $this->getJsonInput();
        $this->validateCsrf($data['csrf_token'] ?? null);

        $type = $data['type'] ?? 'file';
        $content = $data['content'] ?? '';
        $filename = $data['filename'] ?? null;

        // Get parent directory path
        $pathParts = explode('/', $path);
        $newName = array_pop($pathParts);
        $parentPath = implode('/', $pathParts);

        // Resolve parent directory
        $parentDir = $this->content->resolveDirectoryPath($parentPath);
        if ($parentDir === null) {
            $this->error('Parent directory not found', 404);
            return;
        }

        // Generate filename with prefix
        $prefix = $this->content->getNextPrefix($parentPath);
        $slug = sanitizeFilename(titleToSlug($newName));

        if ($type === 'directory') {
            $actualName = $prefix . '-' . $slug;
            $fullPath = $parentDir . '/' . $actualName;

            if (file_exists($fullPath)) {
                $this->error('Directory already exists', 409);
                return;
            }

            if (!mkdir($fullPath, 0755, true)) {
                $this->error('Failed to create directory', 500);
                return;
            }

            $this->json([
                'success' => true,
                'data' => [
                    'path' => $parentPath . '/' . $slug,
                    'realPath' => $actualName,
                    'type' => 'directory',
                ],
            ]);
        } else {
            // Create file
            $actualFilename = $filename ?? ($prefix . '-' . $slug . '.md');
            $fullPath = $parentDir . '/' . $actualFilename;

            if (file_exists($fullPath)) {
                $this->error('File already exists', 409);
                return;
            }

            // Default content if empty
            if (empty($content)) {
                $title = toTitleCase($newName);
                $content = "# {$title}\n\n";
            }

            if (!$this->fileOps->writeFile(substr($fullPath, strlen($this->content->getContentDir()) + 1), $content)) {
                $this->error('Failed to create file', 500);
                return;
            }

            $this->json([
                'success' => true,
                'data' => [
                    'path' => $parentPath . '/' . $slug,
                    'realPath' => ($parentPath ? $parentPath . '/' : '') . $actualFilename,
                    'type' => 'file',
                ],
            ]);
        }
    }

    /**
     * Update file content
     */
    private function updateFile(string $path): void
    {
        $data = $this->getJsonInput();
        $this->validateCsrf($data['csrf_token'] ?? null);

        $content = $data['content'] ?? null;

        if ($content === null) {
            $this->error('Content is required', 400);
            return;
        }

        // Resolve the path
        $resolved = $this->content->resolveWritePath($path);

        if ($resolved === null || !$resolved['exists']) {
            $this->error('File not found', 404);
            return;
        }

        // Check if it's a system file
        if (isSystemFile(basename($resolved['path']))) {
            $this->error('Cannot modify system files', 403);
            return;
        }

        // Get relative path for FileOperations
        $relativePath = substr($resolved['path'], strlen($this->content->getContentDir()) + 1);

        if (!$this->fileOps->writeFile($relativePath, $content)) {
            $this->error('Failed to save file', 500);
            return;
        }

        $this->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'lastModified' => date('c'),
            ],
        ]);
    }

    /**
     * Delete file or directory
     */
    private function deleteFile(string $path): void
    {
        $data = $this->getJsonInput();
        $this->validateCsrf($data['csrf_token'] ?? null);

        // First try to resolve as a file
        $resolved = $this->content->resolveWritePath($path);

        if ($resolved !== null && $resolved['exists']) {
            // It's a file
            if (isSystemFile(basename($resolved['path']))) {
                $this->error('Cannot delete system files', 403);
                return;
            }

            $relativePath = substr($resolved['path'], strlen($this->content->getContentDir()) + 1);

            if (!$this->fileOps->deleteFile($relativePath)) {
                $this->error('Failed to delete file', 500);
                return;
            }

            $this->json(['success' => true]);
            return;
        }

        // Try as directory
        $dirPath = $this->content->resolveDirectoryPath($path);

        if ($dirPath === null) {
            $this->error('File or directory not found', 404);
            return;
        }

        // Check if directory is empty
        if (!$this->content->isDirectoryEmpty($path)) {
            $this->error('Directory is not empty', 409);
            return;
        }

        $relativePath = substr($dirPath, strlen($this->content->getContentDir()) + 1);

        if (!$this->fileOps->deleteDirectory($relativePath)) {
            $this->error('Failed to delete directory', 500);
            return;
        }

        $this->json(['success' => true]);
    }

    /**
     * Move/rename file or directory
     */
    private function moveFile(string $path): void
    {
        $data = $this->getJsonInput();
        $this->validateCsrf($data['csrf_token'] ?? null);

        $destination = $data['destination'] ?? null;
        $newFilename = $data['newFilename'] ?? null;

        if ($destination === null && $newFilename === null) {
            $this->error('Destination or newFilename is required', 400);
            return;
        }

        // Resolve source path
        $resolved = $this->content->resolveWritePath($path);
        $isFile = $resolved !== null && $resolved['exists'];

        $sourcePath = null;
        if ($isFile) {
            $sourcePath = $resolved['path'];
        } else {
            $dirPath = $this->content->resolveDirectoryPath($path);
            if ($dirPath !== null) {
                $sourcePath = $dirPath;
            }
        }

        if ($sourcePath === null) {
            $this->error('Source not found', 404);
            return;
        }

        // Determine destination path
        if ($newFilename !== null) {
            // Just renaming in place
            $parentDir = dirname($sourcePath);
            $destPath = $parentDir . '/' . sanitizeFilename($newFilename);
            if ($isFile && !str_ends_with($destPath, '.md')) {
                $destPath .= '.md';
            }
        } else {
            // Moving to new location
            $destResolved = $this->content->resolveDirectoryPath(dirname($destination));
            if ($destResolved === null) {
                $this->error('Destination directory not found', 404);
                return;
            }

            $destName = basename($destination);
            $destPath = $destResolved . '/' . sanitizeFilename($destName);
            if ($isFile && !str_ends_with($destPath, '.md')) {
                $destPath .= '.md';
            }
        }

        if (file_exists($destPath)) {
            $this->error('Destination already exists', 409);
            return;
        }

        $oldRelative = substr($sourcePath, strlen($this->content->getContentDir()) + 1);
        $newRelative = substr($destPath, strlen($this->content->getContentDir()) + 1);

        if (!$this->fileOps->move($oldRelative, $newRelative)) {
            $this->error('Failed to move', 500);
            return;
        }

        $this->json([
            'success' => true,
            'data' => [
                'oldPath' => $path,
                'newPath' => $destination ?? $path,
            ],
        ]);
    }

    /**
     * Require admin authentication
     */
    private function requireAuth(): void
    {
        if (!AdminAuth::isAuthenticated()) {
            $this->error('Authentication required', 401);
            exit;
        }
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrf(?string $token): void
    {
        if (!AdminAuth::validateCsrfToken($token)) {
            $this->error('Invalid CSRF token', 403);
            exit;
        }
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }

        $data = json_decode($input, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Send JSON response
     */
    private function json(array $data): void
    {
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Send error response
     */
    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json([
            'success' => false,
            'error' => $message,
        ]);
    }
}
