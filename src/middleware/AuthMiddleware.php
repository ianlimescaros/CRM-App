<?php
// Auth middleware for validating tokens on API requests.

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/Response.php';

class AuthMiddleware
{
    public static function require(): array
    {
        $auth = new AuthService();
        $token = self::getBearerToken();
        $user = $auth->requireAuth($token);
        if (!$user) {
            Response::error('Unauthorized', 401);
        }
        return $user;
    }

    private static function getBearerToken(): ?string
    {
        // Try common server vars first
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');

        // Fallback to apache_request_headers when available (some hosts only populate there)
        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $header = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $header = $headers['authorization'];
            }
        }

        // Standard Bearer token from Authorization header
        if (function_exists('str_starts_with') && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }

        // Fallback to cookie-based token (the JS sets `auth_token` cookie for page auth)
        if (!empty($_COOKIE['auth_token'])) {
            return trim((string)$_COOKIE['auth_token']);
        }

        return null;
    }
}
