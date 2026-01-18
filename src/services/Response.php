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
        // Emit security headers (CSP, HSTS when applicable, and other best-practice headers)
        self::emitSecurityHeaders();
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

    /**
     * Emit a set of recommended security response headers. Kept small and conservative so it
     * is safe to enable in most environments; production deployments should tune CSP/HSTS.
     */
    public static function emitSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer-when-downgrade');
        // Modern browsers ignore X-XSS-Protection; setting to 0 avoids legacy quirks
        header('X-XSS-Protection: 0');
        // Conservative default CSP — keep tight but allow inline styles for current UI (review for stricter CSP)
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'");

        // Only set HSTS when running over HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
        }
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
