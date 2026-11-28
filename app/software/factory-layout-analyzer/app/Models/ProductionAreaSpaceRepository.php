<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Preliminary space estimates for production areas named in project routes. */
final class ProductionAreaSpaceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return list<string> */
    public function productionAreasForProject(int $userId, int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT rs.area_name
             FROM SLP_route_steps rs
             INNER JOIN SLP_products pr ON pr.id = rs.product_id
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY rs.area_name'
        );
        $stmt->execute([$userId, $projectId]);
        return array_values(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)));
    }

    /** @return list<array<string,mixed>> */
    public function listForProject(int $userId, int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.id, s.project_id, s.area_name, s.area_m2, s.area_basis, s.notes
             FROM SLP_production_area_spaces s
             INNER JOIN SLP_projects pj ON pj.id = s.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY s.area_name'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param array{area_name:string,area_m2:string,area_basis:string,notes:string} $data */
    public function save(int $userId, int $projectId, array $data): void
    {
        $areaCheck = $this->db->prepare(
            'SELECT 1
             FROM SLP_projects pj
             WHERE pj.user_id = ? AND pj.id = ?
               AND EXISTS (
                   SELECT 1
                   FROM SLP_route_steps rs
                   INNER JOIN SLP_products pr ON pr.id = rs.product_id
                   WHERE pr.project_id = pj.id AND rs.area_name = ?
               )
             LIMIT 1'
        );
        $areaCheck->execute([$userId, $projectId, $data['area_name']]);
        if ($areaCheck->fetchColumn() === false) {
            throw new \InvalidArgumentException('بخش انتخاب‌شده در مسیرهای همین پروژه پیدا نشد.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO SLP_production_area_spaces
                (project_id, area_name, area_m2, area_basis, notes)
             SELECT pj.id, ?, ?, ?, NULLIF(?, \'\')
             FROM SLP_projects pj
             WHERE pj.user_id = ? AND pj.id = ?
               AND EXISTS (
                   SELECT 1
                   FROM SLP_route_steps rs
                   INNER JOIN SLP_products pr ON pr.id = rs.product_id
                   WHERE pr.project_id = pj.id AND rs.area_name = ?
               )
             ON DUPLICATE KEY UPDATE
                area_name = VALUES(area_name), area_m2 = VALUES(area_m2),
                area_basis = VALUES(area_basis), notes = VALUES(notes)'
        );
        $stmt->execute([
            $data['area_name'], $data['area_m2'], $data['area_basis'], $data['notes'],
            $userId, $projectId, $data['area_name'],
        ]);
        if ($stmt->rowCount() === 0 && !$this->existsForUserAndArea($userId, $projectId, $data['area_name'])) {
            throw new \InvalidArgumentException('بخش انتخاب‌شده در مسیرهای همین پروژه پیدا نشد.');
        }
    }

    public function delete(int $userId, int $spaceId): int
    {
        $lookup = $this->db->prepare(
            'SELECT s.project_id
             FROM SLP_production_area_spaces s
             INNER JOIN SLP_projects pj ON pj.id = s.project_id
             WHERE pj.user_id = ? AND s.id = ? LIMIT 1'
        );
        $lookup->execute([$userId, $spaceId]);
        $projectId = $lookup->fetchColumn();
        if ($projectId === false) {
            throw new \InvalidArgumentException('برآورد فضا پیدا نشد یا قبلاً حذف شده است.');
        }

        $stmt = $this->db->prepare(
            'DELETE s FROM SLP_production_area_spaces s
             INNER JOIN SLP_projects pj ON pj.id = s.project_id
             WHERE pj.user_id = ? AND s.id = ?'
        );
        $stmt->execute([$userId, $spaceId]);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('برآورد فضا پیدا نشد یا قبلاً حذف شده است.');
        }
        return (int) $projectId;
    }

    private function existsForUserAndArea(int $userId, int $projectId, string $areaName): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM SLP_production_area_spaces s
             INNER JOIN SLP_projects pj ON pj.id = s.project_id
             WHERE pj.user_id = ? AND pj.id = ? AND s.area_name = ?
             LIMIT 1'
        );
        $stmt->execute([$userId, $projectId, $areaName]);
        return $stmt->fetchColumn() !== false;
    }
}
