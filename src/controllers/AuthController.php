<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
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

    public function me(): void
    {
        $user = AuthMiddleware::require();
        Response::success(['user' => $user]);
    }

    public function updateProfile(): void
    {
        $user = AuthMiddleware::require();
        $input = $this->getJsonInput();
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $newPass = $input['password'] ?? '';

        $errors = Validator::required(['name' => $name, 'email' => $email], ['name', 'email']);
        if ($email !== '') {
            $errors = array_merge($errors, Validator::email($email));
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $existing = User::findByEmail($email);
        if ($existing && (int)$existing['id'] !== (int)$user['id']) {
            Response::error('Validation failed', 422, ['email' => 'Email already in use.']);
        }

        User::updateProfile((int)$user['id'], $name, $email);
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            User::updatePassword((int)$user['id'], $hash);
        }

        $fresh = User::findById((int)$user['id']);
        Response::success(['user' => $this->auth->publicSanitizeUser($fresh)]);
    }

    public function forgot(): void
    {
        $input = $this->getJsonInput();
        $email = $input['email'] ?? '';
        $this->auth->requestPasswordReset((string)$email);
        Response::success(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function reset(): void
    {
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['token', 'password']);
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }
        $result = $this->auth->resetPassword((string)$input['token'], (string)$input['password']);
        if (isset($result['errors'])) {
            Response::error('Invalid or expired token', 422, $result['errors']);
        }
        Response::success(['message' => 'Password has been reset.']);
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
