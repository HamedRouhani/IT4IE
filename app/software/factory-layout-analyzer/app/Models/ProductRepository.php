<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Product operations are always restricted to a project owned by the current user. */
final class ProductRepository
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
            'SELECT pr.id, pr.project_id, pr.product_code, pr.name, pr.production_quantity,
                    pr.production_unit, pr.notes, pr.created_at, pr.updated_at
             FROM SLP_products pr
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY pr.id ASC'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $productId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT pr.* FROM SLP_products pr
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pr.id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($product) ? $product : null;
    }

    /** @param array<string,mixed> $data */
    public function create(int $userId, int $projectId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO SLP_products (project_id, product_code, name, production_quantity, production_unit, notes)
             SELECT pj.id, NULLIF(?, \'\'), ?, ?, ?, NULLIF(?, \'\')
             FROM SLP_projects pj WHERE pj.user_id = ? AND pj.id = ?'
        );
        try {
            $stmt->execute([
                $data['product_code'], $data['name'], $data['production_quantity'],
                $data['production_unit'], $data['notes'], $userId, $projectId,
            ]);
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new \InvalidArgumentException('این کد محصول قبلاً در همین پروژه ثبت شده است.');
            }
            throw $e;
        }
        if ($stmt->rowCount() !== 1) throw new \InvalidArgumentException('پروژه پیدا نشد یا دسترسی به آن ندارید.');
        return (int) $this->db->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $userId, int $productId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE SLP_products pr
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             SET pr.product_code = NULLIF(?, \'\'), pr.name = ?, pr.production_quantity = ?,
                 pr.production_unit = ?, pr.notes = NULLIF(?, \'\')
             WHERE pj.user_id = ? AND pr.id = ?'
        );
        try {
            $stmt->execute([
                $data['product_code'], $data['name'], $data['production_quantity'],
                $data['production_unit'], $data['notes'], $userId, $productId,
            ]);
        } catch (\PDOException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                throw new \InvalidArgumentException('این کد محصول قبلاً در همین پروژه ثبت شده است.');
            }
            throw $e;
        }
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $productId)) {
            throw new \InvalidArgumentException('محصول پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    public function delete(int $userId, int $productId): void
    {
        $stmt = $this->db->prepare(
            'DELETE pr FROM SLP_products pr
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pr.id = ?'
        );
        $stmt->execute([$userId, $productId]);
        if ($stmt->rowCount() < 1) throw new \InvalidArgumentException('محصول پیدا نشد یا قبلاً حذف شده است.');
    }
}
