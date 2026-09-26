<?php
/**
 * ============================================================
 * Quality Analyzer — Dataset Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/Dataset.php
 * جدول: qc_datasets
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class Dataset extends Model
{
    protected $table      = 'qc_datasets';
    protected $primaryKey = 'id';

    /**
     * انواع نمودار پشتیبانی‌شده
     */
    public const CHART_TYPES = [
        'xbar_r' => 'X̄-R (میانگین-دامنه)',
        'xbar_s' => 'X̄-S (میانگین-انحراف)',
        'i_mr'   => 'I-MR (تک‌مقدار-دامنه متحرک)',
        'p'      => 'p (نسبت معیوب)',
        'np'     => 'np (تعداد معیوب)',
        'c'      => 'c (تعداد نقص)',
        'u'      => 'u (نقص در واحد)',
    ];

    /**
     * نمودارهای متغیر
     */
    public const VARIABLE_CHARTS = ['xbar_r', 'xbar_s', 'i_mr'];

    /**
     * نمودارهای صفتی
     */
    public const ATTRIBUTE_CHARTS = ['p', 'np', 'c', 'u'];

    // ═════════════════════════════════════════════
    // دریافت‌ها
    // ═════════════════════════════════════════════

    /**
     * دریافت دیتاست‌های یک پروژه
     */
    public function findByProject(int $projectId): array
    {
        return $this->findAll(
            ['project_id' => $projectId],
            'created_at DESC'
        );
    }

    /**
     * دریافت دیتاست‌های یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->findAll(
            ['system_id' => $systemId],
            'created_at DESC'
        );
    }

    /**
     * دریافت دیتاست با اطلاعات پروژه
     */
    public function findWithProject(int $datasetId): ?array
    {
        return $this->queryOne(
            "SELECT d.*, 
                    p.name AS project_name,
                    p.product_name,
                    p.process_name,
                    p.ctq,
                    p.unit
             FROM {$this->table} d
             JOIN qc_projects p ON p.id = d.project_id
             WHERE d.id = ? LIMIT 1",
            [$datasetId]
        );
    }

    /**
     * دریافت لیست دیتاست‌ها برای dropdown
     */
    public function listForProject(int $projectId): array
    {
        return $this->query(
            "SELECT id, name, chart_type, subgroup_size
             FROM {$this->table}
             WHERE project_id = ?
             ORDER BY created_at DESC",
            [$projectId]
        );
    }

    /**
     * دریافت لیست دیتاست‌ها برای dropdown (کل سیستم)
     */
    public function listForSystem(int $systemId): array
    {
        return $this->query(
            "SELECT d.id, d.name, d.chart_type, p.name AS project_name
             FROM {$this->table} d
             JOIN qc_projects p ON p.id = d.project_id
             WHERE d.system_id = ?
             ORDER BY d.created_at DESC",
            [$systemId]
        );
    }

    // ═════════════════════════════════════════════
    // ایجاد / ویرایش
    // ═════════════════════════════════════════════

    /**
     * ایجاد دیتاست جدید
     */
    public function createForProject(int $systemId, int $projectId, array $data): int
    {
        $payload = [
            'system_id'     => $systemId,
            'project_id'    => $projectId,
            'name'          => $data['name']          ?? '',
            'chart_type'    => $data['chart_type']    ?? 'xbar_r',
            'subgroup_size' => $data['subgroup_size'] ?? null,
            'spec_lsl'      => $data['spec_lsl']      ?? null,
            'spec_usl'      => $data['spec_usl']      ?? null,
            'spec_target'   => $data['spec_target']   ?? null,
            'data_json'     => $data['data_json']     ?? null,
            'notes'         => $data['notes']         ?? null,
        ];
        return (int) $this->create($payload);
    }

    /**
     * بررسی مالکیت دیتاست
     */
    public function belongsToSystem(int $datasetId, int $systemId): bool
    {
        $row = $this->queryOne(
            "SELECT id FROM {$this->table} WHERE id = ? AND system_id = ? LIMIT 1",
            [$datasetId, $systemId]
        );
        return $row !== null;
    }

    // ═════════════════════════════════════════════
    // داده‌ها
    // ═════════════════════════════════════════════

    /**
     * دریافت اندازه‌گیری‌های یک دیتاست
     */
    public function getMeasurements(int $datasetId): array
    {
        return $this->query(
            "SELECT * FROM qc_measurements
             WHERE dataset_id = ?
             ORDER BY subgroup_no ASC, sample_no ASC",
            [$datasetId]
        );
    }

    /**
     * دریافت داده‌ها به صورت گروه‌بندی‌شده (برای نمودارهای متغیر)
     * خروجی: [[v1, v2, ...], [v1, v2, ...], ...]
     */
    public function getGroupedData(int $datasetId): array
    {
        $rows = $this->getMeasurements($datasetId);
        $grouped = [];
        foreach ($rows as $r) {
            $sg = (int) $r['subgroup_no'];
            if (!isset($grouped[$sg])) {
                $grouped[$sg] = [];
            }
            $grouped[$sg][] = (float) $r['value'];
        }
        ksort($grouped);
        return array_values($grouped);
    }

    /**
     * دریافت داده‌های صفتی (برای p / np / c / u)
     * خروجی: [
     *   ['subgroup_no' => 1, 'defectives' => x, 'defects' => y, 'sample_size' => n],
     *   ...
     * ]
     */
    public function getAttributeData(int $datasetId): array
    {
        return $this->query(
            "SELECT subgroup_no,
                    SUM(is_defective) AS defectives,
                    SUM(defect_count) AS defects,
                    COUNT(*)          AS sample_size
             FROM qc_measurements
             WHERE dataset_id = ?
             GROUP BY subgroup_no
             ORDER BY subgroup_no ASC",
            [$datasetId]
        );
    }

    /**
     * شمارش اندازه‌گیری‌ها
     */
    public function getMeasurementCount(int $datasetId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM qc_measurements WHERE dataset_id = ?",
            [$datasetId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * شمارش زیرگروه‌ها
     */
    public function getSubgroupCount(int $datasetId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(DISTINCT subgroup_no) AS c
             FROM qc_measurements
             WHERE dataset_id = ?",
            [$datasetId]
        );
        return (int) ($row['c'] ?? 0);
    }

    // ═════════════════════════════════════════════
    // Bulk Insert
    // ═════════════════════════════════════════════

    /**
     * ذخیره انبوه اندازه‌گیری‌ها
     *
     * @param int   $datasetId
     * @param int   $systemId
     * @param array $rows  [
     *   ['subgroup_no' => 1, 'sample_no' => 1, 'value' => 10.5,
     *    'is_defective' => 0, 'defect_count' => 0, 'measured_at' => null],
     *   ...
     * ]
     */
    public function bulkInsertMeasurements(int $datasetId, int $systemId, array $rows): int
    {
        if (empty($rows)) return 0;

        // ابتدا پاک‌سازی داده‌های قبلی
        $this->db->prepare(
            "DELETE FROM qc_measurements WHERE dataset_id = ?"
        )->execute([$datasetId]);

        // ساخت Bulk Insert
        $values = [];
        $params = [];
        foreach ($rows as $r) {
            $values[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
            $params[] = $systemId;
            $params[] = $datasetId;
            $params[] = (int) ($r['subgroup_no'] ?? 1);
            $params[] = (int) ($r['sample_no']   ?? 1);
            $params[] = (float) ($r['value']        ?? 0);
            $params[] = (int)   ($r['is_defective'] ?? 0);
            $params[] = (int)   ($r['defect_count'] ?? 0);
            $params[] = $r['measured_at'] ?? null;
        }

        $sql = "INSERT INTO qc_measurements
                (system_id, dataset_id, subgroup_no, sample_no, value, is_defective, defect_count, measured_at)
                VALUES " . implode(', ', $values);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * ذخیره داده‌های خام در data_json
     */
    public function saveRawData(int $datasetId, array $data): bool
    {
        return $this->update($datasetId, [
            'data_json' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * خواندن داده‌های خام از data_json
     */
    public function getRawData(int $datasetId): array
    {
        $row = $this->find($datasetId);
        if (!$row || empty($row['data_json'])) return [];
        return json_decode($row['data_json'], true) ?: [];
    }

    // ═════════════════════════════════════════════
    // کمکی‌ها
    // ═════════════════════════════════════════════

    /**
     * آیا نمودار متغیر است؟
     */
    public function isVariableChart(string $chartType): bool
    {
        return in_array($chartType, self::VARIABLE_CHARTS, true);
    }

    /**
     * آیا نمودار صفتی است؟
     */
    public function isAttributeChart(string $chartType): bool
    {
        return in_array($chartType, self::ATTRIBUTE_CHARTS, true);
    }

    /**
     * برچسب فارسی نوع نمودار
     */
    public function getChartLabel(string $chartType): string
    {
        return self::CHART_TYPES[$chartType] ?? $chartType;
    }

    /**
     * حذف دیتاست + اندازه‌گیری‌ها + نتایج
     */
    public function deleteWithDependencies(int $datasetId): bool
    {
        $this->db->prepare("DELETE FROM qc_measurements        WHERE dataset_id = ?")->execute([$datasetId]);
        $this->db->prepare("DELETE FROM qc_control_charts      WHERE dataset_id = ?")->execute([$datasetId]);
        $this->db->prepare("DELETE FROM qc_capability_studies  WHERE dataset_id = ?")->execute([$datasetId]);
        return $this->delete($datasetId);
    }
}