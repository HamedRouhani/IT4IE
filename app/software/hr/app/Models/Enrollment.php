<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Enrollment Model - ثبت‌نام‌های دوره‌های آموزشی
 * ============================================================
 */
class Enrollment extends BaseModel
{
    protected $table = 'enrollments';

    protected $fillable = [
        'training_id', 'employee_id', 'enrolled_date', 'status',
        'attendance_percent', 'score', 'rating',
        'certificate_issued', 'certificate_number',
        'feedback', 'employee_notes', 'manager_notes', 'completed_at',
    ];

    public static function getStatusOptions(): array
    {
        return [
            'pending'    => 'در انتظار تأیید',
            'approved'   => 'تأیید شده',
            'rejected'   => 'رد شده',
            'attended'   => 'حاضر شده',
            'completed'  => 'تکمیل شده',
            'failed'     => 'قبول نشده',
            'cancelled'  => 'لغو شده',
            'no_show'    => 'غایب',
        ];
    }

    public static function getRatingOptions(): array
    {
        return [
            'excellent'         => 'عالی',
            'very_good'         => 'خیلی خوب',
            'good'              => 'خوب',
            'satisfactory'      => 'قابل قبول',
            'needs_improvement' => 'نیاز به بهبود',
            'unsatisfactory'    => 'غیرقابل قبول',
        ];
    }

    public function search(array $filters = []): array
    {
        $sql = "SELECT e.*,
                       t.title AS training_title, t.code AS training_code,
                       t.start_date AS training_start, t.end_date AS training_end,
                       t.status AS training_status,
                       emp.first_name, emp.last_name, emp.employee_code,
                       d.name AS department_name
                FROM {$this->tableName()} e
                LEFT JOIN hr_trainings t ON t.id = e.training_id AND t.system_id = e.system_id
                LEFT JOIN hr_employees emp ON emp.id = e.employee_id AND emp.system_id = e.system_id
                LEFT JOIN hr_departments d ON d.id = emp.department_id AND d.system_id = e.system_id
                WHERE e.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (emp.first_name LIKE :q OR emp.last_name LIKE :q2 OR t.title LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['training_id'])) {
            $sql .= " AND e.training_id = :tid";
            $params['tid'] = (int) $filters['training_id'];
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND e.employee_id = :eid";
            $params['eid'] = (int) $filters['employee_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = :st";
            $params['st'] = $filters['status'];
        }

        $sql .= " ORDER BY e.enrolled_date DESC, e.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats(?int $trainingId = null): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='attended' THEN 1 ELSE 0 END) AS attended,
                    SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) AS failed,
                    COALESCE(AVG(score), 0) AS avg_score
                FROM {$this->tableName()}
                WHERE system_id = :sid";
        $params = ['sid' => $this->systemId];

        if ($trainingId) {
            $sql .= " AND training_id = :tid";
            $params['tid'] = $trainingId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'     => (int) ($row['total'] ?? 0),
            'pending'   => (int) ($row['pending'] ?? 0),
            'approved'  => (int) ($row['approved'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'attended'  => (int) ($row['attended'] ?? 0),
            'failed'    => (int) ($row['failed'] ?? 0),
            'avg_score' => round((float) ($row['avg_score'] ?? 0), 2),
        ];
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*,
                       t.title AS training_title, t.code AS training_code,
                       t.start_date AS training_start, t.end_date AS training_end,
                       t.status AS training_status, t.duration_hours,
                       t.instructor, t.location, t.provider,
                       emp.first_name, emp.last_name, emp.employee_code, emp.mobile, emp.email,
                       d.name AS department_name
                FROM {$this->tableName()} e
                LEFT JOIN hr_trainings t ON t.id = e.training_id AND t.system_id = e.system_id
                LEFT JOIN hr_employees emp ON emp.id = e.employee_id AND emp.system_id = e.system_id
                LEFT JOIN hr_departments d ON d.id = emp.department_id AND d.system_id = e.system_id
                WHERE e.id = :id AND e.system_id = :sid LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $row['employee_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        return $row;
    }

    /**
     * دریافت لیست ثبت‌نام‌های یک کارمند
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT e.*,
                       t.title AS training_title, t.code AS training_code,
                       t.start_date, t.end_date, t.status AS training_status
                FROM {$this->tableName()} e
                LEFT JOIN hr_trainings t ON t.id = e.training_id AND t.system_id = e.system_id
                WHERE e.employee_id = :eid AND e.system_id = :sid
                ORDER BY e.enrolled_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['eid' => $employeeId, 'sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی تکراری بودن ثبت‌نام
     */
    public function isDuplicate(int $trainingId, int $employeeId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName()}
                WHERE training_id = :tid AND employee_id = :eid AND system_id = :sid";
        $params = [
            'tid' => $trainingId,
            'eid' => $employeeId,
            'sid' => $this->systemId,
        ];
        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}