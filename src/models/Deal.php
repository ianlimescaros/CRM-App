<?php

require_once __DIR__ . '/../config/database.php';

class Deal
{
    private static function clientColumn(): string
    {
        static $col = null;
        if ($col !== null) {
            return $col;
        }
        try {
            db()->query('SELECT client_id FROM deals LIMIT 0');
            $col = 'client_id';
        } catch (PDOException $e) {
            $col = 'contact_id';
        }
        return $col;
    }

    private static function clientTable(): string
    {
        static $table = null;
        if ($table !== null) {
            return $table;
        }
        try {
            db()->query('SELECT 1 FROM clients LIMIT 1');
            $table = 'clients';
        } catch (PDOException $e) {
            $table = 'contacts';
        }
        return $table;
    }

    public static function all(int $userId, array $filters = [], array $pagination = []): array
    {
        $clientCol = self::clientColumn();
        $clientTable = self::clientTable();
        $clientAlias = $clientCol === 'client_id' ? 'd.client_id' : 'd.contact_id AS client_id';
        $sql = "SELECT d.*, {$clientAlias}, c.full_name AS client_name FROM deals d LEFT JOIN {$clientTable} c ON c.id = d.{$clientCol} AND c.user_id = d.user_id WHERE d.user_id = :user_id";
        $params = [':user_id' => $userId];

        if (!empty($filters['stage'])) {
            $sql .= ' AND stage = :stage';
            $params[':stage'] = $filters['stage'];
        }
        if (!empty($filters['client_id'])) {
            $sql .= " AND {$clientCol} = :client_id";
            $params[':client_id'] = (int)$filters['client_id'];
        }
        if (!empty($filters['lead_id'])) {
            $sql .= ' AND lead_id = :lead_id';
            $params[':lead_id'] = (int)$filters['lead_id'];
        }
        if (!empty($filters['title'])) {
            $sql .= ' AND title LIKE :title';
            $params[':title'] = '%' . $filters['title'] . '%';
        }

        $orderBy = $pagination['order_by'] ?? 'created_at';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'DESC');
        $allowedOrder = ['created_at', 'title', 'stage', 'amount', 'currency', 'close_date', 'location', 'client_name'];
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
        $clientCol = self::clientColumn();
        $sql = 'SELECT COUNT(*) as cnt FROM deals WHERE user_id = :user_id';
        $params = [':user_id' => $userId];
        if (!empty($filters['stage'])) {
            $sql .= ' AND stage = :stage';
            $params[':stage'] = $filters['stage'];
        }
        if (!empty($filters['client_id'])) {
            $sql .= " AND {$clientCol} = :client_id";
            $params[':client_id'] = (int)$filters['client_id'];
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
        $clientCol = self::clientColumn();
        $clientTable = self::clientTable();
        $clientAlias = $clientCol === 'client_id' ? 'd.client_id' : 'd.contact_id AS client_id';
        $sql = "SELECT d.*, {$clientAlias}, c.full_name AS client_name FROM deals d LEFT JOIN {$clientTable} c ON c.id = d.{$clientCol} AND c.user_id = d.user_id WHERE d.id = :id AND d.user_id = :user_id LIMIT 1";
        $stmt = db()->prepare($sql);
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        $deal = $stmt->fetch();
        return $deal ?: null;
    }

    public static function create(int $userId, array $data): int
    {
        $clientCol = self::clientColumn();
        $stmt = db()->prepare(
            "INSERT INTO deals (user_id, lead_id, {$clientCol}, title, stage, amount, currency, location, property_detail, close_date, created_at)
             VALUES (:user_id, :lead_id, :client_id, :title, :stage, :amount, :currency, :location, :property_detail, :close_date, NOW())"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':lead_id' => $data['lead_id'] ?? null,
            ':client_id' => $data['client_id'] ?? null,
            ':title' => $data['title'],
            ':stage' => $data['stage'],
            ':amount' => $data['amount'] ?? 0,
            ':currency' => $data['currency'] ?? null,
            ':location' => $data['location'] ?? null,
            ':property_detail' => $data['property_detail'] ?? null,
            ':close_date' => $data['close_date'] ?? null,
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateDeal(int $userId, int $id, array $data): bool
    {
        $clientCol = self::clientColumn();
        $stmt = db()->prepare(
            "UPDATE deals SET title = :title, stage = :stage, amount = :amount, currency = :currency,
             location = :location, property_detail = :property_detail, close_date = :close_date, lead_id = :lead_id, {$clientCol} = :client_id
             WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute([
            ':title' => $data['title'],
            ':stage' => $data['stage'],
            ':amount' => $data['amount'] ?? 0,
            ':currency' => $data['currency'] ?? null,
            ':location' => $data['location'] ?? null,
            ':property_detail' => $data['property_detail'] ?? null,
            ':close_date' => $data['close_date'] ?? null,
            ':lead_id' => $data['lead_id'] ?? null,
            ':client_id' => $data['client_id'] ?? null,
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
