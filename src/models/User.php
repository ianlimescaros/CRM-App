<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    public static function create(string $name, string $email, string $passwordHash): int
    {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (:name, :email, :password_hash, NOW())');
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $passwordHash,
        ]);
        return (int)db()->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = db()->prepare(
            'SELECT u.* FROM users u
             JOIN auth_tokens t ON t.user_id = u.id
             WHERE t.token = :token AND t.expires_at > NOW()
             LIMIT 1'
        );
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function storeToken(int $userId, string $token, string $expiresAt): void
    {
        $stmt = db()->prepare('INSERT INTO auth_tokens (user_id, token, expires_at, created_at) VALUES (:user_id, :token, :expires_at, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token,
            ':expires_at' => $expiresAt,
        ]);
    }

    public static function deleteToken(string $token): void
    {
        $stmt = db()->prepare('DELETE FROM auth_tokens WHERE token = :token');
        $stmt->execute([':token' => $token]);
    }

    public static function deleteTokensForUser(int $userId): void
    {
        $stmt = db()->prepare('DELETE FROM auth_tokens WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $userId]);
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([':password_hash' => $passwordHash, ':id' => $id]);
    }

    public static function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = db()->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute([':name' => $name, ':email' => $email, ':id' => $id]);
    }
}
