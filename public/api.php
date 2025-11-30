<?php

// CORS and JSON defaults
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/services/Response.php';

// Basic PSR-4 style autoload for src classes.
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

$routes = require __DIR__ . '/../src/routes/api_routes.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Strip /api.php prefix if present
$prefix = '/api.php';
if (str_starts_with($requestPath, $prefix)) {
    $requestPath = substr($requestPath, strlen($prefix));
}

// Simple matcher for placeholder routes like /leads/{id}
function matchRoute(string $routePath, string $requestPath): array|false
{
    $routeParts = explode('/', trim($routePath, '/'));
    $requestParts = explode('/', trim($requestPath, '/'));

    if (count($routeParts) !== count($requestParts)) {
        return false;
    }

    $params = [];
    foreach ($routeParts as $index => $segment) {
        if (preg_match('/^{(.+)}$/', $segment, $matches)) {
            $params[$matches[1]] = $requestParts[$index];
            continue;
        }
        if ($segment !== $requestParts[$index]) {
            return false;
        }
    }
    return $params;
}

$matched = false;
foreach ($routes as $route) {
    if ($route['method'] !== $requestMethod) {
        continue;
    }

    $params = matchRoute($route['path'], $requestPath);
    if ($params === false) {
        continue;
    }

    $matched = true;
    [$class, $action] = $route['handler'];
    $controller = new $class();
    $controller->$action(...array_values($params));
}

if (!$matched) {
    Response::error('Not Found', 404);
}
