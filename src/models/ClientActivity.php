<?php
// Client activity data access.

require_once __DIR__ . '/../config/database.php';

class ClientActivity
{
    public static function listForClient(int $userId, int $clientId): array
    {
        $stmt = db()->prepare('SELECT * FROM client_activities WHERE user_id = :user_id AND client_id = :client_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':client_id' => $clientId]);
        return $stmt->fetchAll();
    }

    public static function listForClientPaginated(int $userId, int $clientId, int $limit, int $offset): array
    {
        $stmt = db()->prepare('SELECT * FROM client_activities WHERE user_id = :user_id AND client_id = :client_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countForClient(int $userId, int $clientId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) as cnt FROM client_activities WHERE user_id = :user_id AND client_id = :client_id');
        $stmt->execute([':user_id' => $userId, ':client_id' => $clientId]);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    public static function create(int $userId, int $clientId, string $type, string $detail): array
    {
        $allowed = ['call', 'email', 'meeting', 'note', 'task'];
        if (!in_array($type, $allowed, true)) {
            $type = 'note';
        }
        $stmt = db()->prepare('INSERT INTO client_activities (user_id, client_id, type, detail, created_at) VALUES (:user_id, :client_id, :type, :detail, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':client_id' => $clientId,
            ':type' => $type,
            ':detail' => $detail,
        ]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'client_id' => $clientId,
            'type' => $type,
            'detail' => $detail,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
