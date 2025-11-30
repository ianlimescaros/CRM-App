<?php

require_once __DIR__ . '/../config/database.php';

class Lead
{
    public static function all(int $userId, array $filters = [], array $pagination = []): array
    {
        $sql = 'SELECT * FROM leads WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $sql .= ' AND source = :source';
            $params[':source'] = $filters['source'];
        }

        $orderBy = $pagination['order_by'] ?? 'created_at';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'DESC');
        $allowedOrder = ['created_at', 'updated_at', 'name', 'status', 'source', 'budget', 'last_contact_at', 'owner_id'];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'created_at';
        }
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'DESC';
        }
        $sql .= " ORDER BY {$orderBy} {$orderDir}";

        if (!empty($pagination['limit'])) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params[':limit'] = (int)$pagination['limit'];
            $params[':offset'] = (int)($pagination['offset'] ?? 0);
        }

        $stmt = db()->prepare($sql);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($k, $v, $type);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(int $userId, array $filters = []): int
    {
        $sql = 'SELECT COUNT(*) as cnt FROM leads WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $sql .= ' AND source = :source';
            $params[':source'] = $filters['source'];
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM leads WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $lead = $stmt->fetch();
        return $lead ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO leads (user_id, owner_id, name, email, phone, status, source, budget, notes, last_contact_at, created_at)
             VALUES (:user_id, :owner_id, :name, :email, :phone, :status, :source, :budget, :notes, :last_contact_at, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':owner_id' => $data['owner_id'] ?? null,
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':status' => $data['status'] ?? 'new',
            ':source' => $data['source'] ?? null,
            ':budget' => $data['budget'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':last_contact_at' => $data['last_contact_at'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateLead(int $userId, int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE leads SET name = :name, email = :email, phone = :phone, status = :status,
             source = :source, budget = :budget, notes = :notes, owner_id = :owner_id, last_contact_at = :last_contact_at
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':status' => $data['status'],
            ':source' => $data['source'] ?? null,
            ':budget' => $data['budget'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':owner_id' => $data['owner_id'] ?? null,
            ':last_contact_at' => $data['last_contact_at'] ?? null,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteLead(int $userId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM leads WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function bulkUpdateStatus(int $userId, array $ids, string $status): int
    {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$status, $userId], $ids);
        $stmt = db()->prepare("UPDATE leads SET status = ? WHERE user_id = ? AND id IN ($placeholders)");
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
