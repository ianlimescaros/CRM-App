<?php
// Base controller utilities shared by other controllers.

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

abstract class BaseController
{
    /**
     * Parse the JSON request body into an associative array.
     *
     * @return array<string,mixed>
     */
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $raw = $raw === false ? '' : $raw;
        $data = json_decode((string)$raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * @return array<string,mixed>
     */
    protected function requireAuth(): array
    {
        return AuthMiddleware::require();
    }

    /**
     * Safely coerce a value to string for user input.
     *
     * @param mixed $v
     */
    protected function asString(mixed $v): string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_scalar($v)) {
            return (string)$v;
        }
        return '';
    }
}
