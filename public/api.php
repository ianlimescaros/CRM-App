

<?php
// Configure error reporting based on APP_DEBUG.
$config = require __DIR__ . '/../src/config/config.php';
$appDebug = (bool)($config['app']['debug'] ?? false);
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('display_startup_errors', $appDebug ? '1' : '0');
error_reporting($appDebug ? E_ALL : 0);
// echo 'API BOOT OK\n';

require_once __DIR__ . '/../src/services/Response.php';

Response::cors();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../src/config/database.php';
// CSRF middleware (used to validate mutating browser requests)
require_once __DIR__ . '/../src/middleware/CsrfMiddleware.php';

// Ensure consistent timezone (override PHP default if needed).
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// Basic PSR-4 style autoload for src classes.
spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});

$routes = require __DIR__ . '/../src/routes/api_routes.php';

// DEBUG: request logging prep (actual write moved later so request path/method are available)
$rawBody = file_get_contents('php://input');
$truncBody = $rawBody;
if (strlen($truncBody) > 1000) {
    $truncBody = substr($truncBody, 0, 1000) . '...';
}
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
// Redact sensitive cookies before logging
$cookiesForLog = isset($_COOKIE) ? $_COOKIE : null;
if (is_array($cookiesForLog)) {
    if (isset($cookiesForLog['auth_token'])) $cookiesForLog['auth_token'] = 'REDACTED';
    if (isset($cookiesForLog['csrf_token'])) $cookiesForLog['csrf_token'] = 'REDACTED';
    if (isset($cookiesForLog['PHPSESSID'])) $cookiesForLog['PHPSESSID'] = 'REDACTED';
} 


$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// -----------------------------
// DEBUG: lightweight request logging
// -----------------------------
$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0777, true) && !is_dir($logDir)) {
        error_log('Failed to create log directory: ' . $logDir);
    }
}
$logEntry = [
    'time' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'method' => $requestMethod,
    'path' => $requestPath,
    'auth' => $authHeader ? (strlen($authHeader) > 40 ? substr($authHeader, 0, 40) . '...' : $authHeader) : null,
    'cookie' => $cookiesForLog,
    'body' => $truncBody,
];
if (file_put_contents($logDir . '/api_requests.log', json_encode($logEntry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
    error_log('Failed to write API request log to: ' . $logDir);
} 

// Strip /api.php prefix if present
$prefix = '/api.php';
if (str_starts_with($requestPath, $prefix)) {
    $requestPath = substr($requestPath, strlen($prefix));
}

// 🔎 DEBUG: show which DB this API is connected to (TEMP)
// Visit: /api.php/__db
if ($requestMethod === 'GET' && $requestPath === '/__db') {
    if (!$appDebug) {
        Response::error('Not Found', 404);
    }
    try {
        $pdo = db();
        $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $host = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);

        Response::success([
            'db_name' => $dbName,
            'connection_status' => $host,
        ]);
    } catch (Throwable $e) {
        Response::error('DB debug failed', 500, ['error' => $e->getMessage()]);
    }
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

    // Validate CSRF for mutating requests when a server session exists
    CsrfMiddleware::validate();

    $controller = new $class();
    $controller->$action(...array_values($params));
}

if (!$matched) {
    Response::error('Not Found', 404);
}
