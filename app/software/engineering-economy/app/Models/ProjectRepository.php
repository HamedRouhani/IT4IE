<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Models;

use App\Core\Database;

/**
 * دسترسی دادهٔ پروژه‌های اقتصاد مهندسی.
 * همهٔ کوئری‌ها با user_id محدود می‌شوند تا دادهٔ کاربران جدا بماند.
 */
final class ProjectRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * شمارش پروژه‌ها برای کارت‌های داشبورد.
     *
     * @return array{total:int,draft:int,active:int,completed:int,archived:int}
     */
    public function stats(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(status='draft') AS drafts,
                    SUM(status='active') AS active,
                    SUM(status='completed') AS completed,
                    SUM(status='archived') AS archived
             FROM ee_projects WHERE user_id=?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'draft' => (int) ($row['drafts'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'archived' => (int) ($row['archived'] ?? 0),
        ];
    }

    /**
     * فهرست پروژه‌ها با تعداد گزینه و آخرین به‌روزرسانی.
     *
     * @return array<int,array<string,mixed>>
     */
    public function listForUser(int $userId, bool $includeArchived = false, int $limit = 60): array
    {
        $limit = max(1, min(200, $limit));
        $where = $includeArchived ? '' : " AND p.status <> 'archived'";

        $stmt = $this->db->prepare(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM ee_alternatives a WHERE a.project_id = p.id) AS alternative_count
             FROM ee_projects p
             WHERE p.user_id = ?' . $where .
            " ORDER BY FIELD(p.status, 'active', 'draft', 'completed', 'archived'), p.updated_at DESC, p.id DESC
              LIMIT " . $limit
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** یک پروژه با گزینه‌ها و جریان‌های نقدی */
    public function find(int $userId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_projects WHERE user_id=? AND id=? LIMIT 1');
        $stmt->execute([$userId, $id]);
        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$project) return null;

        $project['alternatives'] = $this->alternatives((int) $project['id']);
        $project['cashflows'] = $this->cashflows((int) $project['id']);
        $project['assumptions'] = $this->assumptions((int) $project['id']);

        return $project;
    }

    /** @return array<int,array<string,mixed>> */
    public function alternatives(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_alternatives WHERE project_id=? ORDER BY sequence_no, id');
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array<int,array<string,mixed>> */
    public function cashflows(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_cashflows WHERE project_id=? ORDER BY alternative_id, year_no');
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array<int,array<string,mixed>> */
    public function assumptions(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_assumptions WHERE project_id=? ORDER BY sequence_no, id');
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ثبت فرض مستند برای پروژه.
     *
     * @param array<string,mixed> $data
     */
    public function addAssumption(int $projectId, array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ee_assumptions (project_id, title, value, unit, source, note, sequence_no)
             VALUES (?,?,?,?,?,?,
                (SELECT COALESCE(MAX(a.sequence_no), 0) + 1 FROM ee_assumptions a WHERE a.project_id = ?))'
        );
        $stmt->execute([
            $projectId,
            $data['title'],
            $data['value'],
            $data['unit'],
            $data['source'],
            $data['note'],
            $projectId,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteAssumption(int $projectId, int $assumptionId): void
    {
        $stmt = $this->db->prepare('DELETE FROM ee_assumptions WHERE id=? AND project_id=?');
        $stmt->execute([$assumptionId, $projectId]);

        if ($stmt->rowCount() < 1) throw new \InvalidArgumentException('فرض موردنظر پیدا نشد.');
    }

    /**
     * ساخت پروژهٔ جدید.
     *
     * @param array<string,mixed> $data
     */
    public function create(int $userId, array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ee_projects
                    (user_id, title, description, study_kind, currency, base_year, study_period,
                     marr_pct, inflation_pct, tax_rate_pct, depreciation_method, status, notes)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $userId,
                $data['title'],
                $data['description'],
                $data['study_kind'],
                $data['currency'],
                $data['base_year'],
                $data['study_period'],
                $data['marr_pct'],
                $data['inflation_pct'],
                $data['tax_rate_pct'],
                $data['depreciation_method'],
                'draft',
                $data['notes'],
            ]);

            $id = (int) $this->db->lastInsertId();
            $code = 'EE-' . date('ymd') . '-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
            $this->db->prepare('UPDATE ee_projects SET project_no=? WHERE id=? AND user_id=?')->execute([$code, $id, $userId]);

            $this->db->commit();

            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ویرایش پروژهٔ پیش‌نویس.
     *
     * @param array<string,mixed> $data
     */
    public function updateDraft(int $userId, int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE ee_projects
                SET title=?, description=?, study_kind=?, currency=?, base_year=?, study_period=?,
                    marr_pct=?, inflation_pct=?, tax_rate_pct=?, depreciation_method=?, notes=?
              WHERE id=? AND user_id=? AND status='draft'"
        );
        $stmt->execute([
            $data['title'],
            $data['description'],
            $data['study_kind'],
            $data['currency'],
            $data['base_year'],
            $data['study_period'],
            $data['marr_pct'],
            $data['inflation_pct'],
            $data['tax_rate_pct'],
            $data['depreciation_method'],
            $data['notes'],
            $id,
            $userId,
        ]);

        if ($stmt->rowCount() < 1) {
            throw new \InvalidArgumentException('فقط پروژهٔ پیش‌نویس قابل ویرایش است.');
        }
    }

    /** تغییر وضعیت پروژه با قواعد گردش کار */
    public function setStatus(int $userId, int $id, string $status): void
    {
        $allowed = ['draft', 'active', 'completed', 'archived'];
        if (!in_array($status, $allowed, true)) {
            throw new \InvalidArgumentException('وضعیت درخواستی معتبر نیست.');
        }

        $stmt = $this->db->prepare('SELECT status FROM ee_projects WHERE user_id=? AND id=? LIMIT 1');
        $stmt->execute([$userId, $id]);
        $current = $stmt->fetchColumn();

        if ($current === false) throw new \InvalidArgumentException('پروژه پیدا نشد.');

        $transitions = [
            'draft' => ['active', 'archived'],
            'active' => ['completed', 'archived'],
            'completed' => ['archived'],
            'archived' => [],
        ];

        if (!in_array($status, $transitions[(string) $current] ?? [], true)) {
            throw new \InvalidArgumentException('این تغییر وضعیت مجاز نیست.');
        }

        $this->db->prepare('UPDATE ee_projects SET status=? WHERE id=? AND user_id=?')->execute([$status, $id, $userId]);
    }

    /** حذف کامل پروژه و داده‌های وابسته (فقط مالک) */
    public function delete(int $userId, int $id): void
    {
        $this->db->beginTransaction();
        try {
            $check = $this->db->prepare('SELECT id FROM ee_projects WHERE user_id=? AND id=? FOR UPDATE');
            $check->execute([$userId, $id]);
            if (!$check->fetchColumn()) throw new \InvalidArgumentException('پروژه پیدا نشد.');

            $this->db->prepare('DELETE FROM ee_analyses WHERE project_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ee_scenarios WHERE project_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ee_assumptions WHERE project_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ee_cashflows WHERE project_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ee_alternatives WHERE project_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ee_projects WHERE id=? AND user_id=?')->execute([$id, $userId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
