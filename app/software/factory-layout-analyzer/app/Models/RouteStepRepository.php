<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Ordered product routes; every operation is checked through project ownership. */
final class RouteStepRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return list<array<string,mixed>> */
    public function listForProject(int $userId, int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT rs.id, rs.product_id, rs.step_no, rs.area_name, rs.activity_type,
                    rs.description, rs.notes, pr.name AS product_name
             FROM SLP_route_steps rs
             INNER JOIN SLP_products pr ON pr.id = rs.product_id
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY pr.id, rs.step_no, rs.id'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $stepId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT rs.*, pr.project_id, pr.name AS product_name
             FROM SLP_route_steps rs
             INNER JOIN SLP_products pr ON pr.id = rs.product_id
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND rs.id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $stepId]);
        $step = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($step) ? $step : null;
    }

    /** @param array<string,string> $data */
    public function create(int $userId, int $productId, array $data): int
    {
        $this->db->beginTransaction();
        try {
            $this->lockOwnedProduct($userId, $productId);
            $maxStmt = $this->db->prepare('SELECT COALESCE(MAX(step_no), 0) FROM SLP_route_steps WHERE product_id = ?');
            $maxStmt->execute([$productId]);
            $next = (int) $maxStmt->fetchColumn() + 1;
            if ($next > 99) throw new \InvalidArgumentException('برای هر محصول حداکثر ۹۹ گام مسیر ثبت می‌شود.');

            $stmt = $this->db->prepare(
                'INSERT INTO SLP_route_steps (product_id, step_no, area_name, activity_type, description, notes)
                 VALUES (?, ?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\'))'
            );
            $stmt->execute([$productId, $next, $data['area_name'], $data['activity_type'], $data['description'], $data['notes']]);
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** @param array<string,string> $data */
    public function update(int $userId, int $stepId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE SLP_route_steps rs
             INNER JOIN SLP_products pr ON pr.id = rs.product_id
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             SET rs.area_name = ?, rs.activity_type = ?, rs.description = NULLIF(?, \'\'), rs.notes = NULLIF(?, \'\')
             WHERE pj.user_id = ? AND rs.id = ?'
        );
        $stmt->execute([$data['area_name'], $data['activity_type'], $data['description'], $data['notes'], $userId, $stepId]);
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $stepId)) {
            throw new \InvalidArgumentException('گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    public function delete(int $userId, int $stepId): int
    {
        $step = $this->findForUser($userId, $stepId);
        if (!$step) throw new \InvalidArgumentException('گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
        $productId = (int) $step['product_id'];

        $this->db->beginTransaction();
        try {
            $this->lockOwnedProduct($userId, $productId);
            $stmt = $this->db->prepare('DELETE FROM SLP_route_steps WHERE product_id = ? AND id = ?');
            $stmt->execute([$productId, $stepId]);
            if ($stmt->rowCount() !== 1) throw new \InvalidArgumentException('گام مسیر قبلاً حذف شده است.');
            $remaining = $this->db->prepare('SELECT id, step_no FROM SLP_route_steps WHERE product_id = ? ORDER BY step_no, id');
            $remaining->execute([$productId]);
            $rows = $remaining->fetchAll(PDO::FETCH_ASSOC);
            $renumber = $this->db->prepare('UPDATE SLP_route_steps SET step_no = ? WHERE product_id = ? AND id = ?');
            foreach ($rows as $index => $row) {
                $expected = $index + 1;
                if ((int) $row['step_no'] !== $expected) $renumber->execute([$expected, $productId, (int) $row['id']]);
            }
            $this->db->commit();
            return (int) $step['project_id'];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** Move a route step one place and keep order numbers unique. */
    public function move(int $userId, int $stepId, string $direction): int
    {
        if (!in_array($direction, ['up', 'down'], true)) throw new \InvalidArgumentException('جهت جابه‌جایی معتبر نیست.');
        $step = $this->findForUser($userId, $stepId);
        if (!$step) throw new \InvalidArgumentException('گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
        $productId = (int) $step['product_id'];

        $this->db->beginTransaction();
        try {
            $this->lockOwnedProduct($userId, $productId);
            $current = $this->db->prepare('SELECT step_no FROM SLP_route_steps WHERE product_id = ? AND id = ? FOR UPDATE');
            $current->execute([$productId, $stepId]);
            $currentOrder = $current->fetchColumn();
            if ($currentOrder === false) throw new \InvalidArgumentException('گام مسیر پیدا نشد.');
            $currentOrder = (int) $currentOrder;
            $operator = $direction === 'up' ? '<' : '>';
            $sort = $direction === 'up' ? 'DESC' : 'ASC';
            $neighborStmt = $this->db->prepare(
                "SELECT id, step_no FROM SLP_route_steps WHERE product_id = ? AND step_no {$operator} ? ORDER BY step_no {$sort}, id {$sort} LIMIT 1 FOR UPDATE"
            );
            $neighborStmt->execute([$productId, $currentOrder]);
            $neighbor = $neighborStmt->fetch(PDO::FETCH_ASSOC);
            if (is_array($neighbor)) {
                $maxStmt = $this->db->prepare('SELECT MAX(step_no) FROM SLP_route_steps WHERE product_id = ?');
                $maxStmt->execute([$productId]);
                $temporary = (int) $maxStmt->fetchColumn() + 1;
                if ($temporary > 100) throw new \RuntimeException('ترتیب گام‌های مسیر قابل به‌روزرسانی نیست.');
                $update = $this->db->prepare('UPDATE SLP_route_steps SET step_no = ? WHERE product_id = ? AND id = ?');
                $update->execute([$temporary, $productId, $stepId]);
                $update->execute([$currentOrder, $productId, (int) $neighbor['id']]);
                $update->execute([(int) $neighbor['step_no'], $productId, $stepId]);
            }
            $this->db->commit();
            return (int) $step['project_id'];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function lockOwnedProduct(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare(
            'SELECT pr.id FROM SLP_products pr
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pr.id = ? FOR UPDATE'
        );
        $stmt->execute([$userId, $productId]);
        if ($stmt->fetchColumn() === false) throw new \InvalidArgumentException('محصول پیدا نشد یا دسترسی به آن ندارید.');
    }
}
