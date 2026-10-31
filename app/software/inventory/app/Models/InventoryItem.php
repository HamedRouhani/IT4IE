<?php
namespace App\Software\Inventory\Models;

use App\Core\Database;

final class InventoryItem
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(int $workspaceId, bool $includeInactive = false, bool $onlyInactive = false): array
    {
        $sql = 'SELECT * FROM inv_items WHERE workspace_id = ?';
        if ($onlyInactive) $sql .= ' AND is_active = 0';
        elseif (!$includeInactive) $sql .= ' AND is_active = 1';
        $sql .= ' ORDER BY name ASC, id DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $workspaceId, int $id, bool $includeInactive = false): ?array
    {
        $sql = 'SELECT * FROM inv_items WHERE workspace_id = ? AND id = ?';
        if (!$includeInactive) $sql .= ' AND is_active = 1';
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute([$workspaceId, $id]);
        $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function skuExists(int $workspaceId, string $sku, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM inv_items WHERE workspace_id = ? AND sku = ?';
        $params = [$workspaceId, $sku];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function create(int $workspaceId, array $data, int $userId): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO inv_items
                 (workspace_id, sku, name, category, unit, unit_cost, on_hand, on_order,
                  annual_demand, daily_demand_std, lead_time_days, order_cost, holding_rate, service_level, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $workspaceId, $data['sku'], $data['name'], $data['category'], $data['unit'], $data['unit_cost'],
                $data['on_hand'], $data['on_order'], $data['annual_demand'], $data['daily_demand_std'], $data['lead_time_days'],
                $data['order_cost'], $data['holding_rate'], $data['service_level'], $userId,
            ]);
            $itemId = (int) $this->db->lastInsertId();
            if ((float) $data['on_hand'] > 0) {
                $stmt = $this->db->prepare(
                    "INSERT INTO inv_movements (workspace_id, item_id, user_id, movement_type, quantity, balance_after, note)
                     VALUES (?, ?, ?, 'opening', ?, ?, 'موجودی اولیه هنگام ثبت قلم')"
                );
                $stmt->execute([$workspaceId, $itemId, $userId, $data['on_hand'], $data['on_hand']]);
            }
            $this->db->commit();
            return $itemId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function update(int $workspaceId, int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE inv_items SET sku=?, name=?, category=?, unit=?, unit_cost=?, annual_demand=?,
             daily_demand_std=?, lead_time_days=?, order_cost=?, holding_rate=?, service_level=?
             WHERE workspace_id=? AND id=? AND is_active=1'
        );
        return $stmt->execute([
            $data['sku'], $data['name'], $data['category'], $data['unit'], $data['unit_cost'],
            $data['annual_demand'], $data['daily_demand_std'], $data['lead_time_days'], $data['order_cost'],
            $data['holding_rate'], $data['service_level'], $workspaceId, $id,
        ]);
    }

    public function archive(int $workspaceId, int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE inv_items SET is_active=0 WHERE workspace_id=? AND id=? AND is_active=1');
        return $stmt->execute([$workspaceId, $id]);
    }

    public function restore(int $workspaceId, int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE inv_items SET is_active=1 WHERE workspace_id=? AND id=? AND is_active=0');
        return $stmt->execute([$workspaceId, $id]);
    }

    public function stats(int $workspaceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS item_count,
                    COALESCE(SUM(on_hand * unit_cost), 0) AS inventory_value
             FROM inv_items WHERE workspace_id=? AND is_active=1'
        );
        $stmt->execute([$workspaceId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return [
            'item_count' => (int) ($row['item_count'] ?? 0),
            'inventory_value' => (float) ($row['inventory_value'] ?? 0),
            'reorder_count' => 0,
        ];
    }
}
