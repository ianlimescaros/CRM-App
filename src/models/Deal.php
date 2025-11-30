<?php

require_once __DIR__ . '/../config/database.php';

class Deal
{
    public static function all(int $userId, array $filters = [], array $pagination = []): array
    {
        $sql = 'SELECT * FROM deals WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        if (!empty($filters['stage'])) {
            $sql .= ' AND stage = :stage';
            $params[':stage'] = $filters['stage'];
        }
        if (!empty($filters['contact_id'])) {
            $sql .= ' AND contact_id = :contact_id';
            $params[':contact_id'] = (int)$filters['contact_id'];
        }
        if (!empty($filters['lead_id'])) {
            $sql .= ' AND lead_id = :lead_id';
            $params[':lead_id'] = (int)$filters['lead_id'];
        }

        $orderBy = $pagination['order_by'] ?? 'created_at';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'DESC');
        $allowedOrder = ['created_at', 'title', 'stage', 'amount', 'close_date'];
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
        $sql = 'SELECT COUNT(*) as cnt FROM deals WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['stage'])) {
            $sql .= ' AND stage = :stage';
            $params[':stage'] = $filters['stage'];
        }
        if (!empty($filters['contact_id'])) {
            $sql .= ' AND contact_id = :contact_id';
            $params[':contact_id'] = (int)$filters['contact_id'];
        }
        if (!empty($filters['lead_id'])) {
            $sql .= ' AND lead_id = :lead_id';
            $params[':lead_id'] = (int)$filters['lead_id'];
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return (int)($row['cnt'] ?? 0);
    }

    public static function find(int $userId, int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM deals WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $deal = $stmt->fetch();
        return $deal ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO deals (user_id, lead_id, contact_id, title, stage, amount, close_date, created_at)
             VALUES (:user_id, :lead_id, :contact_id, :title, :stage, :amount, :close_date, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':lead_id' => $data['lead_id'] ?? null,
            ':contact_id' => $data['contact_id'] ?? null,
            ':title' => $data['title'],
            ':stage' => $data['stage'],
            ':amount' => $data['amount'] ?? 0,
            ':close_date' => $data['close_date'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateDeal(int $userId, int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE deals SET title = :title, stage = :stage, amount = :amount,
             close_date = :close_date, lead_id = :lead_id, contact_id = :contact_id
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':stage' => $data['stage'],
            ':amount' => $data['amount'] ?? 0,
            ':close_date' => $data['close_date'] ?? null,
            ':lead_id' => $data['lead_id'] ?? null,
            ':contact_id' => $data['contact_id'] ?? null,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteDeal(int $userId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM deals WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
