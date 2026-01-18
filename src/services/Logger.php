<?php
// Simple file logger for app events.

class Logger
{
    private static function path(): string
    {
        $base = dirname(__DIR__, 2) . '/storage/logs/app.log';
        return $base;
    }

    /**
     * Log error message
     * @param string $message
     * @param array<string,mixed> $context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    /**
     * Log info message
     * @param string $message
     * @param array<string,mixed> $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    private static function write(string $level, string $message, array $context): void
    {
        // Ensure timezone is consistent for logs.
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        $line = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? json_encode($context) : ''
        );
        file_put_contents(self::path(), $line, FILE_APPEND);
    }
}
