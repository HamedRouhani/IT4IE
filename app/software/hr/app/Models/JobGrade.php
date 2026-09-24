<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * JobGrade Model - مدل طبقه‌بندی شغلی
 * ============================================================
 * مسیر: app/software/hr/app/Models/JobGrade.php
 * ============================================================
 */
class JobGrade extends BaseModel
{
    protected $table = 'job_grades';

    protected $fillable = [
        'code',
        'name',
        'level',
        'min_salary',
        'max_salary',
        'description',
    ];

    /**
     * دریافت همه طبقات مرتب‌شده بر اساس سطح
     */
    public function getAllWithDetails(): array
    {
        $sql = "SELECT g.*,
                       (SELECT COUNT(*) FROM {$this->prefix}positions p 
                        WHERE p.grade_id = g.id) AS positions_count,
                       (SELECT COUNT(*) FROM {$this->prefix}employees e 
                        WHERE e.grade_id = g.id AND e.employment_status = 'active') AS employees_count
                FROM {$this->tableName()} g
                WHERE g.system_id = ?
                ORDER BY g.level ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی تکراری بودن کد
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) {
            return false;
        }
        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND code = ?";
        $params = [$this->systemId, $code];

        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی تکراری بودن سطح
     */
    public function levelExists(int $level, ?int $exceptId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND level = ?";
        $params = [$this->systemId, $level];

        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی استفاده در پست‌ها
     */
    public function isUsedInPositions(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}positions 
             WHERE grade_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی استفاده در کارکنان
     */
    public function isUsedInEmployees(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}employees 
             WHERE grade_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * لیست ساده برای Select Box
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, code, name, level, min_salary, max_salary
             FROM {$this->tableName()} 
             WHERE system_id = ?
             ORDER BY level ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                MIN(level) AS min_level,
                MAX(level) AS max_level,
                AVG((min_salary + max_salary) / 2) AS avg_mid_salary
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'          => (int) ($row['total'] ?? 0),
            'min_level'      => (int) ($row['min_level'] ?? 0),
            'max_level'      => (int) ($row['max_level'] ?? 0),
            'avg_mid_salary' => (float) ($row['avg_mid_salary'] ?? 0),
        ];
    }
}