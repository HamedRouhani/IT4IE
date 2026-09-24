<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Compensation Model - حقوق و دستمزد
 * ============================================================
 * مسیر: app/software/hr/app/Models/Compensation.php
 *
 * ستون‌های جدول hr_compensations:
 *   id, system_id, employee_id, effective_date, end_date,
 *   base_salary, housing_allowance, food_allowance, transportation_allowance,
 *   child_allowance, seniority_allowance, other_allowances, overtime_rate,
 *   total_fixed, currency, change_reason, approved_by, document_ref, notes,
 *   created_at
 * ============================================================
 */
class Compensation extends BaseModel
{
    protected $table = 'compensations';

    protected $fillable = [
        'employee_id', 'effective_date', 'end_date',
        'base_salary', 'housing_allowance', 'food_allowance',
        'transportation_allowance', 'child_allowance', 'seniority_allowance',
        'other_allowances', 'overtime_rate', 'total_fixed',
        'currency', 'change_reason', 'approved_by', 'document_ref', 'notes',
    ];

    /**
     * واحد پول
     */
    public static function getCurrencyOptions(): array
    {
        return [
            'IRR' => 'ریال',
            'IRT' => 'تومان',
            'USD' => 'دلار',
            'EUR' => 'یورو',
        ];
    }

    /**
     * فیلدهای مالی برای جمع‌بندی
     */
    public static function getSalaryFields(): array
    {
        return [
            'base_salary'              => 'حقوق پایه',
            'housing_allowance'        => 'حق مسکن',
            'food_allowance'           => 'حق خواربار',
            'transportation_allowance' => 'حق ایاب و ذهاب',
            'child_allowance'          => 'حق اولاد',
            'seniority_allowance'      => 'حق سنوات',
            'other_allowances'         => 'سایر مزایا',
        ];
    }

    /**
     * جمع کل حقوق ثابت
     */
    public static function calculateTotalFixed(array $data): float
    {
        $fields = array_keys(self::getSalaryFields());
        $total = 0.0;
        foreach ($fields as $f) {
            if (isset($data[$f]) && $data[$f] !== '' && $data[$f] !== null) {
                $total += (float) $data[$f];
            }
        }
        return $total;
    }

    /**
     * جستجو با فیلترها
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT c.*,
                       e.first_name, e.last_name, e.employee_code,
                       d.name AS department_name,
                       p.title AS position_title,
                       approver.first_name AS approver_first, approver.last_name AS approver_last
                FROM {$this->tableName()} c
                LEFT JOIN hr_employees e ON e.id = c.employee_id AND e.system_id = c.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = c.system_id
                LEFT JOIN hr_positions p ON p.id = e.position_id AND p.system_id = c.system_id
                LEFT JOIN hr_employees approver ON approver.id = c.approved_by AND approver.system_id = c.system_id
                WHERE c.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (e.first_name LIKE :q OR e.last_name LIKE :q2 OR e.employee_code LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND c.employee_id = :eid";
            $params['eid'] = (int) $filters['employee_id'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :did";
            $params['did'] = (int) $filters['department_id'];
        }
        if (!empty($filters['currency'])) {
            $sql .= " AND c.currency = :cur";
            $params['cur'] = $filters['currency'];
        }
        if (!empty($filters['active_only'])) {
            $sql .= " AND (c.end_date IS NULL OR c.end_date >= CURDATE())";
        }

        $sql .= " ORDER BY c.effective_date DESC, c.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار
     */
    public function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN end_date IS NULL OR end_date >= CURDATE() THEN 1 ELSE 0 END) AS active,
                    COUNT(DISTINCT employee_id) AS total_employees,
                    COALESCE(AVG(CASE WHEN end_date IS NULL OR end_date >= CURDATE() THEN total_fixed END), 0) AS avg_salary,
                    COALESCE(SUM(CASE WHEN end_date IS NULL OR end_date >= CURDATE() THEN total_fixed END), 0) AS total_payroll
                FROM {$this->tableName()}
                WHERE system_id = :sid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'           => (int) ($row['total'] ?? 0),
            'active'          => (int) ($row['active'] ?? 0),
            'total_employees' => (int) ($row['total_employees'] ?? 0),
            'avg_salary'      => round((float) ($row['avg_salary'] ?? 0), 0),
            'total_payroll'   => round((float) ($row['total_payroll'] ?? 0), 0),
        ];
    }

    /**
     * یافتن با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*,
                       e.first_name, e.last_name, e.employee_code, e.mobile, e.email,
                       e.hire_date, e.position_id, e.department_id,
                       d.name AS department_name,
                       p.title AS position_title,
                       approver.first_name AS approver_first, approver.last_name AS approver_last
                FROM {$this->tableName()} c
                LEFT JOIN hr_employees e ON e.id = c.employee_id AND e.system_id = c.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = c.system_id
                LEFT JOIN hr_positions p ON p.id = e.position_id AND p.system_id = c.system_id
                LEFT JOIN hr_employees approver ON approver.id = c.approved_by AND approver.system_id = c.system_id
                WHERE c.id = :id AND c.system_id = :sid LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $row['employee_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['approver_name'] = trim(($row['approver_first'] ?? '') . ' ' . ($row['approver_last'] ?? ''));
        return $row;
    }

    /**
     * آخرین حکم حقوقی فعال یک کارمند
     */
    public function getCurrentByEmployee(int $employeeId): ?array
    {
        $sql = "SELECT * FROM {$this->tableName()}
                WHERE employee_id = :eid AND system_id = :sid
                  AND (end_date IS NULL OR end_date >= CURDATE())
                ORDER BY effective_date DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['eid' => $employeeId, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * تاریخچه حقوق یک کارمند
     */
    public function getHistoryByEmployee(int $employeeId): array
    {
        $sql = "SELECT * FROM {$this->tableName()}
                WHERE employee_id = :eid AND system_id = :sid
                ORDER BY effective_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['eid' => $employeeId, 'sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی تداخل تاریخ (برای یک کارمند)
     */
    public function hasDateConflict(int $employeeId, string $effectiveDate, ?string $endDate, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName()}
                WHERE employee_id = :eid AND system_id = :sid";
        $params = [
            'eid' => $employeeId,
            'sid' => $this->systemId,
        ];

        // منطق تداخل: بازه‌های [effective_date, end_date] نباید همپوشانی داشته باشند
        if ($endDate) {
            $sql .= " AND (
                (effective_date <= :ed AND (end_date IS NULL OR end_date >= :ef))
            )";
            $params['ed'] = $endDate;
            $params['ef'] = $effectiveDate;
        } else {
            $sql .= " AND (end_date IS NULL OR end_date >= :ef)";
            $params['ef'] = $effectiveDate;
        }

        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * لیست کارمندانی که حکم حقوقی فعال دارند (برای select)
     */
    public function getEmployeesWithCompensation(): array
    {
        $sql = "SELECT DISTINCT e.id, e.first_name, e.last_name, e.employee_code
                FROM hr_employees e
                INNER JOIN {$this->tableName()} c ON c.employee_id = e.id AND c.system_id = e.system_id
                WHERE e.system_id = :sid
                  AND (c.end_date IS NULL OR c.end_date >= CURDATE())
                ORDER BY e.first_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}