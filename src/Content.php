<?php

declare(strict_types=1);

namespace App;

class Content
{
    private string $contentDir;

    public function __construct(string $contentDir)
    {
        $this->contentDir = rtrim($contentDir, '/');
    }

    /**
     * Get top-level sections (for tabs)
     */
    public function getSections(): array
    {
        $sections = [];
        $entries = scandir($this->contentDir);

        if ($entries === false) {
            return [];
        }

        $entries = array_filter($entries, fn($e) => $e !== '.' && $e !== '..');
        sortByPrefix($entries);

        foreach ($entries as $entry) {
            $fullPath = $this->contentDir . '/' . $entry;
            if (is_dir($fullPath)) {
                $sections[] = [
                    'name' => getDisplayName($entry),
                    'slug' => getSlug($entry),
                ];
            }
        }

        return $sections;
    }

    /**
     * Build the sidebar tree from the content directory
     * Optionally filter to a specific section
     */
    public function getTree(?string $section = null): array
    {
        if ($section) {
            // Find the actual directory name for this section
            $entries = scandir($this->contentDir);
            if ($entries === false) {
                return [];
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $fullPath = $this->contentDir . '/' . $entry;
                if (is_dir($fullPath) && getSlug($entry) === $section) {
                    return $this->buildTree($fullPath, $section);
                }
            }
            return [];
        }

        return $this->buildTree($this->contentDir, '');
    }

    /**
     * Recursively build tree structure
     */
    private function buildTree(string $dir, string $basePath): array
    {
        $items = [];
        $entries = scandir($dir);

        if ($entries === false) {
            return [];
        }

        // Filter and sort entries
        $entries = array_filter($entries, fn($e) => $e !== '.' && $e !== '..');
        sortByPrefix($entries);

        foreach ($entries as $entry) {
            $fullPath = $dir . '/' . $entry;
            $relativePath = $basePath ? $basePath . '/' . getSlug($entry) : getSlug($entry);

            if (is_dir($fullPath)) {
                $children = $this->buildTree($fullPath, $relativePath);

                // Only add directory if it has children
                if (!empty($children)) {
                    $items[] = [
                        'type' => 'dir',
                        'name' => getDisplayName($entry),
                        'slug' => $relativePath,
                        'children' => $children,
                    ];
                }
            } elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'md') {
                // Skip index.md files in sidebar (they're accessed via parent slug)
                if (pathinfo($entry, PATHINFO_FILENAME) === 'index') {
                    continue;
                }

                $items[] = [
                    'type' => 'file',
                    'name' => getDisplayName($entry),
                    'slug' => $relativePath,
                ];
            }
        }

