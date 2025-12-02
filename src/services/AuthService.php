<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Logger.php';

class AuthService
{
    private int $tokenTtlHours = 168; // 7 days

    public function register(array $data): array
    {
        $errors = Validator::required($data, ['name', 'email', 'password']);
        if (!empty($data['email'])) {
            $errors = array_merge($errors, Validator::email($data['email']));
        }
        if ($errors) {
            return ['errors' => $errors];
        }

        if (User::findByEmail($data['email'])) {
            return ['errors' => ['email' => 'Email already registered.']];
        }

        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $userId = User::create($data['name'], $data['email'], $hash);
        $user = User::findById($userId);
        $token = $this->issueToken($userId);

        return [
            'user' => $this->sanitizeUser($user),
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): array
    {
        $errors = Validator::required(['email' => $email, 'password' => $password], ['email', 'password']);
        if ($errors) {
            return ['errors' => $errors];
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['errors' => ['auth' => 'Invalid credentials.']];
        }

        $token = $this->issueToken((int)$user['id']);
        return [
            'user' => $this->sanitizeUser($user),
            'token' => $token,
        ];
    }

    public function logout(?string $token): void
    {
        if (!$token) {
            return;
        }
        User::deleteToken($token);
    }

    public function currentUser(?string $token): ?array
    {
        if (!$token) {
            return null;
        }
        $user = User::findByToken($token);
        return $user ? $this->sanitizeUser($user) : null;
    }

    public function requireAuth(?string $token): ?array
    {
        return $this->currentUser($token);
    }

    public function requestPasswordReset(string $email): void
    {
        $email = trim($email);
        if ($email === '') {
            return;
        }

        $user = User::findByEmail($email);
        if (!$user) {
            // Avoid leaking existence
            return;
        }

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $token = bin2hex(random_bytes(16));
        }

        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
        PasswordReset::store($email, $tokenHash, $expiresAt);

        $appUrl = env('APP_URL', 'http://localhost');
        $resetUrl = rtrim($appUrl, '/') . '/index.php?page=login&reset_token=' . urlencode($token);
        Logger::info('Password reset requested', ['email' => $email, 'reset_url' => $resetUrl]);
    }

    public function resetPassword(string $token, string $newPassword): array
    {
        // Accept raw token or full URL containing reset_token param
        $token = trim($token);
        if (str_contains($token, 'reset_token=')) {
            $parsed = parse_url($token);
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $qs);
                if (!empty($qs['reset_token'])) {
                    $token = $qs['reset_token'];
                }
            }
            if (!isset($qs['reset_token'])) {
                if (preg_match('/reset_token=([A-Fa-f0-9]{32,64})/', $token, $m)) {
                    $token = $m[1];
                }
            }
        }
        $token = trim($token, " \t\n\r\0\x0B\"'");

        if (!preg_match('/^[a-f0-9]{32,64}$/i', $token) || trim($newPassword) === '') {
            return ['errors' => ['token' => 'Invalid token or password']];
        }

        $entry = PasswordReset::findValidByToken($token);
        if (!$entry) {
            return ['errors' => ['token' => 'Invalid or expired token']];
        }

        $user = User::findByEmail($entry['email']);
        if (!$user) {
            PasswordReset::deleteByEmail($entry['email']);
            return ['errors' => ['token' => 'Invalid or expired token']];
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        User::updatePassword((int)$user['id'], $hash);
        User::deleteTokensForUser((int)$user['id']);
        PasswordReset::deleteByEmail($entry['email']);

        return ['status' => 'ok'];
    }

    private function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTime())->modify("+{$this->tokenTtlHours} hours")->format('Y-m-d H:i:s');
        User::storeToken($userId, $token, $expiresAt);
        return $token;
    }

    private function sanitizeUser(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }

    public function publicSanitizeUser(array $user): array
    {
        return $this->sanitizeUser($user);
    }
}
