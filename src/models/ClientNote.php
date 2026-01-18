<?php
// Client note data access.

require_once __DIR__ . '/../config/database.php';

class ClientNote
{
    public static function listForClient(int $userId, int $clientId): array
    {
        $stmt = db()->prepare('SELECT * FROM client_notes WHERE user_id = :user_id AND client_id = :client_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, int $clientId, string $content): array
    {
        $stmt = db()->prepare('INSERT INTO client_notes (user_id, client_id, content, created_at) VALUES (:user_id, :client_id, :content, NOW())');
        $stmt->execute([':user_id' => $userId, ':client_id' => $clientId, ':content' => $content]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'client_id' => $clientId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
