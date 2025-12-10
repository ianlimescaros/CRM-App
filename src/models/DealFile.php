<?php

require_once __DIR__ . '/../config/database.php';

class DealFile
{
    private static function stripSensitive(array $row): array
    {
        unset($row['disk_path']);
        return $row;
    }

    public static function listForDeal(int $userId, int $dealId): array
    {
        $stmt = db()->prepare('SELECT * FROM deal_files WHERE user_id = :user_id AND deal_id = :deal_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':deal_id' => $dealId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['has_local'] = !empty($row['disk_path']);
            $row = self::stripSensitive($row);
        }
        return $rows;
    }

    public static function create(int $userId, int $dealId, string $name, ?string $url = null, ?string $sizeLabel = null, ?string $diskPath = null): array
    {
        $stmt = db()->prepare('INSERT INTO deal_files (user_id, deal_id, name, url, size_label, disk_path, created_at) VALUES (:user_id, :deal_id, :name, :url, :size_label, :disk_path, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':deal_id' => $dealId,
            ':name' => $name,
            ':url' => $url,
            ':size_label' => $sizeLabel,
            ':disk_path' => $diskPath,
        ]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'deal_id' => $dealId,
            'name' => $name,
            'url' => $url,
            'size_label' => $sizeLabel,
            'created_at' => date('Y-m-d H:i:s'),
            'has_local' => $diskPath !== null,
        ];
    }

    public static function find(int $userId, int $dealId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM deal_files WHERE id = :id AND user_id = :user_id AND deal_id = :deal_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId, ':deal_id' => $dealId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function deleteFile(int $userId, int $dealId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM deal_files WHERE id = :id AND user_id = :user_id AND deal_id = :deal_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId, ':deal_id' => $dealId]);
        return $stmt->rowCount() > 0;
    }
}
