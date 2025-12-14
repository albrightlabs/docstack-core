<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\AdminAuth;
use App\Api;
use App\Config;
use App\Content;
use App\FileOperations;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Initialize configuration (must be before AdminAuth is used)
$config = Config::getInstance();

// Start session for authentication
session_start();

// Initialize services
$contentDir = __DIR__ . '/../' . Config::get('content_dir', 'content');
$content = new Content($contentDir);
$fileOps = new FileOperations($contentDir);
$api = new Api($content, $fileOps);

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

// Remove /api prefix from path
$path = preg_replace('/^\/api\/?/', '', $requestUri);

// Handle CORS preflight requests
if ($method === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

// Handle the API request
$api->handleRequest($method, $path);
