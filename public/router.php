<?php

/**
 * Router for PHP's built-in development server
 * Usage: php -S localhost:8000 router.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route API requests to api.php
if (preg_match('#^/api(?:/|$)#', $uri)) {
    require __DIR__ . '/api.php';
    return true;
}

// Route everything else to index.php
require __DIR__ . '/index.php';
