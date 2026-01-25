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

// Check if setup is needed (no users exist)
if (!Auth::hasAnyUsers() && $path !== 'setup') {
    header('Location: /docs/setup');
    exit;
}

// Handle setup page (first-time installation)
if ($path === 'setup') {
    // If users already exist, redirect to login
    if (Auth::hasAnyUsers()) {
        header('Location: /docs/login');
        exit;
    }

    $error = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Auth::validateCsrf($_POST['csrf_token'] ?? null)) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            // Validate inputs
            if (empty($name)) {
                $error = 'Name is required.';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'A valid email address is required.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Passwords do not match.';
            } else {
                // Create the first user as super admin
                try {
                    $userManager = Auth::getUserManager();
                    $userManager->create($name, $email, $password, 'admin', true);

                    // Auto-login the new user
                    Auth::login($email, $password);

                    header('Location: /docs');
                    exit;
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }
    }

    include __DIR__ . '/../templates/setup.php';
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

// Handle user edit page (admin only)
if (preg_match('#^users/([a-f0-9-]+)/edit$#', $path, $matches)) {
    if (!Auth::check()) {
        header('Location: /docs/login');
        exit;
    }
    if (!Auth::isAdmin()) {
        http_response_code(403);
        include __DIR__ . '/../templates/403.php';
        exit;
    }

    $userId = $matches[1];
    $editUser = Auth::getUserManager()->getById($userId);

    if (!$editUser) {
        http_response_code(404);
        $pageTitle = 'User Not Found';
        include __DIR__ . '/../templates/404.php';
        exit;
    }

    $currentUser = Auth::getCurrentUser();
    $contentTree = $content->getFullTree();
    include __DIR__ . '/../templates/user-edit.php';
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

// Get user's permission paths for filtering
$userContentPaths = [];
$userNavPaths = [];
if (Auth::check()) {
    $userNavPaths = Auth::getNavigationPaths();
    // Content paths are the sections array from user permissions
    $userId = Auth::getCurrentUserId();
    if ($userId) {
        $userPerms = Auth::getUserManager()->getById($userId)['permissions'] ?? ['full_access' => false, 'sections' => []];
        if ($userPerms['full_access'] || Auth::isAdmin()) {
            $userContentPaths = ['*'];
        } else {
            $userContentPaths = $userPerms['sections'] ?? [];
        }
    }
}

// Load sidebar tree for current section (filtered by permissions)
if ($sectionExists) {
    if (in_array('*', $userNavPaths)) {
        $tree = $content->getTree($currentSection);
    } else {
        $tree = $content->getFilteredTree($currentSection, $userContentPaths, $userNavPaths);
    }
} else {
    $tree = [];
}

// Load requested doc
$doc = $content->getDoc($path ?: 'index');

if (!$doc) {
    http_response_code(404);
    $pageTitle = 'Page Not Found';
    $currentPath = $path ?: '';
    include __DIR__ . '/../templates/404.php';
    exit;
}

// Check content access permissions
if (Auth::check() && !Auth::canAccessContent($path)) {
    // Check if this is a nav-only path
    if (Auth::isNavOnly($path)) {
        // Show nav-only message within the layout
        $pageTitle = 'Navigate to Content';
        $currentPath = $path ?: '';
        $isNavOnlyPage = true;
        $html = '<div class="nav-only-container">
            <div class="nav-only-icon">&#128194;</div>
            <h1 class="nav-only-title">Navigate to Content</h1>
            <p class="nav-only-message">You don\'t have access to view this page directly, but you can navigate to sub-pages you have access to using the sidebar.</p>
            <p class="nav-only-hint">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
                Select a page from the sidebar
            </p>
        </div>';
        $headings = [];
        $breadcrumb = [];
        $isAdmin = Auth::isAuthenticated() && Auth::isAdmin();
        $csrfToken = Auth::getCsrfToken();
        $currentUser = Auth::getCurrentUser();
        include __DIR__ . '/../templates/layout.php';
        exit;
    }

    // No access at all
    http_response_code(403);
    include __DIR__ . '/../templates/403.php';
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
