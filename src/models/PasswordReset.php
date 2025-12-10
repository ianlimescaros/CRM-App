<?php

require_once __DIR__ . '/../config/database.php';

class PasswordReset
{
    public static function store(string $email, string $tokenHash, string $codeHash, string $expiresAt): void
    {
        // Remove existing tokens for this email
        $del = db()->prepare('DELETE FROM password_resets WHERE email = :email');
        $del->execute([':email' => $email]);

        $stmt = db()->prepare(
            'INSERT INTO password_resets (email, token_hash, code_hash, expires_at, created_at, attempts)
             VALUES (:email, :token_hash, :code_hash, :expires_at, NOW(), 0)'
        );
        $stmt->execute([
            ':email' => $email,
            ':token_hash' => $tokenHash,
            ':code_hash' => $codeHash,
            ':expires_at' => $expiresAt,
        ]);
    }

    public static function findValidByToken(string $email, string $token): ?array
    {
        $hash = hash('sha256', $token);
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE email = :email AND token_hash = :token_hash AND expires_at > NOW() LIMIT 1');
        $stmt->execute([':email' => $email, ':token_hash' => $hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findValidByCode(string $email, string $code): ?array
    {
        $hash = hash('sha256', $code);
        $stmt = db()->prepare('SELECT * FROM password_resets WHERE email = :email AND code_hash = :code_hash AND expires_at > NOW() LIMIT 1');
        $stmt->execute([':email' => $email, ':code_hash' => $hash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function recordFailure(string $email, int $maxAttempts): void
    {
        $stmt = db()->prepare('UPDATE password_resets SET attempts = attempts + 1 WHERE email = :email');
        $stmt->execute([':email' => $email]);

        $stmtCheck = db()->prepare('SELECT attempts FROM password_resets WHERE email = :email LIMIT 1');
        $stmtCheck->execute([':email' => $email]);
        $row = $stmtCheck->fetch();
        if ($row && (int)$row['attempts'] >= $maxAttempts) {
            self::deleteByEmail($email);
        }
    }

    public static function deleteByEmail(string $email): void
    {
        $stmt = db()->prepare('DELETE FROM password_resets WHERE email = :email');
        $stmt->execute([':email' => $email]);
    }
}
