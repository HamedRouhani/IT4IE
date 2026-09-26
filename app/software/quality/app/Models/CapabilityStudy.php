<?php
/**
 * ============================================================
 * Quality Analyzer — CapabilityStudy Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/CapabilityStudy.php
 * جدول: qc_capability_studies
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class CapabilityStudy extends Model
{
    protected $table      = 'qc_capability_studies';
    protected $primaryKey = 'id';

    // ═════════════════════════════════════════════
    // دریافت‌ها
    // ═════════════════════════════════════════════

    /**
     * دریافت آخرین مطالعه قابلیت یک دیتاست
     */
    public function findByDataset(int $datasetId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table}
             WHERE dataset_id = ?
             ORDER BY created_at DESC
             LIMIT 1",
            [$datasetId]
        );
    }

    /**
     * دریافت همه مطالعات قابلیت یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->query(
            "SELECT cs.*,
                    d.name AS dataset_name,
                    p.name AS project_name,
                    p.unit AS unit
             FROM {$this->table} cs
             JOIN qc_datasets d ON d.id = cs.dataset_id
             JOIN qc_projects p ON p.id = d.project_id
             WHERE cs.system_id = ?
             ORDER BY cs.created_at DESC",
            [$systemId]
        );
    }

    /**
     * دریافت مطالعات با آمار کامل
     */
    public function findBySystemWithStats(int $systemId): array
    {
        return $this->query(
            "SELECT cs.*,
                    d.name AS dataset_name,
                    p.name AS project_name,
                    CASE 
                        WHEN cs.cpk >= 1.33 THEN 'capable'
                        WHEN cs.cpk >= 1.00 THEN 'marginal'
                        ELSE 'not_capable'
                    END AS verdict
             FROM {$this->table} cs
             JOIN qc_datasets d ON d.id = cs.dataset_id
             JOIN qc_projects p ON p.id = d.project_id
             WHERE cs.system_id = ?
             ORDER BY cs.created_at DESC",
            [$systemId]
        );
    }

    // ═════════════════════════════════════════════
    // ذخیره
    // ═════════════════════════════════════════════

    /**
     * ذخیره مطالعه قابلیت برای دیتاست (حذف قبلی + درج جدید)
     */
    public function saveForDataset(int $systemId, int $datasetId, array $data): int
    {
        // حذف مطالعه قبلی همین دیتاست
        $this->db->prepare(
            "DELETE FROM {$this->table} WHERE dataset_id = ?"
        )->execute([$datasetId]);

        $payload = [
            'system_id'   => $systemId,
            'dataset_id'  => $datasetId,
            'lsl'         => $data['lsl']         ?? null,
            'usl'         => $data['usl']         ?? null,
            'target'      => $data['target']      ?? null,
            'mean'        => $data['mean']        ?? null,
            'std_within'  => $data['std_within']  ?? null,
            'std_overall' => $data['std_overall'] ?? null,
            'cp'          => $data['cp']          ?? null,
            'cpk'         => $data['cpk']         ?? null,
            'pp'          => $data['pp']          ?? null,
            'ppk'         => $data['ppk']         ?? null,
            'cpm'         => $data['cpm']         ?? null,
            'cpu'         => $data['cpu']         ?? null,
            'cpl'         => $data['cpl']         ?? null,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return (int) $this->create($payload);
    }

    // ═════════════════════════════════════════════
    // ارزیابی
    // ═════════════════════════════════════════════

    /**
     * ارزیابی متنی بر اساس Cpk
     */
    public static function verdict(?float $cpk): string
    {
        if ($cpk === null)  return 'unknown';
        if ($cpk >= 1.67)   return 'excellent';
        if ($cpk >= 1.33)   return 'capable';
        if ($cpk >= 1.00)   return 'marginal';
        return 'not_capable';
    }

    /**
     * برچسب فارسی ارزیابی
     */
    public static function verdictLabel(?float $cpk): string
    {
        return match (self::verdict($cpk)) {
            'excellent'   => 'عالی',
            'capable'     => 'قابل قبول',
            'marginal'    => 'مرزی',
            'not_capable' => 'ناتوان',
            default       => 'نامشخص',
        };
    }

    /**
     * رنگ ارزیابی (برای badge)
     */
    public static function verdictColor(?float $cpk): string
    {
        return match (self::verdict($cpk)) {
            'excellent'   => 'success',
            'capable'     => 'success',
            'marginal'    => 'warning',
            'not_capable' => 'danger',
            default       => 'secondary',
        };
    }

    // ═════════════════════════════════════════════
    // آمار
    // ═════════════════════════════════════════════

    /**
     * شمارش کل مطالعات
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
     * شمارش مطالعات قابل قبول (Cpk >= 1.33)
     */
    public function countCapable(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE system_id = ? AND cpk >= 1.33",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * میانگین Cpk سیستم
     */
    public function averageCpk(int $systemId): ?float
    {
        $row = $this->queryOne(
            "SELECT AVG(cpk) AS avg_cpk FROM {$this->table}
             WHERE system_id = ? AND cpk IS NOT NULL",
            [$systemId]
        );
        return $row && $row['avg_cpk'] !== null ? (float) $row['avg_cpk'] : null;
    }

    /**
     * حذف مطالعه یک دیتاست
     */
    public function deleteByDataset(int $datasetId): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE dataset_id = ?"
        );
        return $stmt->execute([$datasetId]);
    }
}