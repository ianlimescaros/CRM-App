<?php

require_once __DIR__ . '/../config/database.php';

class PasswordReset
{
    public static function store(string $email, string $tokenHash, string $expiresAt): void
    {
        // Remove existing tokens for this email
        $del = db()->prepare('DELETE FROM password_resets WHERE email = :email');
        $del->execute([':email' => $email]);

        $stmt = db()->prepare('INSERT INTO password_resets (email, token_hash, expires_at, created_at) VALUES (:email, :token_hash, :expires_at, NOW())');
        $stmt->execute([
            ':email' => $email,
            ':token_hash' => $tokenHash,
            ':expires_at' => $expiresAt,
        ]);
    }

    public static function findValidByToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE token_hash = :token_hash AND expires_at > NOW() LIMIT 1');
        $stmt->execute([':token_hash' => $hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function deleteByEmail(string $email): void
    {
        $stmt = db()->prepare('DELETE FROM password_resets WHERE email = :email');
        $stmt->execute([':email' => $email]);
    }
}
