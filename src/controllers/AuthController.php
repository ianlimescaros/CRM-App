<?php

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../services/Response.php';
require_once __DIR__ . '/../services/RateLimiter.php';
require_once __DIR__ . '/BaseController.php';

class AuthController extends BaseController
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
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $emailKey = strtolower(trim($input['email'] ?? ''));

        $this->throttleOrFail('login:ip:' . $ip, 5, 60);
        if ($emailKey !== '') {
            $this->throttleOrFail('login:email:' . $emailKey, 5, 60);
        }

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

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->throttleOrFail('forgot:ip:' . $ip, 5, 300);
        if ($email) {
            $this->throttleOrFail('forgot:email:' . strtolower($email), 3, 300);
        }

        $this->auth->requestPasswordReset((string)$email);
        Response::success(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function reset(): void
    {
        $input = $this->getJsonInput();
        $errors = Validator::required($input, ['email', 'token', 'password']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email($input['email']));
        }
        if ($errors) {
            Response::error('Validation failed', 422, $errors);
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $email = strtolower(trim((string)$input['email']));
        $this->throttleOrFail('reset:ip:' . $ip, 5, 300);
        $this->throttleOrFail('reset:email:' . $email, 5, 300);

        $result = $this->auth->resetPassword((string)$input['token'], (string)$input['password'], $email);
        if (isset($result['errors'])) {
            Response::error('Invalid or expired token', 422, $result['errors']);
        }
        Response::success(['message' => 'Password has been reset.']);
    }

    private function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    private function throttleOrFail(string $key, int $maxAttempts, int $decaySeconds): void
    {
        if (!RateLimiter::hit($key, $maxAttempts, $decaySeconds)) {
            Response::error('Too many attempts. Try again later.', 429);
        }
    }
}
