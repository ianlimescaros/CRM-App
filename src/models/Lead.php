<?php
// Lead data access and filters.

require_once __DIR__ . '/../config/database.php';

class Lead
{
    /**
     * @param int $userId
     * @param array<string,mixed> $filters
     * @param array<string,mixed> $pagination
     * @return array<int,array<string,mixed>>
     */
    public static function all(int $userId, array $filters = [], array $pagination = []): array
    {
        $sql = 'SELECT * FROM leads WHERE user_id = :user_id';
        $params = [':user_id' => $userId];

        $archivedFilter = $filters['archived'] ?? null;
        if ($archivedFilter === 'all') {
            // no archived filter
        } elseif ($archivedFilter === 'archived' || $archivedFilter === '1' || $archivedFilter === 1 || $archivedFilter === true) {
            $sql .= ' AND archived_at IS NOT NULL';
        } else {
            $sql .= ' AND archived_at IS NULL';
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $sql .= ' AND source = :source';
            $params[':source'] = $filters['source'];
        }
        if (!empty($filters['search'])) {
            // simple multi-column search
            $sql .= ' AND (name LIKE :q_name OR email LIKE :q_email OR phone LIKE :q_phone OR interested_property LIKE :q_property OR area LIKE :q_area OR notes LIKE :q_notes)';
            $q = '%' . $filters['search'] . '%';
            $params[':q_name'] = $q;
            $params[':q_email'] = $q;
            $params[':q_phone'] = $q;
            $params[':q_property'] = $q;
            $params[':q_area'] = $q;
            $params[':q_notes'] = $q;
        }
        if (!empty($filters['created_from'])) {
            $sql .= ' AND created_at >= :created_from';
            $params[':created_from'] = $filters['created_from'] . ' 00:00:00';
        }
        if (!empty($filters['created_to'])) {
            $sql .= ' AND created_at <= :created_to';
            $params[':created_to'] = $filters['created_to'] . ' 23:59:59';
        }

        $orderBy = $pagination['order_by'] ?? 'created_at';
        $orderDir = strtoupper($pagination['order_dir'] ?? 'DESC');
        $allowedOrder = ['created_at', 'updated_at', 'name', 'status', 'source', 'property_for', 'payment_option', 'interested_property', 'area', 'budget', 'currency', 'last_contact_at', 'owner_id', 'archived_at'];
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
        $archivedFilter = $filters['archived'] ?? null;
        if ($archivedFilter === 'all') {
            // no archived filter
        } elseif ($archivedFilter === 'archived' || $archivedFilter === '1' || $archivedFilter === 1 || $archivedFilter === true) {
            $sql .= ' AND archived_at IS NOT NULL';
        } else {
            $sql .= ' AND archived_at IS NULL';
        }
        if (!empty($filters['status'])) {
            $sql .= ' AND status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['source'])) {
            $sql .= ' AND source = :source';
            $params[':source'] = $filters['source'];
        }
        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE :q_name OR email LIKE :q_email OR phone LIKE :q_phone OR interested_property LIKE :q_property OR area LIKE :q_area OR notes LIKE :q_notes)';
            $q = '%' . $filters['search'] . '%';
            $params[':q_name'] = $q;
            $params[':q_email'] = $q;
            $params[':q_phone'] = $q;
            $params[':q_property'] = $q;
            $params[':q_area'] = $q;
            $params[':q_notes'] = $q;
        }
        if (!empty($filters['created_from'])) {
            $sql .= ' AND created_at >= :created_from';
            $params[':created_from'] = $filters['created_from'] . ' 00:00:00';
        }
        if (!empty($filters['created_to'])) {
            $sql .= ' AND created_at <= :created_to';
            $params[':created_to'] = $filters['created_to'] . ' 23:59:59';
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
            'INSERT INTO leads (user_id, owner_id, property_for, payment_option, interested_property, area, name, email, phone, status, source, budget, currency, notes, last_contact_at, created_at)
             VALUES (:user_id, :owner_id, :property_for, :payment_option, :interested_property, :area, :name, :email, :phone, :status, :source, :budget, :currency, :notes, :last_contact_at, NOW())'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':owner_id' => $data['owner_id'] ?? null,
            ':property_for' => $data['property_for'] ?? null,
            ':payment_option' => $data['payment_option'] ?? null,
            ':interested_property' => $data['interested_property'] ?? null,
            ':area' => $data['area'] ?? null,
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':status' => $data['status'] ?? 'new',
            ':source' => $data['source'] ?? null,
            ':budget' => $data['budget'] ?? null,
            ':currency' => $data['currency'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':last_contact_at' => $data['last_contact_at'] ?? null,
        ]);

        $id = (int)db()->lastInsertId();

        // Lightweight create logging for debugging duplicate/ghost entries
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0777, true) && !is_dir($logDir)) {
                error_log('Failed to create log dir: ' . $logDir);
            }
        }
        $payload = $data;
        // Truncate potentially large fields
        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (strlen($payloadJson) > 1000) {
            $payloadJson = substr($payloadJson, 0, 1000) . '...';
        }
        $logEntry = json_encode([
            'time' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'action' => 'create_lead',
            'user_id' => $userId,
            'lead_id' => $id,
            'payload' => $payloadJson,
        ], JSON_UNESCAPED_SLASHES);
        if (file_put_contents($logDir . '/creates.log', $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
            error_log('Failed to write creates.log in ' . $logDir);
        }

        return $id;
    }

    public static function updateLead(int $userId, int $id, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE leads SET name = :name, email = :email, phone = :phone, status = :status,
             source = :source, property_for = :property_for, payment_option = :payment_option, interested_property = :interested_property, area = :area, budget = :budget, currency = :currency, notes = :notes, owner_id = :owner_id, last_contact_at = :last_contact_at
             WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':status' => $data['status'],
            ':source' => $data['source'] ?? null,
            ':property_for' => $data['property_for'] ?? null,
            ':payment_option' => $data['payment_option'] ?? null,
            ':interested_property' => $data['interested_property'] ?? null,
            ':area' => $data['area'] ?? null,
            ':budget' => $data['budget'] ?? null,
            ':currency' => $data['currency'] ?? null,
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

    public static function bulkArchive(int $userId, array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$userId], $ids);
        $stmt = db()->prepare("UPDATE leads SET archived_at = NOW() WHERE user_id = ? AND archived_at IS NULL AND id IN ($placeholders)");
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function bulkRestore(int $userId, array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$userId], $ids);
        $stmt = db()->prepare("UPDATE leads SET archived_at = NULL WHERE user_id = ? AND archived_at IS NOT NULL AND id IN ($placeholders)");
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function archiveLead(int $userId, int $id): bool
    {
        $stmt = db()->prepare('UPDATE leads SET archived_at = NOW() WHERE id = :id AND user_id = :user_id AND archived_at IS NULL');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function restoreLead(int $userId, int $id): bool
    {
        $stmt = db()->prepare('UPDATE leads SET archived_at = NULL WHERE id = :id AND user_id = :user_id AND archived_at IS NOT NULL');
        $stmt->execute([':id' => $id, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
