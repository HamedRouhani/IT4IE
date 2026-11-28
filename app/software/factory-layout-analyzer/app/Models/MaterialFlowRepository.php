<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** Project-owned, directed observations of material handling between activities. */
final class MaterialFlowRepository
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
            'SELECT mf.id, mf.project_id, mf.from_area, mf.to_area, mf.load_moves,
                    mf.flow_period, mf.evidence, mf.created_at, mf.updated_at
             FROM SLP_material_flows mf
             INNER JOIN SLP_projects pj ON pj.id = mf.project_id
             WHERE pj.user_id = ? AND pj.id = ?
             ORDER BY mf.from_area, mf.to_area'
        );
        $stmt->execute([$userId, $projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param array{from_area:string,to_area:string,load_moves:string,flow_period:string,evidence:string} $data */
    public function create(int $userId, int $projectId, array $data): void
    {
        $areas = (new ActivityRelationshipRepository())->activityAreas($userId, $projectId);
        if (!in_array($data['from_area'], $areas, true) || !in_array($data['to_area'], $areas, true)) {
            throw new \InvalidArgumentException('مبدأ و مقصد باید از فعالیت‌های تعریف‌شدهٔ همین پروژه باشند.');
        }
        if ($data['from_area'] === $data['to_area']) {
            throw new \InvalidArgumentException('مبدأ و مقصد جریان نمی‌توانند یک فعالیت باشند.');
        }

        try {
            $stmt = $this->db->prepare(
                'INSERT INTO SLP_material_flows (project_id, from_area, to_area, load_moves, flow_period, evidence)
                 SELECT pj.id, ?, ?, ?, ?, ? FROM SLP_projects pj WHERE pj.user_id = ? AND pj.id = ?'
            );
            $stmt->execute([
                $data['from_area'], $data['to_area'], $data['load_moves'], $data['flow_period'],
                $data['evidence'], $userId, $projectId,
            ]);
        } catch (\PDOException $e) {
            if ((string) ($e->errorInfo[1] ?? '') === '1062') {
                throw new \InvalidArgumentException('برای این مسیر جهت‌دار قبلاً جریان ثبت شده است؛ همان ردیف را ویرایش کنید.');
            }
            throw $e;
        }
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('پروژه پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $flowId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT mf.* FROM SLP_material_flows mf
             INNER JOIN SLP_projects pj ON pj.id = mf.project_id
             WHERE pj.user_id = ? AND mf.id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $flowId]);
        $flow = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($flow) ? $flow : null;
    }

    /** @param array{load_moves:string,flow_period:string,evidence:string} $data */
    public function update(int $userId, int $flowId, array $data): int
    {
        $flow = $this->findForUser($userId, $flowId);
        if (!$flow) {
            throw new \InvalidArgumentException('جریان پیدا نشد یا دسترسی به آن ندارید.');
        }
        $stmt = $this->db->prepare(
            'UPDATE SLP_material_flows SET load_moves = ?, flow_period = ?, evidence = ? WHERE id = ? AND project_id = ?'
        );
        $stmt->execute([$data['load_moves'], $data['flow_period'], $data['evidence'], $flowId, (int) $flow['project_id']]);
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $flowId)) {
            throw new \InvalidArgumentException('جریان پیدا نشد یا دسترسی به آن ندارید.');
        }
        return (int) $flow['project_id'];
    }

    public function delete(int $userId, int $flowId): int
    {
        $flow = $this->findForUser($userId, $flowId);
        if (!$flow) {
            throw new \InvalidArgumentException('جریان پیدا نشد یا دسترسی به آن ندارید.');
        }
        $stmt = $this->db->prepare('DELETE FROM SLP_material_flows WHERE id = ? AND project_id = ?');
        $stmt->execute([$flowId, (int) $flow['project_id']]);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('جریان پیدا نشد یا قبلاً حذف شده است.');
        }
        return (int) $flow['project_id'];
    }
}
