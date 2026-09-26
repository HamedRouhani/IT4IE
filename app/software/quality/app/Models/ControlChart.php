<?php
/**
 * ============================================================
 * Quality Analyzer — ControlChart Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/ControlChart.php
 * جدول: qc_control_charts
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class ControlChart extends Model
{
    protected $table      = 'qc_control_charts';
    protected $primaryKey = 'id';

    // ═════════════════════════════════════════════
    // دریافت‌ها
    // ═════════════════════════════════════════════

    /**
     * دریافت آخرین نمودار یک دیتاست
     */
    public function findByDataset(int $datasetId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table}
             WHERE dataset_id = ?
             ORDER BY computed_at DESC
             LIMIT 1",
            [$datasetId]
        );
    }

    /**
     * دریافت همه نمودارهای یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->query(
            "SELECT cc.*,
                    d.name        AS dataset_name,
                    d.chart_type  AS dataset_chart_type,
                    p.name        AS project_name
             FROM {$this->table} cc
             JOIN qc_datasets  d ON d.id = cc.dataset_id
             JOIN qc_projects  p ON p.id = d.project_id
             WHERE cc.system_id = ?
             ORDER BY cc.computed_at DESC",
            [$systemId]
        );
    }

    /**
     * دریافت نمودارها با آمار کامل
     */
    public function findBySystemWithStats(int $systemId): array
    {
        return $this->query(
            "SELECT cc.*,
                    d.name        AS dataset_name,
                    p.name        AS project_name,
                    CASE WHEN cc.in_control = 1 THEN 'in' ELSE 'out' END AS control_status
             FROM {$this->table} cc
             JOIN qc_datasets d ON d.id = cc.dataset_id
             JOIN qc_projects p ON p.id = d.project_id
             WHERE cc.system_id = ?
             ORDER BY cc.computed_at DESC",
            [$systemId]
        );
    }

    // ═════════════════════════════════════════════
    // ذخیره
    // ═════════════════════════════════════════════

    /**
     * ذخیره نمودار برای دیتاست (حذف قبلی + درج جدید)
     */
    public function saveForDataset(int $systemId, int $datasetId, array $data): int
    {
        // حذف نمودار قبلی همین دیتاست
        $this->db->prepare(
            "DELETE FROM {$this->table} WHERE dataset_id = ?"
        )->execute([$datasetId]);

        // آماده‌سازی payload
        $payload = [
            'system_id'         => $systemId,
            'dataset_id'        => $datasetId,
            'chart_type'        => $data['chart_type']        ?? 'xbar_r',
            'center_line'       => $data['center_line']       ?? null,
            'ucl'               => $data['ucl']               ?? null,
            'lcl'               => $data['lcl']               ?? null,
            'r_bar'             => $data['r_bar']             ?? null,
            's_bar'             => $data['s_bar']             ?? null,
            'mr_bar'            => $data['mr_bar']            ?? null,
            'sigma_hat'         => $data['sigma_hat']         ?? null,
            'nelson_violations' => isset($data['nelson_violations'])
                                    ? json_encode($data['nelson_violations'], JSON_UNESCAPED_UNICODE)
                                    : null,
            'we_violations'     => isset($data['we_violations'])
                                    ? json_encode($data['we_violations'], JSON_UNESCAPED_UNICODE)
                                    : null,
            'in_control'        => $data['in_control']        ?? 1,
            'computed_at'       => date('Y-m-d H:i:s'),
        ];

        return (int) $this->create($payload);
    }

    // ═════════════════════════════════════════════
    // Decode / Encode
    // ═════════════════════════════════════════════

    /**
     * دیکد کردن قوانین نقض‌شده
     * خروجی: ['nelson' => [...], 'we' => [...]]
     */
    public function decodeViolations(array $chart): array
    {
        return [
            'nelson' => json_decode($chart['nelson_violations'] ?? '[]', true) ?: [],
            'we'     => json_decode($chart['we_violations']     ?? '[]', true) ?: [],
        ];
    }

    /**
     * آیا نمودار در کنترل است؟
     */
    public function isInControl(array $chart): bool
    {
        return (int) ($chart['in_control'] ?? 1) === 1;
    }

    // ═════════════════════════════════════════════
    // آمار
    // ═════════════════════════════════════════════

    /**
     * شمارش کل نمودارها
     */
    public function countBySystem(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table} WHERE system_id = ?",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * شمارش نمودارهای خارج از کنترل
     */
    public function countOutOfControl(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE system_id = ? AND in_control = 0",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * حذف نمودار یک دیتاست
     */
    public function deleteByDataset(int $datasetId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE dataset_id = ?"
        );
        return $stmt->execute([$datasetId]);
    }

    /**
     * دریافت آخرین N نمودار سیستم
     */
    public function getLatestBySystem(int $systemId, int $limit = 5): array
    {
        return $this->query(
            "SELECT cc.*, d.name AS dataset_name, p.name AS project_name
             FROM {$this->table} cc
             JOIN qc_datasets d ON d.id = cc.dataset_id
             JOIN qc_projects p ON p.id = d.project_id
             WHERE cc.system_id = ?
             ORDER BY cc.computed_at DESC
             LIMIT " . (int) $limit,
            [$systemId]
        );
    }
}