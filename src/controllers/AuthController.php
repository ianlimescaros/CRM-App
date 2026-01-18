<?php
// Controller for login, register, logout, and reset.

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
            Response::error('Validation failed', 422, (array)$result['errors']);
        }

        // Create server-side cookie + session for browser clients
        $this->setTokenCookieAndSession((string)$result['token'], (int)$result['user']['id']);

        Response::success(['user' => $result['user'], 'token' => $result['token']]);
    }

    public function login(): void
    {
        $input = $this->getJsonInput();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $emailKey = strtolower(trim($this->asString($input['email'] ?? '')));

        $this->throttleOrFail('login:ip:' . $ip, 5, 60);
        if ($emailKey !== '') {
            $this->throttleOrFail('login:email:' . $emailKey, 5, 60);
        }

        $result = $this->auth->login($this->asString($input['email'] ?? ''), $this->asString($input['password'] ?? ''));

        if (isset($result['errors'])) {
            Response::error('Invalid credentials', 401, (array)$result['errors']);
        }

        // Create server-side cookie + session for browser clients
        $this->setTokenCookieAndSession((string)$result['token'], (int)$result['user']['id']);

        Response::success(['user' => $result['user'], 'token' => $result['token']]);
    }

    public function logout(): void
    {
        $token = $this->getBearerToken();
        $this->auth->logout($token);
        // Destroy any server-side session and clear cookies
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start();
        }
        // Clear session data
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        // Clear session cookie
        setcookie('PHPSESSID', '', time() - 3600, '/');
        // Clear auth and csrf cookies as well
        setcookie('auth_token', '', time() - 3600, '/');
        setcookie('csrf_token', '', time() - 3600, '/');

        Response::success(['message' => 'Logged out']);
    }

    public function me(): void
    {
        $user = AuthMiddleware::require();
        Response::success(['user' => $user]);
    }

    public function createSession(): void
    {
        $user = AuthMiddleware::require();

        // Start or resume PHP session and store minimal user info
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // regenerate id for safety
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];

        // create CSRF token for this session and expose a non-HttpOnly cookie so JS can read it
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
        setcookie('csrf_token', $_SESSION['csrf_token'], ['path' => '/', 'samesite' => 'Lax', 'secure' => $secure]);

        Response::success(['message' => 'Session created']);
    }

    private function setTokenCookieAndSession(string $token, int $userId): void
    {
        // Set auth_token cookie (HttpOnly) and create a server-side session with CSRF token
        $ttl = 60 * 60 * 24 * 7; // 7 days
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);

        // Auth cookie (HttpOnly)
        setcookie('auth_token', $token, ['expires' => time() + $ttl, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $secure]);

        // Start session and set minimal user info
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start();
        }
        if (function_exists('session_regenerate_id') && !headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $userId;

        // CSRF token for browser requests (exposed as non-HttpOnly cookie so JS can read it)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        setcookie('csrf_token', $_SESSION['csrf_token'], ['path' => '/', 'samesite' => 'Lax', 'secure' => $secure]);
    }

    public function updateProfile(): void
    {
        $user = AuthMiddleware::require();
        $input = $this->getJsonInput();
        $name = trim($this->asString($input['name'] ?? ''));
        $email = trim($this->asString($input['email'] ?? ''));
        $newPass = $this->asString($input['password'] ?? '');

        $errors = (array) Validator::required(['name' => $name, 'email' => $email], ['name', 'email']);
        if ($email !== '') {
            $errors = array_merge($errors, Validator::email($email));
        }
        if ($newPass !== '') {
            $errors = array_merge($errors, Validator::passwordStrength($newPass, 'password', 8));
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
        Response::success(['user' => $this->auth->publicSanitizeUser((array)$fresh)]);
    }

    public function forgot(): void
    {
        $input = $this->getJsonInput();
        $email = strtolower(trim($this->asString($input['email'] ?? '')));

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->throttleOrFail('forgot:ip:' . $ip, 5, 300);
        if ($email !== '') {
            $this->throttleOrFail('forgot:email:' . $email, 3, 300);
        }

        $this->auth->requestPasswordReset($email);
        Response::success(['message' => 'If that email exists, a reset link has been sent.']);
    }

    public function reset(): void
    {
        $input = $this->getJsonInput();
        $errors = (array) Validator::required($input, ['email', 'token', 'password']);
        if (!empty($input['email'])) {
            $errors = array_merge($errors, Validator::email((string)$input['email']));
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
            Response::error('Invalid or expired token', 422, (array)$result['errors']);
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