        return $items;
    }

    /**
     * Get document content by path
     */
    public function getDoc(string $path): ?array
    {
        if (!validatePath($path, $this->contentDir)) {
            return null;
        }

        $resolved = $this->resolvePath($path);

        if ($resolved === null || !file_exists($resolved['path'])) {
            return null;
        }

        $markdown = file_get_contents($resolved['path']);

        if ($markdown === false) {
            return null;
        }

        return [
            'slug' => $path,
            'title' => $this->extractTitle($markdown) ?? getDisplayName(basename($resolved['path'])),
            'markdown' => $markdown,
            'isIndex' => $resolved['isIndex'],
        ];
    }

    /**
     * Resolve URL path to actual file path
     * Handles both direct files and index.md in directories
     * Returns array with 'path' and 'isIndex' keys, or null if not found
     */
    private function resolvePath(string $path): ?array
    {
        if (empty($path) || $path === 'index') {
            $indexPath = $this->contentDir . '/index.md';
            return file_exists($indexPath) ? ['path' => $indexPath, 'isIndex' => true] : null;
        }

        // Try to find the file, handling numeric prefixes
        $parts = explode('/', $path);
        $currentDir = $this->contentDir;

        foreach ($parts as $i => $part) {
            $found = false;
            $entries = scandir($currentDir);

            if ($entries === false) {
                return null;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $slug = getSlug($entry);
                $fullPath = $currentDir . '/' . $entry;

                if ($slug === $part) {
                    if ($i === count($parts) - 1) {
                        // Last part - could be file or directory
                        if (is_dir($fullPath)) {
                            // Check for index.md in directory
                            $indexPath = $fullPath . '/index.md';
                            return file_exists($indexPath) ? ['path' => $indexPath, 'isIndex' => true] : null;
                        } elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'md') {
                            return ['path' => $fullPath, 'isIndex' => false];
                        }
                    } else {
                        // Not last part - must be directory
                        if (is_dir($fullPath)) {
                            $currentDir = $fullPath;
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if (!$found && $i < count($parts) - 1) {
                return null;
            }
        }

        return null;
    }

    /**
     * Extract first H1 heading as title
     */
    public function extractTitle(string $markdown): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $markdown, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Get the content directory path
     */
    public function getContentDir(): string
    {
        return $this->contentDir;
    }

    /**
     * Resolve slug path to actual filesystem path for write operations
     * Returns the full filesystem path or null if invalid/not found
     */
    public function resolveWritePath(string $slugPath): ?array
    {
        if (empty($slugPath)) {
            return null;
        }

        // Validate path
        if (!validatePathForWrite($slugPath, $this->contentDir)) {
            return null;
        }

        $parts = explode('/', $slugPath);
        $currentDir = $this->contentDir;
        $realPath = [];

        foreach ($parts as $i => $part) {
            $found = false;
            $entries = scandir($currentDir);

            if ($entries === false) {
                // Directory doesn't exist yet - this might be a new file path
                break;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $slug = getSlug($entry);
                $fullPath = $currentDir . '/' . $entry;

                if ($slug === $part) {
                    $realPath[] = $entry;

                    if ($i === count($parts) - 1) {
                        // Last part - return the path
                        if (is_dir($fullPath)) {
                            // Directory - look for index.md
                            $indexPath = $fullPath . '/index.md';
                            return [
                                'path' => $indexPath,
                                'realPath' => implode('/', $realPath) . '/index.md',
                                'exists' => file_exists($indexPath),
                                'isIndex' => true,
                                'isDirectory' => false,
                            ];
                        } elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'md') {
                            return [
                                'path' => $fullPath,
                                'realPath' => implode('/', $realPath),
                                'exists' => true,
                                'isIndex' => false,
                                'isDirectory' => false,
                            ];
                        }
                    } else {
                        if (is_dir($fullPath)) {
                            $currentDir = $fullPath;
                            $found = true;
                            break;
                        }
                    }
                }
            }

            if (!$found && $i < count($parts) - 1) {
                // Parent directory not found
                return null;
            }
        }

        return null;
    }

    /**
     * Get the next available numeric prefix for a directory
     */
    public function getNextPrefix(string $directory): string
    {
        $fullPath = $this->contentDir;

        if (!empty($directory)) {
            // Resolve the directory path
            $resolved = $this->resolveDirectoryPath($directory);
            if ($resolved === null) {
                return '01';
            }
            $fullPath = $resolved;
        }

        if (!is_dir($fullPath)) {
            return '01';
        }

        $entries = scandir($fullPath);
        if ($entries === false) {
            return '01';
        }

        $maxPrefix = 0;
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (preg_match('/^(\d+)-/', $entry, $matches)) {
                $prefix = (int) $matches[1];
                if ($prefix > $maxPrefix) {
                    $maxPrefix = $prefix;
                }
            }
        }

        return str_pad((string) ($maxPrefix + 1), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Check if a slug path exists (file or directory)
     */
    public function pathExists(string $slugPath): bool
    {
        $resolved = $this->resolvePath($slugPath);
        return $resolved !== null && file_exists($resolved['path']);
    }

    /**
     * Check if a directory at slug path is empty
     */
    public function isDirectoryEmpty(string $slugPath): bool
    {
        $dirPath = $this->resolveDirectoryPath($slugPath);

        if ($dirPath === null || !is_dir($dirPath)) {
            return false;
        }

        $entries = scandir($dirPath);
        if ($entries === false) {
            return false;
        }

        $entries = array_filter($entries, fn($e) => $e !== '.' && $e !== '..');
        return empty($entries);
    }

    /**
     * Resolve slug path to directory path
     */
    public function resolveDirectoryPath(string $slugPath): ?string
    {
        if (empty($slugPath)) {
            return $this->contentDir;
        }

        $parts = explode('/', $slugPath);
        $currentDir = $this->contentDir;

        foreach ($parts as $part) {
            $entries = scandir($currentDir);
            if ($entries === false) {
                return null;
            }

            $found = false;
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $fullPath = $currentDir . '/' . $entry;
                if (is_dir($fullPath) && getSlug($entry) === $part) {
                    $currentDir = $fullPath;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return null;
            }
        }

        return $currentDir;
    }

    /**
     * Get the actual filename (with prefix) for a slug within a directory
     */
    public function getActualFilename(string $directory, string $slug): ?string
    {
        $dirPath = $this->resolveDirectoryPath($directory);

        if ($dirPath === null || !is_dir($dirPath)) {
            return null;
        }

        $entries = scandir($dirPath);
        if ($entries === false) {
            return null;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (getSlug($entry) === $slug) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Get file tree for a section (used by API)
     */
    public function getFileTree(?string $section = null): array
    {
        return $this->getTree($section);
    }
}
