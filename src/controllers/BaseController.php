<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

abstract class BaseController
{
    protected function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    protected function requireAuth(): array
    {
        return AuthMiddleware::require();
    }
}
