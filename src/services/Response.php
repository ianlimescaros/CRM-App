<?php
// JSON response helpers and CORS headers.

class Response
{
    /**
     * Send JSON response
     * @param array<string,mixed> $data
     * @param int $status
     * @return void
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        self::cors();
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Send success response
     * @param array<string,mixed> $data
     * @param int $status
     * @return void
     */
    public static function success(array $data = [], int $status = 200): void
    {
        self::json(array_merge(['success' => true], $data), $status);
    }

    /**
     * Send error response
     * @param string $message
     * @param int $status
     * @param array<string,mixed> $errors
     * @return void
     */
    public static function error(string $message, int $status = 400, array $errors = []): void
    {
        // Optional logging hook without leaking sensitive data.
        if ($status >= 500) {
            if (class_exists('Logger')) {
                Logger::error($message, ['status' => $status]);
            }
        }

        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    public static function cors(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = self::allowedOrigins();
        if ($origin !== '' && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }

    private static function allowedOrigins(): array
    {
        $origins = [];
        if (function_exists('env')) {
            $configured = trim((string)env('CORS_ORIGINS', ''));
            if ($configured !== '') {
                $origins = array_map('trim', explode(',', $configured));
            }
            $appUrl = trim((string)env('APP_URL', ''));
            if ($appUrl !== '') {
                $origins[] = rtrim($appUrl, '/');
            }
        }

        $origins = array_merge($origins, [
            'http://localhost',
            'http://localhost:3000',
            'http://localhost:8765',
            'http://127.0.0.1',
            'http://127.0.0.1:8765',
        ]);

        $origins = array_filter($origins, fn ($origin) => $origin !== '');
        return array_values(array_unique($origins));
    }
}
