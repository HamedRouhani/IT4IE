<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Project-owned qualitative closeness ratings between currently defined activity areas. */
final class ActivityRelationshipRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return list<array{area_a:string,area_b:string,closeness_code:string,reason:string,id:int}> */
    public function listForProject(int $userId, int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ar.id, ar.area_a, ar.area_b, ar.closeness_code, ar.reason
             FROM SLP_activity_relationships ar
             INNER JOIN SLP_projects pj ON pj.id = ar.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY FIELD(ar.closeness_code, \'A\', \'E\', \'I\', \'O\', \'U\', \'X\'), ar.area_a, ar.area_b'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param array{area_a:string,area_b:string,closeness_code:string,reason:string} $data */
    public function create(int $userId, int $projectId, array $data): void
    {
        $areas = $this->activityAreas($userId, $projectId);
        if (!in_array($data['area_a'], $areas, true) || !in_array($data['area_b'], $areas, true)) {
            throw new \InvalidArgumentException('هر دو فعالیت باید از فهرست بخش‌های همین پروژه انتخاب شوند.');
        }
        if ($data['area_a'] === $data['area_b']) {
            throw new \InvalidArgumentException('رابطهٔ هر بخش با خودش قابل ثبت نیست.');
        }

        $areas = [$data['area_a'], $data['area_b']];
        sort($areas, SORT_STRING);
        foreach ($this->listForProject($userId, $projectId) as $existing) {
            if ($existing['area_a'] === $areas[0] && $existing['area_b'] === $areas[1]) {
                throw new \InvalidArgumentException('رابطهٔ این دو بخش قبلاً ثبت شده است.');
            }
        }
        $stmt = $this->db->prepare(
            'INSERT INTO SLP_activity_relationships (project_id, area_a, area_b, closeness_code, reason)
             SELECT pj.id, ?, ?, ?, ? FROM SLP_projects pj WHERE pj.user_id = ? AND pj.id = ?'
        );
        $stmt->execute([$areas[0], $areas[1], $data['closeness_code'], $data['reason'], $userId, $projectId]);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('پروژه پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $relationshipId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ar.id, ar.project_id, ar.area_a, ar.area_b, ar.closeness_code, ar.reason
             FROM SLP_activity_relationships ar
             INNER JOIN SLP_projects pj ON pj.id = ar.project_id
             WHERE pj.user_id = ? AND ar.id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $relationshipId]);
        $relationship = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($relationship) ? $relationship : null;
    }

    /** @param array{closeness_code:string,reason:string} $data */
    public function update(int $userId, int $relationshipId, array $data): int
    {
        $relationship = $this->findForUser($userId, $relationshipId);
        if (!$relationship) {
            throw new \InvalidArgumentException('رابطه پیدا نشد یا دسترسی به آن ندارید.');
        }
        $stmt = $this->db->prepare(
            'UPDATE SLP_activity_relationships SET closeness_code = ?, reason = ? WHERE id = ? AND project_id = ?'
        );
        $stmt->execute([$data['closeness_code'], $data['reason'], $relationshipId, (int) $relationship['project_id']]);
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $relationshipId)) {
            throw new \InvalidArgumentException('رابطه پیدا نشد یا دسترسی به آن ندارید.');
        }
        return (int) $relationship['project_id'];
    }

    public function delete(int $userId, int $relationshipId): int
    {
        $lookup = $this->db->prepare(
            'SELECT ar.project_id FROM SLP_activity_relationships ar
             INNER JOIN SLP_projects pj ON pj.id = ar.project_id
             WHERE pj.user_id = ? AND ar.id = ? LIMIT 1'
        );
        $lookup->execute([$userId, $relationshipId]);
        $projectId = $lookup->fetchColumn();
        if ($projectId === false) {
            throw new \InvalidArgumentException('رابطه پیدا نشد یا قبلاً حذف شده است.');
        }
        $stmt = $this->db->prepare(
            'DELETE ar FROM SLP_activity_relationships ar
             INNER JOIN SLP_projects pj ON pj.id = ar.project_id
             WHERE pj.user_id = ? AND ar.id = ?'
        );
        $stmt->execute([$userId, $relationshipId]);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('رابطه پیدا نشد یا قبلاً حذف شده است.');
        }
        return (int) $projectId;
    }

    /** @return list<string> */
    public function activityAreas(int $userId, int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT area_name AS activity_area FROM SLP_route_steps rs
             INNER JOIN SLP_products pr ON pr.id = rs.product_id
             INNER JOIN SLP_projects pj ON pj.id = pr.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             UNION
             SELECT DISTINCT ss.name AS activity_area FROM SLP_support_services ss
             INNER JOIN SLP_projects pj ON pj.id = ss.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY activity_area'
        );
        $stmt->execute([$userId, $projectId, $userId, $projectId]);
        $areas = array_map(static fn(mixed $area): string => (string) $area, $stmt->fetchAll(PDO::FETCH_COLUMN));
        return array_values(array_filter($areas, static fn(string $area): bool => trim($area) !== ''));
    }
}
