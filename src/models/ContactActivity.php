<?php

require_once __DIR__ . '/../config/database.php';

class ContactActivity
{
    public static function listForContact(int $userId, int $contactId): array
    {
        $stmt = db()->prepare('SELECT * FROM contact_activities WHERE user_id = :user_id AND contact_id = :contact_id ORDER BY created_at DESC');
        $stmt->execute([':user_id' => $userId, ':contact_id' => $contactId]);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, int $contactId, string $type, string $detail): array
    {
        $allowed = ['call', 'email', 'meeting', 'note', 'task'];
        if (!in_array($type, $allowed, true)) {
            $type = 'note';
        }
        $stmt = db()->prepare('INSERT INTO contact_activities (user_id, contact_id, type, detail, created_at) VALUES (:user_id, :contact_id, :type, :detail, NOW())');
        $stmt->execute([
            ':user_id' => $userId,
            ':contact_id' => $contactId,
            ':type' => $type,
            ':detail' => $detail,
        ]);
        return [
            'id' => (int)db()->lastInsertId(),
            'user_id' => $userId,
            'contact_id' => $contactId,
            'type' => $type,
            'detail' => $detail,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}
