<?php
// CSRF middleware for state-changing requests.

require_once __DIR__ . '/../services/Response.php';

class CsrfMiddleware
{
    // Public endpoints that don't require CSRF (for unauthenticated users)
    private static $publicEndpoints = [
        '/auth/register',
        '/auth/login',
        '/auth/forgot',
        '/auth/reset',
    ];

    public static function validate(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // Safe methods do not require CSRF
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return;
        }

        // Check request path
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (str_starts_with($requestPath, '/api.php')) {
            $requestPath = substr($requestPath, 8); // Remove /api.php prefix
        }

        // Public endpoints don't require CSRF
        if (in_array($requestPath, self::$publicEndpoints)) {
            return;
        }

        // Check if using Bearer token authentication
        $hasBearerToken = !empty($_SERVER['HTTP_AUTHORIZATION']) && strpos($_SERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0;

        // If using Bearer token (API client), skip CSRF - token auth is already secure
        if ($hasBearerToken) {
            return;
        }

        // If there's no auth cookie, treat as unauthenticated and skip CSRF
        if (empty($_COOKIE['auth_token'])) {
            return;
        }

        // Ensure session is started when possible (if it exists, we can validate against it)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!headers_sent()) {
                session_start();
            }
        }

        // For session-based authenticated requests, validate CSRF token
        // Expect header X-CSRF-Token to match session token, or accept form body param csrf_token
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_SERVER['HTTP_X_CSRFTOKEN'] ?? null);
        $bodyToken = $_POST['csrf_token'] ?? null;

        // For JSON requests, attempt to parse raw body to find csrf_token
        if (!$bodyToken && empty($_POST) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['csrf_token'])) {
                $bodyToken = $decoded['csrf_token'];
            }
        }

        $tokenToCheck = $header ?: $bodyToken;
        if (!$tokenToCheck) {
            Response::error('Invalid CSRF token', 403);
        }

        if (!empty($_SESSION['csrf_token'])) {
            if (!hash_equals($_SESSION['csrf_token'], $tokenToCheck)) {
                Response::error('Invalid CSRF token', 403);
            }
            return;
        }

        $cookieToken = $_COOKIE['csrf_token'] ?? null;
        if (!$cookieToken || !hash_equals($cookieToken, $tokenToCheck)) {
            Response::error('Invalid CSRF token', 403);
        }
    }
}
