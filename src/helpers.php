<?php

declare(strict_types=1);

/**
 * Strip numeric prefix from filename (e.g., "01-getting-started" -> "getting-started")
 */
function stripPrefix(string $name): string
{
    return preg_replace('/^\d+-/', '', $name);
}

/**
 * Convert kebab-case or snake_case to Title Case
 */
function toTitleCase(string $name): string
{
    $name = str_replace(['_', '-'], ' ', $name);
    return ucwords($name);
}

/**
 * Get display name from filename (strip prefix, extension, and convert to title case)
 */
function getDisplayName(string $filename): string
{
    // Remove extension
    $name = pathinfo($filename, PATHINFO_FILENAME);
    // Strip numeric prefix
    $name = stripPrefix($name);
    // Convert to title case
    return toTitleCase($name);
}

/**
 * Get slug from filename (strip prefix and extension, keep kebab-case)
 */
function getSlug(string $filename): string
{
    $name = pathinfo($filename, PATHINFO_FILENAME);
    return stripPrefix($name);
}

/**
 * Validate path to prevent directory traversal attacks
 */
function validatePath(string $path, string $contentDir): bool
{
    // Reject paths with ..
    if (str_contains($path, '..')) {
        return false;
    }

    // Reject absolute paths
    if (str_starts_with($path, '/')) {
        return false;
    }

    // Reject null bytes
    if (str_contains($path, "\0")) {
        return false;
    }

    return true;
}

/**
 * Sort items by numeric prefix, then alphabetically
 */
function sortByPrefix(array &$items): void
{
    usort($items, function ($a, $b) {
        // Extract numeric prefix if present
        preg_match('/^(\d+)-/', $a, $matchA);
        preg_match('/^(\d+)-/', $b, $matchB);

        $numA = isset($matchA[1]) ? (int) $matchA[1] : PHP_INT_MAX;
        $numB = isset($matchB[1]) ? (int) $matchB[1] : PHP_INT_MAX;

        if ($numA !== $numB) {
            return $numA - $numB;
        }

        return strcasecmp($a, $b);
    });
}

/**
 * Build breadcrumb from path
 */
function buildBreadcrumb(string $path): array
{
    if (empty($path) || $path === 'index') {
        return [];
    }

    $parts = explode('/', $path);
    $breadcrumb = [];
    $currentPath = '';

    foreach ($parts as $part) {
        $currentPath .= ($currentPath ? '/' : '') . $part;
        $breadcrumb[] = [
            'name' => toTitleCase($part),
            'slug' => $currentPath,
        ];
    }

    return $breadcrumb;
}

/**
 * Enhanced path validation for write operations
 * Returns the real content directory path if valid, false otherwise
 */
function validatePathForWrite(string $path, string $contentDir): bool|string
{
    // Basic validation
    if (!validatePath($path, $contentDir)) {
        return false;
    }

    // Reject paths with special characters that could cause issues
    if (preg_match('/[<>:"|?*\\\\]/', $path)) {
        return false;
    }

    // Reject hidden files/directories (starting with .)
    $parts = explode('/', $path);
    foreach ($parts as $part) {
        if (str_starts_with($part, '.')) {
            return false;
        }
    }

    // Get real content directory path
    $realContentDir = realpath($contentDir);
    if ($realContentDir === false) {
        return false;
    }

    return $realContentDir;
}

/**
 * Sanitize filename for safe file system operations
 */
function sanitizeFilename(string $filename): string
{
    // Remove path separators
    $filename = str_replace(['/', '\\'], '', $filename);

    // Remove null bytes
    $filename = str_replace("\0", '', $filename);

    // Keep only safe characters (alphanumeric, dots, dashes, underscores)
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '-', $filename);

    // Collapse multiple dashes
    $filename = preg_replace('/-+/', '-', $filename);

    // Remove leading/trailing dashes
    $filename = trim($filename, '-');

    // Ensure not empty
    if (empty($filename)) {
        $filename = 'untitled';
    }

    return $filename;
}

/**
 * Ensure filename has .md extension
 */
function ensureMarkdownExtension(string $filename): string
{
    if (!str_ends_with(strtolower($filename), '.md')) {
        $filename .= '.md';
    }
    return $filename;
}

/**
 * Check if a file is a system file that should not be modified
 */
function isSystemFile(string $filename): bool
{
    $systemFiles = [
        '.htaccess',
        '.protected',
        '.gitignore',
        '.git',
        '.env',
        '.DS_Store',
    ];

    $basename = basename($filename);

    // Check against system file list
    if (in_array(strtolower($basename), $systemFiles)) {
        return true;
    }

    // Reject any hidden files (starting with .)
    if (str_starts_with($basename, '.')) {
        return true;
    }

    return false;
}

/**
 * Generate a slug from a title
 */
function titleToSlug(string $title): string
{
    // Convert to lowercase
    $slug = strtolower($title);

    // Replace spaces and underscores with dashes
    $slug = str_replace([' ', '_'], '-', $slug);

    // Remove special characters
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);

    // Collapse multiple dashes
    $slug = preg_replace('/-+/', '-', $slug);

    // Trim dashes from ends
    $slug = trim($slug, '-');

    return $slug ?: 'untitled';
}
