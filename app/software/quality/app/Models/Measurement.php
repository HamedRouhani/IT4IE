<?php
/**
 * ============================================================
 * Quality Analyzer — Measurement Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/Measurement.php
 * جدول: qc_measurements
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class Measurement extends Model
{
    protected $table      = 'qc_measurements';
    protected $primaryKey = 'id';

    /**
     * دریافت همه اندازه‌گیری‌های یک دیتاست
     */
    public function findByDataset(int $datasetId): array
    {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE dataset_id = ?
             ORDER BY subgroup_no ASC, sample_no ASC",
            [$datasetId]
        );
    }

    /**
     * دریافت اندازه‌گیری‌های یک زیرگروه خاص
     */
    public function findBySubgroup(int $datasetId, int $subgroupNo): array
    {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE dataset_id = ? AND subgroup_no = ?
             ORDER BY sample_no ASC",
            [$datasetId, $subgroupNo]
        );
    }

    /**
     * شمارش کل اندازه‌گیری‌ها
     */
    public function countByDataset(int $datasetId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table} WHERE dataset_id = ?",
            [$datasetId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * شمارش زیرگروه‌ها
     */
    public function countSubgroups(int $datasetId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(DISTINCT subgroup_no) AS c
             FROM {$this->table}
             WHERE dataset_id = ?",
            [$datasetId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * حذف همه اندازه‌گیری‌های یک دیتاست
     */
    public function deleteByDataset(int $datasetId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE dataset_id = ?"
        );
        return $stmt->execute([$datasetId]);
    }

    /**
     * حذف یک زیرگروه
     */
    public function deleteSubgroup(int $datasetId, int $subgroupNo): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table}
             WHERE dataset_id = ? AND subgroup_no = ?"
        );
        return $stmt->execute([$datasetId, $subgroupNo]);
    }

    /**
     * دریافت آمار سریع
     */
    public function getQuickStats(int $datasetId): array
    {
        $row = $this->queryOne(
            "SELECT
                COUNT(*)             AS n,
                MIN(value)           AS min_val,
                MAX(value)           AS max_val,
                AVG(value)           AS mean_val,
                SUM(is_defective)    AS total_defectives,
                SUM(defect_count)    AS total_defects
             FROM {$this->table}
             WHERE dataset_id = ?",
            [$datasetId]
        );

        if (!$row) {
            return [
                'n'                => 0,
                'min_val'          => null,
                'max_val'          => null,
                'mean_val'         => null,
                'total_defectives' => 0,
                'total_defects'    => 0,
            ];
        }

        return [
            'n'                => (int)   $row['n'],
            'min_val'          => $row['min_val']  !== null ? (float) $row['min_val']  : null,
            'max_val'          => $row['max_val']  !== null ? (float) $row['max_val']  : null,
            'mean_val'         => $row['mean_val'] !== null ? (float) $row['mean_val'] : null,
            'total_defectives' => (int)   $row['total_defectives'],
            'total_defects'    => (int)   $row['total_defects'],
        ];
    }

    /**
     * افزودن یک اندازه‌گیری
     */
    public function addOne(int $systemId, int $datasetId, array $data): int
    {
        return (int) $this->create([
            'system_id'    => $systemId,
            'dataset_id'   => $datasetId,
            'subgroup_no'  => (int)   ($data['subgroup_no']  ?? 1),
            'sample_no'    => (int)   ($data['sample_no']    ?? 1),
            'value'        => (float) ($data['value']        ?? 0),
            'is_defective' => (int)   ($data['is_defective'] ?? 0),
            'defect_count' => (int)   ($data['defect_count'] ?? 0),
            'measured_at'  => $data['measured_at'] ?? null,
        ]);
    }

    /**
     * به‌روزرسانی یک اندازه‌گیری
     */
    public function updateOne(int $measurementId, array $data): bool
    {
        $payload = [];
        if (isset($data['value']))        $payload['value']        = (float) $data['value'];
        if (isset($data['is_defective'])) $payload['is_defective'] = (int)   $data['is_defective'];
        if (isset($data['defect_count'])) $payload['defect_count'] = (int)   $data['defect_count'];
        if (isset($data['measured_at']))  $payload['measured_at']  = $data['measured_at'];

        if (empty($payload)) return false;
        return $this->update($measurementId, $payload);
    }
}