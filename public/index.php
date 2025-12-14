<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\AdminAuth;
use App\Config;
use App\Content;
use App\Markdown;
use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Initialize configuration
$config = Config::getInstance();
$branding = Config::getBranding();

// Initialize services
$contentDir = __DIR__ . '/../' . Config::get('content_dir', 'content');
$content = new Content($contentDir);
$markdown = new Markdown();

// Start session for password protection
session_start();

// Check if section is protected
function isSectionProtected(string $contentDir, string $section): bool {
    $entries = scandir($contentDir);
    if ($entries === false) {
        return false;
    }
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        if (getSlug($entry) === $section && is_dir($contentDir . '/' . $entry)) {
            return file_exists($contentDir . '/' . $entry . '/.protected');
        }
    }
    return false;
}

function isAuthenticated(string $section): bool {
    return isset($_SESSION['auth_sections'][$section]) && $_SESSION['auth_sections'][$section] === true;
}

// Get requested path from URL
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = trim($requestUri, '/');

// Remove 'docs' prefix if present
$path = preg_replace('/^docs\/?/', '', $path);

// Handle asset requests
if (preg_match('/\.(css|js|png|jpg|gif|svg|ico)$/i', $requestUri)) {
    return false; // Let the server handle static files
}

// Get sections for tabs
$sections = $content->getSections();

// Determine current section from path
$pathParts = $path ? explode('/', $path) : [];
$currentSection = $pathParts[0] ?? null;

// If no path or root, redirect to first section
if (empty($path) && !empty($sections)) {
    header('Location: /docs/' . $sections[0]['slug']);
    exit;
}

// Validate current section exists
$sectionExists = false;
foreach ($sections as $section) {
    if ($section['slug'] === $currentSection) {
        $sectionExists = true;
        break;
    }
}

// Check password protection
$authError = null;
$isProtected = $sectionExists && isSectionProtected($content->getContentDir(), $currentSection);
$needsAuth = $isProtected && !isAuthenticated($currentSection);

// Handle password submission
if ($isProtected && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $correctPassword = Config::get('docs_password', '');
    if ($_POST['password'] === $correctPassword && !empty($correctPassword)) {
        $_SESSION['auth_sections'][$currentSection] = true;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    $authError = 'Incorrect password. Please try again.';
    $needsAuth = true;
}

// Show password form if needed
if ($needsAuth) {
    $pageTitle = 'Password Required';
    $sectionName = '';
    foreach ($sections as $s) {
        if ($s['slug'] === $currentSection) {
            $sectionName = $s['name'];
            break;
        }
    }
    include __DIR__ . '/../templates/password.php';
    exit;
}

// Load sidebar tree for current section
$tree = $sectionExists ? $content->getTree($currentSection) : [];

// Load requested doc
$doc = $content->getDoc($path ?: 'index');

if (!$doc) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $currentPath = $path ?: '';
    include __DIR__ . '/../templates/404.php';
    exit;
}

// Parse markdown
$html = $markdown->parse($doc['markdown']);
$html = $markdown->rewriteLinks($html, $path, $doc['isIndex'] ?? false);
$html = $markdown->addHeadingAnchors($html);
$headings = $markdown->extractHeadings($html);

// Build breadcrumb (skip the section since it's in tabs)
$breadcrumb = buildBreadcrumb($path);
if (!empty($breadcrumb)) {
    array_shift($breadcrumb); // Remove first item (section)
}

// Page title
$pageTitle = $doc['title'];

// Current path for sidebar highlighting
$currentPath = $path ?: 'index';

// Admin state for templates
$isAdmin = AdminAuth::isAuthenticated();
$csrfToken = AdminAuth::getCsrfToken();

// Include layout
include __DIR__ . '/../templates/layout.php';
