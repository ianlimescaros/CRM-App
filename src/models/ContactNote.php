<?php

require_once __DIR__ . '/../config/database.php';

class ContactNote
{
    public static function listForContact(int $userId, int $contactId): array
    {
        $stmt = db()->prepare('SELECT * FROM contact_notes WHERE user_id = :user_id AND contact_id = :contact_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':contact_id' => $contactId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, int $contactId, string $content): array
    {
        $stmt = db()->prepare('INSERT INTO contact_notes (user_id, contact_id, content, created_at) VALUES (:user_id, :contact_id, :content, NOW())');
        $stmt->execute([':user_id' => $userId, ':contact_id' => $contactId, ':content' => $content]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'contact_id' => $contactId,
            'content' => $content,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
