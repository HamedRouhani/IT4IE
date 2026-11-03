<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Models;

use App\Core\Database;

/**
 * گزینه‌ها و جریان‌های نقدی.
 * هر متد مالکیت را از طریق user_id پروژه بررسی می‌کند.
 */
final class AlternativeRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * گزینه با مالکیت پروژه.
     *
     * @return array<string,mixed>|null
     */
    public function find(int $userId, int $alternativeId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, p.user_id, p.title AS project_title, p.study_period, p.currency, p.marr_pct,
                    p.inflation_pct, p.tax_rate_pct, p.depreciation_method, p.status AS project_status
             FROM ee_alternatives a
             JOIN ee_projects p ON p.id = a.project_id
             WHERE a.id = ? AND p.user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$alternativeId, $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /** @return array<int,array<string,mixed>> */
    public function listForProject(int $projectId): array
    {
        $stmt = $this->db->prepare(
            'SELECT a.*, (SELECT COUNT(*) FROM ee_cashflows c WHERE c.alternative_id = a.id) AS flow_count
             FROM ee_alternatives a WHERE a.project_id = ? ORDER BY a.sequence_no, a.id'
        );
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ساخت گزینه و (به‌صورت خودکار) سطرهای صفر برای همهٔ سال‌های مطالعه.
     *
     * @param array<string,mixed> $data
     */
    public function create(int $projectId, int $studyPeriod, array $data): int
    {
        $this->db->beginTransaction();
        try {
            $sequence = (int) $this->scalar('SELECT COALESCE(MAX(sequence_no), 0) + 1 FROM ee_alternatives WHERE project_id = ?', [$projectId]);

            $stmt = $this->db->prepare(
                'INSERT INTO ee_alternatives (project_id, name, description, life_years, is_baseline, sequence_no)
                 VALUES (?,?,?,?,?,?)'
            );
            $stmt->execute([
                $projectId,
                $data['name'],
                $data['description'],
                $data['life_years'],
                $data['is_baseline'],
                $sequence,
            ]);

            $alternativeId = (int) $this->db->lastInsertId();

            $insert = $this->db->prepare(
                'INSERT INTO ee_cashflows (project_id, alternative_id, year_no, inflow, outflow)
                 VALUES (?,?,?,0,0)'
            );
            for ($year = 0; $year <= $studyPeriod; $year++) {
                $insert->execute([$projectId, $alternativeId, $year]);
            }

            $this->db->commit();

            return $alternativeId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $alternativeId, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE ee_alternatives SET name=?, description=?, life_years=? WHERE id=?');
        $stmt->execute([$data['name'], $data['description'], $data['life_years'], $alternativeId]);
    }

    public function delete(int $alternativeId): void
    {
        $this->db->prepare('DELETE FROM ee_alternatives WHERE id=?')->execute([$alternativeId]);
    }

    /** @return array<int,array<string,mixed>> سطرهای جریان نقدی به‌ترتیب سال */
    public function flowsFor(int $alternativeId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_cashflows WHERE alternative_id=? ORDER BY year_no');
        $stmt->execute([$alternativeId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * جریان‌های همهٔ گزینه‌های یک پروژه (برای تحلیل).
     *
     * @return array<int,array<int,array<string,mixed>>> کلید = شناسهٔ گزینه
     */
    public function flowsByProject(int $projectId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ee_cashflows WHERE project_id=? ORDER BY alternative_id, year_no');
        $stmt->execute([$projectId]);

        $grouped = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $grouped[(int) $row['alternative_id']][] = $row;
        }

        return $grouped;
    }

    /**
     * ذخیرهٔ جریان نقدی یک گزینه.
     *
     * @param array<int,array{year:int,inflow:float,outflow:float,label:?string}> $rows
     */
    public function saveFlows(int $projectId, int $alternativeId, int $studyPeriod, array $rows): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ee_cashflows (project_id, alternative_id, year_no, inflow, outflow, label)
                 VALUES (?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE inflow = VALUES(inflow), outflow = VALUES(outflow), label = VALUES(label)'
            );

            foreach ($rows as $row) {
                if ($row['year'] < 0 || $row['year'] > $studyPeriod) continue;
                $stmt->execute([$projectId, $alternativeId, $row['year'], $row['inflow'], $row['outflow'], $row['label']]);
            }

            $this->db->prepare('DELETE FROM ee_cashflows WHERE alternative_id=? AND year_no > ?')
                ->execute([$alternativeId, $studyPeriod]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function scalar(string $sql, array $params)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }
}
