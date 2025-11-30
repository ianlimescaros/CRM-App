<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/Response.php';

class AuthController
{
    private AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function register(): void
    {
        $input = $this->getJsonInput();
        $result = $this->auth->register($input);

        if (isset($result['errors'])) {
            Response::error('Validation failed', 422, $result['errors']);
        }

        Response::success(['user' => $result['user'], 'token' => $result['token']]);
    }

    public function login(): void
    {
        $input = $this->getJsonInput();
        $result = $this->auth->login($input['email'] ?? '', $input['password'] ?? '');

        if (isset($result['errors'])) {
            Response::error('Invalid credentials', 401, $result['errors']);
        }

        Response::success(['user' => $result['user'], 'token' => $result['token']]);
    }

    public function logout(): void
    {
        $token = $this->getBearerToken();
        $this->auth->logout($token);
        Response::success(['message' => 'Logged out']);
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}
