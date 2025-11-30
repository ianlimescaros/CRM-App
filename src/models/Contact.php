<?php

require_once __DIR__ . '/../config/database.php';

class Contact
{
    public static function all(int $userId, array $pagination = [], array $filters = []): array
    {
        $sql = 'SELECT * FROM contacts WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['search'])) {
            $sql .= ' AND (full_name LIKE :q OR email LIKE :q OR company LIKE :q)';
            $params[':q'] = '%' . $filters['search'] . '%';
        }
        $orderBy = $pagination['order_by'] ?? 'created_at';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'DESC');
        $allowedOrder = ['created_at', 'full_name', 'email'];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'created_at';
        }
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'DESC';
        }
        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        if (!empty($pagination['limit'])) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }
        $stmt = db()->prepare($sql);
        if (!empty($pagination['limit'])) {
            $params[':limit'] = (int)$pagination['limit'];
            $params[':offset'] = (int)($pagination['offset'] ?? 0);
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAll(int $userId, array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) as cnt FROM contacts WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['search'])) {
            $sql .= ' AND (full_name LIKE :q OR email LIKE :q OR company LIKE :q)';
            $params[':q'] = '%' . $filters['search'] . '%';
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM contacts WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $contact = $stmt->fetch();
        return $contact ?: null;
    }

    public static function findByEmail(int $userId, string $email): ?array
    {
        $stmt = db()->prepare('SELECT * FROM contacts WHERE email = :email AND user_id = :user_id LIMIT 1');
        $stmt->execute([':email' => $email, ':user_id' => $userId]);
        $contact = $stmt->fetch();
        return $contact ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO contacts (user_id, full_name, email, phone, company, position, created_at)
             VALUES (:user_id, :full_name, :email, :phone, :company, :position, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':full_name' => $data['full_name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':company' => $data['company'] ?? null,
            ':position' => $data['position'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateContact(int $userId, int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE contacts SET full_name = :full_name, email = :email, phone = :phone,
             company = :company, position = :position
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':company' => $data['company'] ?? null,
            ':position' => $data['position'] ?? null,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteContact(int $userId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM contacts WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
