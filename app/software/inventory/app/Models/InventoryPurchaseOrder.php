<?php
namespace App\Software\Inventory\Models;

use App\Core\Database;

final class InventoryPurchaseOrder
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(int $workspaceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT po.*, COUNT(pol.id) AS line_count, COALESCE(SUM(pol.ordered_quantity * pol.unit_cost), 0) AS order_value,
                    COALESCE(SUM(pol.ordered_quantity - pol.received_quantity), 0) AS open_quantity
             FROM inv_purchase_orders po LEFT JOIN inv_purchase_order_lines pol
               ON pol.purchase_order_id=po.id AND pol.workspace_id=po.workspace_id
             WHERE po.workspace_id=? GROUP BY po.id ORDER BY po.created_at DESC, po.id DESC'
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function page(int $workspaceId, int $limit = 50, int $offset = 0): array
    {
        $limit = max(1, min(100, $limit));
        $offset = max(0, $offset);
        $stmt = $this->db->prepare(
            'SELECT po.*, COUNT(pol.id) AS line_count, COALESCE(SUM(pol.ordered_quantity * pol.unit_cost), 0) AS order_value,
                    COALESCE(SUM(pol.ordered_quantity - pol.received_quantity), 0) AS open_quantity
             FROM (SELECT * FROM inv_purchase_orders WHERE workspace_id=? ORDER BY created_at DESC, id DESC LIMIT ' . $limit . ' OFFSET ' . $offset . ') po
             LEFT JOIN inv_purchase_order_lines pol ON pol.purchase_order_id=po.id AND pol.workspace_id=po.workspace_id
             GROUP BY po.id ORDER BY po.created_at DESC, po.id DESC'
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count(int $workspaceId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM inv_purchase_orders WHERE workspace_id=?');
        $stmt->execute([$workspaceId]);
        return (int) $stmt->fetchColumn();
    }

    public function recent(int $workspaceId, int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));
        $stmt = $this->db->prepare(
            'SELECT po.*, COUNT(pol.id) AS line_count, COALESCE(SUM(pol.ordered_quantity * pol.unit_cost), 0) AS order_value,
                    COALESCE(SUM(pol.ordered_quantity - pol.received_quantity), 0) AS open_quantity
             FROM (SELECT * FROM inv_purchase_orders WHERE workspace_id=? ORDER BY created_at DESC, id DESC LIMIT ' . $limit . ') po
             LEFT JOIN inv_purchase_order_lines pol
               ON pol.purchase_order_id=po.id AND pol.workspace_id=po.workspace_id
             GROUP BY po.id ORDER BY po.created_at DESC, po.id DESC'
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countOpen(int $workspaceId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM inv_purchase_orders WHERE workspace_id=? AND status IN ('ordered','partial')");
        $stmt->execute([$workspaceId]);
        return (int) $stmt->fetchColumn();
    }

    public function find(int $workspaceId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM inv_purchase_orders WHERE workspace_id=? AND id=? LIMIT 1');
        $stmt->execute([$workspaceId, $id]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$order) return null;
        $stmt = $this->db->prepare(
            'SELECT pol.*, i.sku, i.name AS item_name, i.unit, i.on_hand, i.on_order
             FROM inv_purchase_order_lines pol JOIN inv_items i
               ON i.id=pol.item_id AND i.workspace_id=pol.workspace_id
             WHERE pol.workspace_id=? AND pol.purchase_order_id=? ORDER BY pol.id'
        );
        $stmt->execute([$workspaceId, $id]);
        $order['lines'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $order;
    }

    public function exportLines(int $workspaceId): array
    {
        $stmt = $this->db->prepare(
            'SELECT po.order_no, po.supplier_name, po.status, po.expected_date, po.notes,
                    pol.ordered_quantity, pol.received_quantity, pol.unit_cost, i.sku, i.name AS item_name
             FROM inv_purchase_orders po
             JOIN inv_purchase_order_lines pol ON pol.purchase_order_id=po.id AND pol.workspace_id=po.workspace_id
             JOIN inv_items i ON i.id=pol.item_id AND i.workspace_id=pol.workspace_id
             WHERE po.workspace_id=? ORDER BY po.created_at DESC, po.id DESC, pol.id'
        );
        $stmt->execute([$workspaceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createDraft(int $workspaceId, int $userId, string $supplier, ?string $expectedDate, ?string $notes, array $lines): int
    {
        if (!$lines) throw new \InvalidArgumentException('برای سفارش دست‌کم یک قلم با مقدار مثبت وارد کنید.');
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO inv_purchase_orders (workspace_id, supplier_name, status, expected_date, notes, created_by)
                 VALUES (?, ?, 'draft', ?, ?, ?)"
            );
            $stmt->execute([$workspaceId, $supplier, $expectedDate, $notes, $userId]);
            $orderId = (int) $this->db->lastInsertId();
            $orderNo = 'PO-' . date('ymd') . '-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
            $stmt = $this->db->prepare('UPDATE inv_purchase_orders SET order_no=? WHERE workspace_id=? AND id=?');
            $stmt->execute([$orderNo, $workspaceId, $orderId]);
            $insert = $this->db->prepare(
                'INSERT INTO inv_purchase_order_lines (workspace_id, purchase_order_id, item_id, ordered_quantity, unit_cost)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $itemQuery = $this->db->prepare('SELECT id FROM inv_items WHERE workspace_id=? AND id=? AND is_active=1 FOR UPDATE');
            foreach ($lines as $line) {
                $itemQuery->execute([$workspaceId, $line['item_id']]);
                if (!$itemQuery->fetchColumn()) throw new \InvalidArgumentException('یکی از اقلام سفارش پیدا نشد یا بایگانی شده است.');
                $insert->execute([$workspaceId, $orderId, $line['item_id'], $line['quantity'], $line['unit_cost']]);
            }
            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function submit(int $workspaceId, int $orderId): void
    {
        $this->db->beginTransaction();
        try {
            $order = $this->lockOrder($workspaceId, $orderId);
            if ($order['status'] !== 'draft') throw new \InvalidArgumentException('فقط پیش‌نویس سفارش قابل ارسال است.');
            $stmt = $this->db->prepare('SELECT item_id, ordered_quantity FROM inv_purchase_order_lines WHERE workspace_id=? AND purchase_order_id=? ORDER BY item_id FOR UPDATE');
            $stmt->execute([$workspaceId, $orderId]);
            $lines = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!$lines) throw new \InvalidArgumentException('سفارش قلمی برای ارسال ندارد.');
            $item = $this->db->prepare('UPDATE inv_items SET on_order=on_order+? WHERE workspace_id=? AND id=? AND is_active=1');
            foreach ($lines as $line) {
                $item->execute([$line['ordered_quantity'], $workspaceId, $line['item_id']]);
                if ($item->rowCount() !== 1) throw new \RuntimeException('به‌روزرسانی سفارش در راه قلم ناموفق بود.');
            }
            $this->db->prepare("UPDATE inv_purchase_orders SET status='ordered' WHERE workspace_id=? AND id=?")->execute([$workspaceId, $orderId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function receive(int $workspaceId, int $orderId, int $userId, array $receipts): void
    {
        $this->db->beginTransaction();
        try {
            $order = $this->lockOrder($workspaceId, $orderId);
            if (!in_array($order['status'], ['ordered', 'partial'], true)) throw new \InvalidArgumentException('این سفارش در وضعیت قابل دریافت نیست.');
            $lineStmt = $this->db->prepare(
                'SELECT pol.id AS line_id, pol.item_id, pol.ordered_quantity, pol.received_quantity,
                        i.on_hand, i.on_order, i.unit
                 FROM inv_purchase_order_lines pol JOIN inv_items i
                   ON i.id=pol.item_id AND i.workspace_id=pol.workspace_id
                 WHERE pol.workspace_id=? AND pol.purchase_order_id=? AND pol.id=? FOR UPDATE'
            );
            $updateLine = $this->db->prepare('UPDATE inv_purchase_order_lines SET received_quantity=received_quantity+? WHERE workspace_id=? AND purchase_order_id=? AND id=?');
            $updateItem = $this->db->prepare('UPDATE inv_items SET on_hand=on_hand+?, on_order=on_order-? WHERE workspace_id=? AND id=?');
            $insertMovement = $this->db->prepare(
                "INSERT INTO inv_movements (workspace_id,item_id,user_id,movement_type,quantity,on_order_received,balance_after,note)
                 VALUES (?,?,?,'receipt',?,?,?,?)"
            );
            $receivedAny = false;
            foreach ($receipts as $lineId => $quantity) {
                if ($quantity <= 0) continue;
                $lineStmt->execute([$workspaceId, $orderId, $lineId]);
                $line = $lineStmt->fetch(\PDO::FETCH_ASSOC);
                if (!$line) throw new \InvalidArgumentException('یکی از ردیف‌های سفارش معتبر نیست.');
                $remaining = (float) $line['ordered_quantity'] - (float) $line['received_quantity'];
                if ($quantity > $remaining + 0.000001) throw new \InvalidArgumentException('مقدار دریافت از ماندهٔ سفارش بیشتر است.');
                if ($quantity > (float) $line['on_order'] + 0.000001) throw new \InvalidArgumentException('ماندهٔ سفارش در راه قلم با این دریافت سازگار نیست؛ گردش‌های دستی را بررسی کنید.');
                $balance = (float) $line['on_hand'] + $quantity;
                $updateItem->execute([$quantity, $quantity, $workspaceId, $line['item_id']]);
                $updateLine->execute([$quantity, $workspaceId, $orderId, $lineId]);
                $note = 'دریافت سفارش ' . $order['order_no'];
                $insertMovement->execute([$workspaceId, $line['item_id'], $userId, $quantity, $quantity, $balance, $note]);
                $receivedAny = true;
            }
            if (!$receivedAny) throw new \InvalidArgumentException('مقدار دریافت را برای دست‌کم یک ردیف وارد کنید.');
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM inv_purchase_order_lines WHERE workspace_id=? AND purchase_order_id=? AND received_quantity < ordered_quantity');
            $stmt->execute([$workspaceId, $orderId]);
            $status = (int) $stmt->fetchColumn() === 0 ? 'received' : 'partial';
            $this->db->prepare('UPDATE inv_purchase_orders SET status=? WHERE workspace_id=? AND id=?')->execute([$status, $workspaceId, $orderId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function cancel(int $workspaceId, int $orderId): void
    {
        $this->db->beginTransaction();
        try {
            $order = $this->lockOrder($workspaceId, $orderId);
            if (!in_array($order['status'], ['draft', 'ordered', 'partial'], true)) throw new \InvalidArgumentException('این سفارش قابل لغو نیست.');
            if ($order['status'] !== 'draft') {
                $stmt = $this->db->prepare('SELECT item_id, ordered_quantity, received_quantity FROM inv_purchase_order_lines WHERE workspace_id=? AND purchase_order_id=? ORDER BY item_id FOR UPDATE');
                $stmt->execute([$workspaceId, $orderId]);
                $decrease = $this->db->prepare('UPDATE inv_items SET on_order=on_order-? WHERE workspace_id=? AND id=? AND on_order>=?');
                foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $line) {
                    $remaining = (float) $line['ordered_quantity'] - (float) $line['received_quantity'];
                    if ($remaining <= 0) continue;
                    $decrease->execute([$remaining, $workspaceId, $line['item_id'], $remaining]);
                    if ($decrease->rowCount() !== 1) throw new \RuntimeException('لغو ماندهٔ سفارش در راه قلم ممکن نشد.');
                }
            }
            $this->db->prepare("UPDATE inv_purchase_orders SET status='cancelled' WHERE workspace_id=? AND id=?")->execute([$workspaceId, $orderId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function lockOrder(int $workspaceId, int $orderId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM inv_purchase_orders WHERE workspace_id=? AND id=? FOR UPDATE');
        $stmt->execute([$workspaceId, $orderId]);
        $order = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$order) throw new \InvalidArgumentException('سفارش خرید پیدا نشد.');
        return $order;
    }
}
