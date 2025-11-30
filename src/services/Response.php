<?php

class Response
{
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        echo json_encode($data);
        exit;
    }

    public static function success(array $data = [], int $status = 200): void
    {
        self::json(array_merge(['success' => true], $data), $status);
    }

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
}
