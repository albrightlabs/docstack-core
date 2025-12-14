<?php

declare(strict_types=1);

namespace App;

class FileOperations
{
    private string $contentDir;
    private string $backupDir;

    public function __construct(string $contentDir)
    {
        $this->contentDir = rtrim($contentDir, '/');
        $this->backupDir = dirname($contentDir) . '/.backups';
    }

    /**
     * Write content to a file safely with file locking
     */
    public function writeFile(string $path, string $content): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        // Ensure parent directory exists
        $parentDir = dirname($fullPath);
        if (!is_dir($parentDir)) {
            if (!mkdir($parentDir, 0755, true)) {
                return false;
            }
        }

        // Use file locking for atomic writes
        $fp = fopen($fullPath, 'c');
        if ($fp === false) {
            return false;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return false;
        }

        ftruncate($fp, 0);
        $written = fwrite($fp, $content);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $written !== false;
    }

    /**
     * Read file content
     */
    public function readFile(string $path): ?string
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return null;
        }

        $content = file_get_contents($fullPath);
        return $content !== false ? $content : null;
    }

    /**
     * Delete a file with backup
     */
    public function deleteFile(string $path): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!file_exists($fullPath)) {
            return false;
        }

        if (is_dir($fullPath)) {
            return $this->deleteDirectory($path);
        }

        // Create backup before delete
        $this->createBackup($path);

        return unlink($fullPath);
    }

    /**
     * Delete an empty directory
     */
    public function deleteDirectory(string $path): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!is_dir($fullPath)) {
            return false;
        }

        // Check if directory is empty (excluding . and ..)
        $entries = scandir($fullPath);
        if ($entries === false) {
            return false;
        }

        $entries = array_filter($entries, fn($e) => $e !== '.' && $e !== '..');

        if (!empty($entries)) {
            return false; // Directory not empty
        }

        return rmdir($fullPath);
    }

    /**
     * Create a directory
     */
    public function createDirectory(string $path): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (file_exists($fullPath)) {
            return false;
        }

        return mkdir($fullPath, 0755, true);
    }

    /**
     * Move or rename a file/directory
     */
    public function move(string $oldPath, string $newPath): bool
    {
        $oldFullPath = $this->contentDir . '/' . $oldPath;
        $newFullPath = $this->contentDir . '/' . $newPath;

        if (!file_exists($oldFullPath)) {
            return false;
        }

        if (file_exists($newFullPath)) {
            return false; // Destination already exists
        }

        // Ensure parent directory exists
        $parentDir = dirname($newFullPath);
        if (!is_dir($parentDir)) {
            if (!mkdir($parentDir, 0755, true)) {
                return false;
            }
        }

        return rename($oldFullPath, $newFullPath);
    }

    /**
     * Check if path exists
     */
    public function exists(string $path): bool
    {
        return file_exists($this->contentDir . '/' . $path);
    }

    /**
     * Check if path is a directory
     */
    public function isDirectory(string $path): bool
    {
        return is_dir($this->contentDir . '/' . $path);
    }

    /**
     * Check if directory is empty
     */
    public function isDirectoryEmpty(string $path): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!is_dir($fullPath)) {
            return false;
        }

        $entries = scandir($fullPath);
        if ($entries === false) {
            return false;
        }

        $entries = array_filter($entries, fn($e) => $e !== '.' && $e !== '..');
        return empty($entries);
    }

    /**
     * Get file info
     */
    public function getFileInfo(string $path): ?array
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!file_exists($fullPath)) {
            return null;
        }

        return [
            'path' => $path,
            'isDirectory' => is_dir($fullPath),
            'size' => is_file($fullPath) ? filesize($fullPath) : null,
            'lastModified' => date('c', filemtime($fullPath)),
        ];
    }

    /**
     * Create backup of a file before deletion
     */
    private function createBackup(string $path): bool
    {
        $fullPath = $this->contentDir . '/' . $path;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return false;
        }

        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            if (!mkdir($this->backupDir, 0755, true)) {
                return false;
            }
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFilename = $timestamp . '_' . str_replace('/', '_', $path);
        $backupPath = $this->backupDir . '/' . $backupFilename;

        return copy($fullPath, $backupPath);
    }

    /**
     * Get the content directory path
     */
    public function getContentDir(): string
    {
        return $this->contentDir;
    }
}
