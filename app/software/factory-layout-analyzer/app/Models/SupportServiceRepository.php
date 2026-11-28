<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Project-owned support activities and their preliminary area requirements. */
final class SupportServiceRepository
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
            'SELECT ss.* FROM SLP_support_services ss
             INNER JOIN SLP_projects pj ON pj.id = ss.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY FIELD(ss.requirement_level, \'required\', \'preferred\', \'optional\'), ss.id'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $serviceId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ss.* FROM SLP_support_services ss
             INNER JOIN SLP_projects pj ON pj.id = ss.project_id
             WHERE pj.user_id = ? AND ss.id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $serviceId]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($service) ? $service : null;
    }

    /** @param array{name:string,category:string,requirement_level:string,area_m2:?string,area_basis:string,notes:string} $data */
    public function create(int $userId, int $projectId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO SLP_support_services
                (project_id, name, category, requirement_level, area_m2, area_basis, notes)
             SELECT pj.id, ?, ?, ?, ?, NULLIF(?, \'\'), NULLIF(?, \'\')
             FROM SLP_projects pj WHERE pj.user_id = ? AND pj.id = ?'
        );
        $stmt->execute([
            $data['name'], $data['category'], $data['requirement_level'], $data['area_m2'],
            $data['area_basis'], $data['notes'], $userId, $projectId,
        ]);
        if ($stmt->rowCount() !== 1) throw new \InvalidArgumentException('پروژه پیدا نشد یا دسترسی به آن ندارید.');
        return (int) $this->db->lastInsertId();
    }

    /** @param array{name:string,category:string,requirement_level:string,area_m2:?string,area_basis:string,notes:string} $data */
    public function update(int $userId, int $serviceId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE SLP_support_services ss
             INNER JOIN SLP_projects pj ON pj.id = ss.project_id
             SET ss.name = ?, ss.category = ?, ss.requirement_level = ?, ss.area_m2 = ?,
                 ss.area_basis = NULLIF(?, \'\'), ss.notes = NULLIF(?, \'\')
             WHERE pj.user_id = ? AND ss.id = ?'
        );
        $stmt->execute([
            $data['name'], $data['category'], $data['requirement_level'], $data['area_m2'],
            $data['area_basis'], $data['notes'], $userId, $serviceId,
        ]);
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $serviceId)) {
            throw new \InvalidArgumentException('خدمت پشتیبان پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    public function delete(int $userId, int $serviceId): int
    {
        $service = $this->findForUser($userId, $serviceId);
        if (!$service) throw new \InvalidArgumentException('خدمت پشتیبان پیدا نشد یا دسترسی به آن ندارید.');
        $stmt = $this->db->prepare(
            'DELETE ss FROM SLP_support_services ss
             INNER JOIN SLP_projects pj ON pj.id = ss.project_id
             WHERE pj.user_id = ? AND ss.id = ?'
        );
        $stmt->execute([$userId, $serviceId]);
        if ($stmt->rowCount() !== 1) throw new \InvalidArgumentException('خدمت پشتیبان قبلاً حذف شده است.');
        return (int) $service['project_id'];
    }
}
