<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Models;

use App\Core\Database;
use PDO;

/** All project reads and writes are scoped to the authenticated owner. */
final class ProjectRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return list<array<string,mixed>> */
    public function listForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, project_code, title, facility_name, industry, planning_period,
                    shifts_per_day, hours_per_shift, working_days_per_week, status, created_at, updated_at
             FROM SLP_projects WHERE user_id = ? ORDER BY updated_at DESC, id DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array{total:int,draft:int} */
    public function stats(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total, COALESCE(SUM(status = 'draft'), 0) AS drafts
             FROM SLP_projects WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return ['total' => (int) ($row['total'] ?? 0), 'draft' => (int) ($row['drafts'] ?? 0)];
    }

    /** @return array<string,mixed>|null */
    public function findForUser(int $userId, int $projectId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM SLP_projects WHERE user_id = ? AND id = ? LIMIT 1');
        $stmt->execute([$userId, $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($project) ? $project : null;
    }

    /** @param array<string,mixed> $data */
    public function create(int $userId, array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO SLP_projects
                    (user_id, title, facility_name, industry, site_location, scope_description,
                     planning_period, shifts_per_day, hours_per_shift, working_days_per_week, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'draft\')'
            );
            $stmt->execute([
                $userId, $data['title'], $data['facility_name'], $data['industry'],
                $data['site_location'], $data['scope_description'], $data['planning_period'],
                $data['shifts_per_day'], $data['hours_per_shift'], $data['working_days_per_week'],
            ]);

            $id = (int) $this->db->lastInsertId();
            $code = 'FL-' . date('ymd') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
            $update = $this->db->prepare('UPDATE SLP_projects SET project_code = ? WHERE user_id = ? AND id = ?');
            $update->execute([$code, $userId, $id]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /** @param array<string,mixed> $data */
    public function update(int $userId, int $projectId, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE SLP_projects
             SET title = ?, facility_name = ?, industry = ?, site_location = ?, scope_description = ?,
                 planning_period = ?, shifts_per_day = ?, hours_per_shift = ?, working_days_per_week = ?
             WHERE user_id = ? AND id = ?'
        );
        $stmt->execute([
            $data['title'], $data['facility_name'], $data['industry'], $data['site_location'],
            $data['scope_description'], $data['planning_period'], $data['shifts_per_day'],
            $data['hours_per_shift'], $data['working_days_per_week'], $userId, $projectId,
        ]);
        if ($stmt->rowCount() < 1 && !$this->findForUser($userId, $projectId)) {
            throw new \InvalidArgumentException('پروژه پیدا نشد یا دسترسی به آن ندارید.');
        }
    }

    public function delete(int $userId, int $projectId): void
    {
        $stmt = $this->db->prepare('DELETE FROM SLP_projects WHERE user_id = ? AND id = ?');
        $stmt->execute([$userId, $projectId]);
        if ($stmt->rowCount() < 1) throw new \InvalidArgumentException('پروژه پیدا نشد یا قبلاً حذف شده است.');
    }
}
