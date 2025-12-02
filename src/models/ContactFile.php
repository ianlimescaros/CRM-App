<?php

require_once __DIR__ . '/../config/database.php';

class ContactFile
{
    private static function stripSensitive(array $row): array
    {
        unset($row['disk_path']);
        return $row;
    }

    public static function listForContact(int $userId, int $contactId): array
    {
        $stmt = db()->prepare('SELECT * FROM contact_files WHERE user_id = :user_id AND contact_id = :contact_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':contact_id' => $contactId]);
        $rows = $stmt->fetchAll();
        return array_map([self::class, 'stripSensitive'], $rows);
    }

    public static function create(int $userId, int $contactId, string $name, ?string $url = null, ?string $sizeLabel = null, ?string $diskPath = null): array
    {
        $stmt = db()->prepare('INSERT INTO contact_files (user_id, contact_id, name, url, size_label, disk_path, created_at) VALUES (:user_id, :contact_id, :name, :url, :size_label, :disk_path, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_id' => $contactId,
            ':name' => $name,
            ':url' => $url,
            ':size_label' => $sizeLabel,
            ':disk_path' => $diskPath,
        ]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'contact_id' => $contactId,
            'name' => $name,
            'url' => $url,
            'size_label' => $sizeLabel,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function find(int $userId, int $contactId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM contact_files WHERE id = :id AND user_id = :user_id AND contact_id = :contact_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId, ':contact_id' => $contactId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function deleteFile(int $userId, int $contactId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM contact_files WHERE id = :id AND user_id = :user_id AND contact_id = :contact_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId, ':contact_id' => $contactId]);
        return $stmt->rowCount() > 0;
    }
}
