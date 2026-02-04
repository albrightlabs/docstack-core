<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;
use App\Api;
use App\UserApi;
use App\Config;
use App\Content;
use App\FileOperations;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Initialize configuration
$config = Config::getInstance();

// Initialize auth (starts session)
Auth::init();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Handle CORS - restrict to same origin or configured origins
$allowedOrigins = Config::get('cors_allowed_origins', []);
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Determine if origin is allowed
$isAllowedOrigin = false;
if (!empty($origin)) {
    // Check if origin matches the server's own host
    $serverHost = ($_SERVER['HTTPS'] ?? 'off') !== 'off' ? 'https://' : 'http://';
    $serverHost .= $_SERVER['HTTP_HOST'] ?? '';

    if ($origin === $serverHost) {
        $isAllowedOrigin = true;
    } elseif (!empty($allowedOrigins) && in_array($origin, $allowedOrigins, true)) {
        $isAllowedOrigin = true;
    }
}

// Handle CORS preflight requests
if ($method === 'OPTIONS') {
    if ($isAllowedOrigin) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

// Set CORS headers for actual requests
if ($isAllowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}

// Remove base path prefix if present, then handle /api prefix
$basePath = Config::getBasePath();
$path = $basePath ? preg_replace('/^' . preg_quote($basePath, '/') . '/', '', $requestUri) : $requestUri;

// Route to appropriate API handler
if (str_starts_with($path, '/api/auth/') || str_starts_with($path, '/api/users') || $path === '/api/content/tree') {
    // User, auth, and content tree endpoints
    $userApi = new UserApi();
    $userApi->handle($method, $path);
} else {
    // Content API endpoints
    $contentDir = __DIR__ . '/../' . Config::get('content_dir', 'content');
    $content = new Content($contentDir);
    $fileOps = new FileOperations($contentDir);
    $api = new Api($content, $fileOps);

    // Remove /api prefix from path for content API
    $apiPath = preg_replace('/^\/api\/?/', '', $path);
    $api->handleRequest($method, $apiPath);
}
