<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Employee Model - مدل کارمند
 * ============================================================
 * مسیر: app/software/hr/app/Models/Employee.php
 * ============================================================
 */
class Employee extends BaseModel
{
    protected $table = 'employees';

    protected $fillable = [
        'department_id',
        'position_id',
        'grade_id',
        'manager_id',
        'employee_code',
        'national_id',
        'personnel_number',
        'first_name',
        'last_name',
        'father_name',
        'gender',
        'birth_date',
        'birth_place',
        'marital_status',
        'dependents_count',
        'military_status',
        'mobile',
        'phone',
        'email',
        'address',
        'postal_code',
        'education_level',
        'field_of_study',
        'university',
        'graduation_year',
        'hire_date',
        'contract_start_date',
        'contract_end_date',
        'contract_type',
        'probation_end_date',
        'employment_status',
        'termination_date',
        'termination_reason',
        'bank_name',
        'bank_account',
        'iban',
        'card_number',
        'insurance_number',
        'insurance_type',
        'tax_code',
        'photo_path',
        'skills',
        'languages',
        'certificates',
        'notes',
        'status',
    ];

    /**
     * جستجو و فیلتر کارکنان
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT e.*, 
                       d.name AS department_name,
                       p.title AS position_title,
                       g.code AS grade_code,
                       g.name AS grade_name,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name
                FROM {$this->tableName()} e
                LEFT JOIN {$this->prefix}departments d ON e.department_id = d.id
                LEFT JOIN {$this->prefix}positions p ON e.position_id = p.id
                LEFT JOIN {$this->prefix}job_grades g ON e.grade_id = g.id
                LEFT JOIN {$this->tableName()} m ON e.manager_id = m.id
                WHERE e.system_id = ?";
        $params = [$this->systemId];

        // جستجو
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? 
                       OR e.employee_code LIKE ? OR e.national_id LIKE ? 
                       OR e.mobile LIKE ? OR e.email LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        // فیلتر دپارتمان
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        // فیلتر پست
        if (!empty($filters['position_id'])) {
            $sql .= " AND e.position_id = ?";
            $params[] = (int) $filters['position_id'];
        }

        // فیلتر طبقه
        if (!empty($filters['grade_id'])) {
            $sql .= " AND e.grade_id = ?";
            $params[] = (int) $filters['grade_id'];
        }

        // فیلتر وضعیت اشتغال
        if (!empty($filters['employment_status'])) {
            $sql .= " AND e.employment_status = ?";
            $params[] = $filters['employment_status'];
        }

        // فیلتر جنسیت
        if (!empty($filters['gender'])) {
            $sql .= " AND e.gender = ?";
            $params[] = $filters['gender'];
        }

        // فیلتر مدرک
        if (!empty($filters['education_level'])) {
            $sql .= " AND e.education_level = ?";
            $params[] = $filters['education_level'];
        }

        // فیلتر قرارداد نزدیک به انقضا
        if (!empty($filters['expiring_contract'])) {
            $sql .= " AND e.contract_end_date IS NOT NULL 
                     AND e.contract_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }

        // فیلتر پایان دوره آزمایشی
        if (!empty($filters['expiring_probation'])) {
            $sql .= " AND e.probation_end_date IS NOT NULL 
                     AND e.probation_end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)";
        }

        $sql .= " ORDER BY e.last_name ASC, e.first_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت یک کارمند با جزئیات کامل
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*, 
                       d.name AS department_name,
                       d.code AS department_code,
                       p.title AS position_title,
                       p.code AS position_code,
                       g.code AS grade_code,
                       g.name AS grade_name,
                       CONCAT(m.first_name, ' ', m.last_name) AS manager_name,
                       m.employee_code AS manager_code
                FROM {$this->tableName()} e
                LEFT JOIN {$this->prefix}departments d ON e.department_id = d.id
                LEFT JOIN {$this->prefix}positions p ON e.position_id = p.id
                LEFT JOIN {$this->prefix}job_grades g ON e.grade_id = g.id
                LEFT JOIN {$this->tableName()} m ON e.manager_id = m.id
                WHERE e.id = ? AND e.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * بررسی تکراری بودن کد پرسنلی
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND employee_code = ?";
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
     * بررسی تکراری بودن کد ملی
     */
    public function nationalIdExists(string $nationalId, ?int $exceptId = null): bool
    {
        if (empty($nationalId)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND national_id = ?";
        $params = [$this->systemId, $nationalId];

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
     * تولید کد پرسنلی خودکار
     * فرمت: EMP-{counter}
     */
    public function generateEmployeeCode(): string
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->tableName()} WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $count = (int) $stmt->fetchColumn() + 1;

        do {
            $code = 'EMP-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * دریافت لیست ساده برای Select Box
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, employee_code, first_name, last_name,
                    CONCAT(first_name, ' ', last_name) AS full_name
             FROM {$this->tableName()} 
             WHERE system_id = ? AND employment_status = 'active'
             ORDER BY last_name ASC, first_name ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * توزیع بر اساس دپارتمان
     */
    public function getDepartmentDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.name, COUNT(e.id) AS count
             FROM {$this->prefix}departments d
             LEFT JOIN {$this->tableName()} e ON e.department_id = d.id 
                AND e.employment_status = 'active'
             WHERE d.system_id = ?
             GROUP BY d.id, d.name
             ORDER BY count DESC
             LIMIT 10"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * توزیع بر اساس جنسیت
     */
    public function getGenderDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT gender, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ? AND employment_status = 'active'
             GROUP BY gender"
        );
        $stmt->execute([$this->systemId]);

        $result = ['male' => 0, 'female' => 0, 'other' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['gender']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * توزیع بر اساس مدرک
     */
    public function getEducationDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT education_level, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ? AND employment_status = 'active'
             GROUP BY education_level"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * توزیع سنی
     */
    public function getAgeDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) < 25 THEN 1 ELSE 0 END) AS age_under_25,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 25 AND 34 THEN 1 ELSE 0 END) AS age_25_34,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 35 AND 44 THEN 1 ELSE 0 END) AS age_35_44,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 45 AND 54 THEN 1 ELSE 0 END) AS age_45_54,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 55 THEN 1 ELSE 0 END) AS age_55_plus
             FROM {$this->tableName()}
             WHERE system_id = ? AND employment_status = 'active' AND birth_date IS NOT NULL"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * آمار کارکنان
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total_count,
                SUM(CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN employment_status = 'on_leave' THEN 1 ELSE 0 END) AS on_leave_count,
                SUM(CASE WHEN employment_status = 'terminated' THEN 1 ELSE 0 END) AS terminated_count,
                SUM(CASE WHEN gender = 'male' AND employment_status = 'active' THEN 1 ELSE 0 END) AS male_count,
                SUM(CASE WHEN gender = 'female' AND employment_status = 'active' THEN 1 ELSE 0 END) AS female_count
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'       => (int) ($row['total_count'] ?? 0),
            'active'      => (int) ($row['active_count'] ?? 0),
            'on_leave'    => (int) ($row['on_leave_count'] ?? 0),
            'terminated'  => (int) ($row['terminated_count'] ?? 0),
            'male_count'  => (int) ($row['male_count'] ?? 0),
            'female_count'=> (int) ($row['female_count'] ?? 0),
        ];
    }

    /**
     * به‌روزرسانی filled_count در پست مرتبط
     */
    public function syncPositionCount(?int $positionId): void
    {
        if (!$positionId) return;

        $stmt = $this->db->prepare(
            "UPDATE {$this->prefix}positions 
             SET filled_count = (
                 SELECT COUNT(*) FROM {$this->tableName()} 
                 WHERE position_id = ? AND system_id = ? 
                   AND employment_status = 'active'
             )
             WHERE id = ? AND system_id = ?"
        );
        $stmt->execute([$positionId, $this->systemId, $positionId, $this->systemId]);
    }
}