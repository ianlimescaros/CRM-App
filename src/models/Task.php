<?php

require_once __DIR__ . '/../config/database.php';

class Task
{
    public static function all(int $userId, array $filters = [], array $pagination = []): array
    {
        $sql = 'SELECT * FROM tasks WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['due_date'])) {
            $sql .= ' AND due_date = :due_date';
            $params[':due_date'] = $filters['due_date'];
        }
        if (!empty($filters['contact_id'])) {
            $sql .= ' AND contact_id = :contact_id';
            $params[':contact_id'] = (int)$filters['contact_id'];
        }
        if (!empty($filters['lead_id'])) {
            $sql .= ' AND lead_id = :lead_id';
            $params[':lead_id'] = (int)$filters['lead_id'];
        }
        if (!empty($filters['title'])) {
            $sql .= ' AND title LIKE :title';
            $params[':title'] = '%' . $filters['title'] . '%';
        }

        $orderBy = $pagination['order_by'] ?? 'due_date';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'ASC');
        $allowedOrder = ['due_date', 'created_at', 'title', 'status'];
        if (!in_array($orderBy, $allowedOrder, true)) {
            $orderBy = 'due_date';
        }
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'ASC';
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
        $sql = 'SELECT COUNT(*) as cnt FROM tasks WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['due_date'])) {
            $sql .= ' AND due_date = :due_date';
            $params[':due_date'] = $filters['due_date'];
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
        $stmt = db()->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO tasks (user_id, lead_id, contact_id, title, description, due_date, status, created_at)
             VALUES (:user_id, :lead_id, :contact_id, :title, :description, :due_date, :status, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':lead_id' => $data['lead_id'] ?? null,
            ':contact_id' => $data['contact_id'] ?? null,
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':due_date' => $data['due_date'] ?? null,
            ':status' => $data['status'] ?? 'pending',
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateTask(int $userId, int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE tasks SET title = :title, description = :description, due_date = :due_date,
             status = :status, lead_id = :lead_id, contact_id = :contact_id
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':due_date' => $data['due_date'] ?? null,
            ':status' => $data['status'],
            ':lead_id' => $data['lead_id'] ?? null,
            ':contact_id' => $data['contact_id'] ?? null,
            ':id' => $id,
            ':user_id' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteTask(int $userId, int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
