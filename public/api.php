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

// Handle CORS preflight requests
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

// Remove /docs prefix if present, then handle /api prefix
$path = preg_replace('/^\/docs/', '', $requestUri);

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
