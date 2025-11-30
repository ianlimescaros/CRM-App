<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/Validator.php';

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
}
