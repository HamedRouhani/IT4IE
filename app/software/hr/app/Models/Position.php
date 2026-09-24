<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Position Model - مدل پست سازمانی
 * ============================================================
 * مسیر: app/software/hr/app/Models/Position.php
 * ============================================================
 */
class Position extends BaseModel
{
    protected $table = 'positions';

    protected $fillable = [
        'department_id',
        'grade_id',
        'parent_position_id',
        'code',
        'title',
        'position_type',
        'employment_type',
        'headcount',
        'filled_count',
        'min_education',
        'min_experience_years',
        'is_managerial',
        'is_critical',
        'is_active',
        'description',
    ];

    /**
     * دریافت همه پست‌ها با اطلاعات کامل
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT p.*, 
                       d.name AS department_name,
                       g.code AS grade_code,
                       g.name AS grade_name,
                       parent.title AS parent_title,
                       (p.headcount - p.filled_count) AS open_count
                FROM {$this->tableName()} p
                LEFT JOIN {$this->prefix}departments d ON p.department_id = d.id
                LEFT JOIN {$this->prefix}job_grades g ON p.grade_id = g.id
                LEFT JOIN {$this->tableName()} parent ON p.parent_position_id = parent.id
                WHERE p.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (p.title LIKE ? OR p.code LIKE ?)";
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND p.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        if (!empty($filters['grade_id'])) {
            $sql .= " AND p.grade_id = ?";
            $params[] = (int) $filters['grade_id'];
        }

        if (!empty($filters['is_active'])) {
            $sql .= " AND p.is_active = 1";
        }

        if (!empty($filters['has_open'])) {
            $sql .= " AND (p.headcount - p.filled_count) > 0";
        }

        if (!empty($filters['is_critical'])) {
            $sql .= " AND p.is_critical = 1";
        }

        $sql .= " ORDER BY 
                    d.name ASC,
                    p.is_managerial DESC,
                    p.title ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT p.*, 
                       d.name AS department_name,
                       g.code AS grade_code,
                       g.name AS grade_name,
                       parent.title AS parent_title
                FROM {$this->tableName()} p
                LEFT JOIN {$this->prefix}departments d ON p.department_id = d.id
                LEFT JOIN {$this->prefix}job_grades g ON p.grade_id = g.id
                LEFT JOIN {$this->tableName()} parent ON p.parent_position_id = parent.id
                WHERE p.id = ? AND p.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * به‌روزرسانی filled_count برای یک پست
     */
    public function updateFilledCount(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->tableName()} 
             SET filled_count = (
                 SELECT COUNT(*) FROM {$this->prefix}employees 
                 WHERE position_id = ? AND system_id = ? 
                   AND employment_status = 'active'
             )
             WHERE id = ? AND system_id = ?"
        );
        $stmt->execute([$id, $this->systemId, $id, $this->systemId]);
    }

    /**
     * به‌روزرسانی همه filled_count
     */
    public function updateAllFilledCounts(): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->tableName()} p
             SET filled_count = (
                 SELECT COUNT(*) FROM {$this->prefix}employees e
                 WHERE e.position_id = p.id 
                   AND e.system_id = p.system_id
                   AND e.employment_status = 'active'
             )
             WHERE p.system_id = ?"
        );
        $stmt->execute([$this->systemId]);
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
     * بررسی استفاده در کارکنان
     */
    public function isUsedInEmployees(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}employees 
             WHERE position_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی فرزند داشتن (پست‌های زیرمجموعه)
     */
    public function hasChildren(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->tableName()} 
             WHERE parent_position_id = ? AND system_id = ? LIMIT 1"
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
            "SELECT p.id, p.code, p.title, d.name AS department_name
             FROM {$this->tableName()} p
             LEFT JOIN {$this->prefix}departments d ON p.department_id = d.id
             WHERE p.system_id = ? AND p.is_active = 1
             ORDER BY d.name ASC, p.title ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار پست‌ها
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN is_managerial = 1 THEN 1 ELSE 0 END) AS managerial,
                SUM(CASE WHEN is_critical = 1 THEN 1 ELSE 0 END) AS critical,
                SUM(headcount) AS total_headcount,
                SUM(filled_count) AS total_filled,
                SUM(headcount - filled_count) AS total_open
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'             => (int) ($row['total'] ?? 0),
            'active'            => (int) ($row['active'] ?? 0),
            'managerial'        => (int) ($row['managerial'] ?? 0),
            'critical'          => (int) ($row['critical'] ?? 0),
            'total_headcount'   => (int) ($row['total_headcount'] ?? 0),
            'total_filled'      => (int) ($row['total_filled'] ?? 0),
            'total_open'        => (int) ($row['total_open'] ?? 0),
        ];
    }
}