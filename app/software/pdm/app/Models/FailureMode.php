<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * FailureMode Model - مدل حالات خرابی و FMEA
 * ============================================================
 * مسیر: app/software/pdm/app/Models/FailureMode.php
 * ============================================================
 */
class FailureMode extends BaseModel
{
    protected $table = 'failure_modes';

    protected $fillable = [
        'asset_id',
        'code',
        'name',
        'description',
        'severity',
        'occurrence',
        'detection',
        'recommended_action',
    ];

    /**
     * دریافت همه حالات خرابی با اطلاعات دارایی
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT fm.*, 
                       a.name AS asset_name,
                       a.asset_code,
                       a.criticality AS asset_criticality,
                       (fm.severity * fm.occurrence * fm.detection) AS rpn
                FROM {$this->tableName()} fm
                LEFT JOIN {$this->prefix}assets a ON fm.asset_id = a.id
                WHERE fm.system_id = ?
                ORDER BY rpn DESC, fm.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT fm.*, 
                       a.name AS asset_name,
                       a.asset_code,
                       (fm.severity * fm.occurrence * fm.detection) AS rpn
                FROM {$this->tableName()} fm
                LEFT JOIN {$this->prefix}assets a ON fm.asset_id = a.id
                WHERE fm.id = ? AND fm.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * دریافت حالات خرابی یک دارایی خاص
     */
    public function getByAsset(int $assetId): array
    {
        $sql = "SELECT fm.*,
                       (fm.severity * fm.occurrence * fm.detection) AS rpn
                FROM {$this->tableName()} fm
                WHERE fm.asset_id = ? AND fm.system_id = ?
                ORDER BY rpn DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assetId, $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * محاسبه RPN
     */
    public static function calculateRPN(int $severity, int $occurrence, int $detection): int
    {
        return $severity * $occurrence * $detection;
    }

    /**
     * سطح ریسک بر اساس RPN
     */
    public static function getRiskLevel(int $rpn): string
    {
        if ($rpn >= 200) return 'critical';
        if ($rpn >= 100) return 'high';
        if ($rpn >= 50) return 'medium';
        return 'low';
    }

    /**
     * برچسب سطح ریسک
     */
    public static function getRiskLabel(int $rpn): string
    {
        if ($rpn >= 200) return 'بحرانی';
        if ($rpn >= 100) return 'بالا';
        if ($rpn >= 50) return 'متوسط';
        return 'پایین';
    }

    /**
     * کلاس CSS سطح ریسک
     */
    public static function getRiskClass(int $rpn): string
    {
        if ($rpn >= 200) return 'pdm-criticality-critical';
        if ($rpn >= 100) return 'pdm-criticality-high';
        if ($rpn >= 50) return 'pdm-criticality-medium';
        return 'pdm-criticality-low';
    }

    /**
     * توزیع RPN
     */
    public function getRiskDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                SUM(CASE WHEN (severity * occurrence * detection) >= 200 THEN 1 ELSE 0 END) AS critical,
                SUM(CASE WHEN (severity * occurrence * detection) BETWEEN 100 AND 199 THEN 1 ELSE 0 END) AS high,
                SUM(CASE WHEN (severity * occurrence * detection) BETWEEN 50 AND 99 THEN 1 ELSE 0 END) AS medium,
                SUM(CASE WHEN (severity * occurrence * detection) < 50 THEN 1 ELSE 0 END) AS low
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'critical' => (int) ($row['critical'] ?? 0),
            'high'     => (int) ($row['high'] ?? 0),
            'medium'   => (int) ($row['medium'] ?? 0),
            'low'      => (int) ($row['low'] ?? 0),
        ];
    }
}