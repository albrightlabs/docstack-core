<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;
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

// Initialize auth (starts session)
Auth::init();

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

// Handle API requests
if (str_starts_with($path, 'api/') || $path === 'api') {
    require __DIR__ . '/api.php';
    exit;
}

// Handle login page
if ($path === 'login') {
    if (Auth::check()) {
        header('Location: /docs');
        exit;
    }

    $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check rate limiting first
        if (Auth::isRateLimited()) {
            $remaining = Auth::getRateLimitRemainingTime();
            $minutes = ceil($remaining / 60);
            $error = "Too many failed attempts. Please try again in {$minutes} minute(s).";
        } elseif (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            if (Auth::login($email, $password)) {
                header('Location: /docs');
                exit;
            } else {
                // Check if now rate limited after failed attempt
                if (Auth::isRateLimited()) {
                    $remaining = Auth::getRateLimitRemainingTime();
                    $minutes = ceil($remaining / 60);
                    $error = "Too many failed attempts. Please try again in {$minutes} minute(s).";
                } else {
                    $error = 'Invalid email or password';
                }
            }
        }
    }

    include __DIR__ . '/../templates/login.php';
    exit;
}

// Handle logout
if ($path === 'logout') {
    Auth::logout();
    header('Location: /docs/login');
    exit;
}

// Handle users page (admin only)
if ($path === 'users') {
    if (!Auth::check()) {
        header('Location: /docs/login');
        exit;
    }
    if (!Auth::isAdmin()) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
    $currentUser = Auth::getCurrentUser();
    include __DIR__ . '/../templates/users.php';
    exit;
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
$isAdmin = Auth::isAuthenticated() && Auth::isAdmin();
$csrfToken = Auth::getCsrfToken();
$currentUser = Auth::getCurrentUser();

// Include layout
include __DIR__ . '/../templates/layout.php';
