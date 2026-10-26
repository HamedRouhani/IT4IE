<?php
namespace App\Software\Inventory\Models;

use App\Core\Database;

final class InventoryMovement
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function record(int $workspaceId, int $itemId, int $userId, string $type, float $quantity, ?string $note, string $direction = 'increase', float $receivedAgainstOrder = 0.0): void
    {
        if (!in_array($type, ['receipt', 'issue', 'adjustment'], true) || !is_finite($quantity) || $quantity <= 0) {
            throw new \InvalidArgumentException('نوع و مقدار گردش موجودی معتبر نیست.');
        }
        if (!is_finite($receivedAgainstOrder) || $receivedAgainstOrder < 0 || $receivedAgainstOrder > $quantity) {
            throw new \InvalidArgumentException('مقدار دریافت‌شده از سفارش باید بین صفر و مقدار دریافت باشد.');
        }
        if ($type !== 'receipt' && $receivedAgainstOrder > 0) {
            throw new \InvalidArgumentException('تسویه سفارش در راه فقط برای گردش دریافت کاربرد دارد.');
        }
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT on_hand, on_order FROM inv_items WHERE id=? AND workspace_id=? AND is_active=1 FOR UPDATE');
            $stmt->execute([$itemId, $workspaceId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$row) throw new \RuntimeException('قلم موجودی پیدا نشد.');
            $delta = match ($type) {
                'receipt' => $quantity,
                'issue' => -$quantity,
                default => $quantity,
            };
            if ($type === 'adjustment') {
                if (!in_array($direction, ['increase', 'decrease'], true)) throw new \InvalidArgumentException('جهت تعدیل معتبر نیست.');
                $delta = $direction === 'decrease' ? -$quantity : $quantity;
            }
            $balance = (float) $row['on_hand'] + $delta;
            if ($balance < -0.000001) throw new \InvalidArgumentException('موجودی خروجی از موجودی فعلی بیشتر است.');
            $balance = max(0.0, $balance);
            $onOrder = (float) $row['on_order'];
            if ($receivedAgainstOrder > $onOrder + 0.000001) {
                throw new \InvalidArgumentException('مقدار تسویه‌شده از سفارش در راه بیشتر است.');
            }
            $onOrder = max(0.0, $onOrder - $receivedAgainstOrder);
            $stmt = $this->db->prepare('UPDATE inv_items SET on_hand=?, on_order=? WHERE id=? AND workspace_id=?');
            $stmt->execute([$balance, $onOrder, $itemId, $workspaceId]);
            $stmt = $this->db->prepare(
                'INSERT INTO inv_movements (workspace_id,item_id,user_id,movement_type,quantity,on_order_received,balance_after,note)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([$workspaceId, $itemId, $userId, $type, $delta, $receivedAgainstOrder, $balance, $note]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function latest(int $workspaceId, int $limit = 10): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            'SELECT m.*, i.sku, i.name AS item_name, i.unit
             FROM inv_movements m JOIN inv_items i ON i.id=m.item_id AND i.workspace_id=m.workspace_id
             WHERE m.workspace_id=? ORDER BY m.created_at DESC, m.id DESC LIMIT ' . $limit
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function all(int $workspaceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*, i.sku, i.name AS item_name, i.unit
             FROM inv_movements m JOIN inv_items i ON i.id=m.item_id AND i.workspace_id=m.workspace_id
             WHERE m.workspace_id=? ORDER BY m.created_at DESC, m.id DESC'
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count(int $workspaceId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM inv_movements WHERE workspace_id=?');
        $stmt->execute([$workspaceId]);
        return (int) $stmt->fetchColumn();
    }
}
