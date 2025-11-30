<?php

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
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (function_exists('str_starts_with') && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        if (strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }
        return null;
    }
}
