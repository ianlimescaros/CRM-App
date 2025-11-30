<?php

require_once __DIR__ . '/../config/database.php';

class ContactFile
{
    public static function listForContact(int $userId, int $contactId): array
    {
        $stmt = db()->prepare('SELECT * FROM contact_files WHERE user_id = :user_id AND contact_id = :contact_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':contact_id' => $contactId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, int $contactId, string $name, ?string $url = null, ?string $sizeLabel = null): array
    {
        $stmt = db()->prepare('INSERT INTO contact_files (user_id, contact_id, name, url, size_label, created_at) VALUES (:user_id, :contact_id, :name, :url, :size_label, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_id' => $contactId,
            ':name' => $name,
            ':url' => $url,
            ':size_label' => $sizeLabel,
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
}
