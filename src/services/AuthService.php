<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PasswordReset.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/Mailer.php';

class AuthService
{
    private int $tokenTtlHours = 168; // 7 days
    private int $resetExpiryMinutes = 30;
    private int $maxResetFailures = 5;

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

        // Use a 6-digit numeric token for reset
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $longToken = bin2hex(random_bytes(32));

        $tokenHash = hash('sha256', $longToken);
        $codeHash = hash('sha256', $code);
        $expiresAt = (new DateTime('+' . $this->resetExpiryMinutes . ' minutes'))->format('Y-m-d H:i:s');
        PasswordReset::store($email, $tokenHash, $codeHash, $expiresAt);

        $appUrl = env('APP_URL', 'http://localhost');
        $resetUrl = rtrim($appUrl, '/') . '/index.php?page=login&reset_token=' . urlencode($longToken);

        // Attempt to email the reset link; do not expose raw tokens in logs.
        $sent = Mailer::sendResetEmail($email, $resetUrl, $code);
        if (!$sent) {
            Logger::error('Password reset email failed to send', ['email' => $email]);
        } else {
            Logger::info('Password reset requested', ['email' => $email]);
        }
    }

    public function resetPassword(string $token, string $newPassword, string $email): array
    {
        $email = trim(strtolower($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['errors' => ['email' => 'Invalid email']];
        }

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

        if (trim($newPassword) === '') {
            return ['errors' => ['token' => 'Invalid token or password']];
        }

        $isCode = preg_match('/^\d{6}$/', $token) === 1;
        $entry = $isCode
            ? PasswordReset::findValidByCode($email, $token)
            : PasswordReset::findValidByToken($email, $token);

        if (!$entry) {
            PasswordReset::recordFailure($email, $this->maxResetFailures);
            return ['errors' => ['token' => 'Invalid or expired token']];
        }

        if (($entry['attempts'] ?? 0) >= $this->maxResetFailures) {
            PasswordReset::deleteByEmail($email);
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
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTime())->modify("+{$this->tokenTtlHours} hours")->format('Y-m-d H:i:s');
        User::storeToken($userId, $tokenHash, $expiresAt);
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
