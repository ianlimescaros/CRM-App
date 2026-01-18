<?php

// Polyfill for PHP < 8.
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

// Simple .env loader for key=value lines.
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, null);
            $name = trim($name);
            $value = $value === null ? '' : trim($value);

            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// Helper to fetch env vars with default.
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?? $default;
    }
}

loadEnv(dirname(__DIR__, 2) . '/.env');

return [
    'app' => [
        'env' => env('APP_ENV', 'production'),
        'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
        'url' => env('APP_URL', 'http://localhost'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 3306),
        'name' => env('DB_NAME', env('DB_DATABASE', 'crm_app')),
        'user' => env('DB_USER', env('DB_USERNAME', 'root')),
        'pass' => env('DB_PASS', env('DB_PASSWORD', '')),
        'charset' => 'utf8mb4',
    ],
    'llm' => [
        'url' => env('LLM_API_URL', env('LLM_API_BASE', '')),
        'key' => env('LLM_API_KEY', ''),
        'model' => env('LLM_MODEL', 'gpt-4'),
    ],
];
