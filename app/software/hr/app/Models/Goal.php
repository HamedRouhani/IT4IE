<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Goal Model - مدیریت اهداف (OKR / MBO)
 * ============================================================
 */
class Goal extends BaseModel
{
    protected $table = 'goals';

    protected $fillable = [
        'employee_id', 'parent_goal_id', 'title', 'description',
        'goal_type', 'category', 'weight', 'target_value', 'current_value',
        'unit', 'start_date', 'due_date', 'priority', 'progress',
        'status', 'review_period', 'notes', 'created_by',
    ];

    public static function getTypeOptions(): array
    {
        return [
            'okr'      => 'OKR (اهداف و نتایج کلیدی)',
            'mbo'      => 'MBO (مدیریت بر مبنای هدف)',
            'kpi'      => 'KPI (شاخص کلیدی عملکرد)',
            'personal' => 'هدف شخصی',
            'team'     => 'هدف تیمی',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft'     => 'پیش‌نویس',
            'active'    => 'فعال',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            'on_hold'   => 'متوقف',
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            'low'      => 'پایین',
            'normal'   => 'عادی',
            'high'     => 'بالا',
            'urgent'   => 'فوری',
            'critical' => 'بحرانی',
        ];
    }

    /**
     * جستجو با فیلترها (با اعمال system_id)
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT g.*,
                       e.first_name, e.last_name, e.employee_code,
                       d.name AS department_name,
                       p.title AS parent_title
                FROM {$this->tableName()} g
                LEFT JOIN hr_employees e ON e.id = g.employee_id AND e.system_id = g.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = g.system_id
                LEFT JOIN {$this->tableName()} p ON p.id = g.parent_goal_id AND p.system_id = g.system_id
                WHERE g.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (g.title LIKE :q OR g.description LIKE :q2)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND g.employee_id = :eid";
            $params['eid'] = (int) $filters['employee_id'];
        }
        if (!empty($filters['goal_type'])) {
            $sql .= " AND g.goal_type = :gt";
            $params['gt'] = $filters['goal_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND g.status = :st";
            $params['st'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND g.priority = :pr";
            $params['pr'] = $filters['priority'];
        }
        if (!empty($filters['period'])) {
            $sql .= " AND g.review_period = :rp";
            $params['rp'] = $filters['period'];
        }

        $sql .= " ORDER BY g.due_date ASC, g.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats(?int $employeeId = null): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='on_hold' THEN 1 ELSE 0 END) AS on_hold,
                    SUM(CASE WHEN due_date < CURDATE() AND status='active' THEN 1 ELSE 0 END) AS overdue,
                    COALESCE(AVG(progress), 0) AS avg_progress
                FROM {$this->tableName()}
                WHERE system_id = :sid";
        $params = ['sid' => $this->systemId];

        if ($employeeId) {
            $sql .= " AND employee_id = :eid";
            $params['eid'] = $employeeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'        => (int) ($row['total'] ?? 0),
            'active'       => (int) ($row['active'] ?? 0),
            'completed'    => (int) ($row['completed'] ?? 0),
            'on_hold'      => (int) ($row['on_hold'] ?? 0),
            'overdue'      => (int) ($row['overdue'] ?? 0),
            'avg_progress' => round((float) ($row['avg_progress'] ?? 0), 1),
        ];
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT g.*,
                       e.first_name, e.last_name, e.employee_code, e.mobile,
                       d.name AS department_name,
                       p.title AS parent_title,
                       creator.first_name AS creator_first, creator.last_name AS creator_last
                FROM {$this->tableName()} g
                LEFT JOIN hr_employees e ON e.id = g.employee_id AND e.system_id = g.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = g.system_id
                LEFT JOIN {$this->tableName()} p ON p.id = g.parent_goal_id AND p.system_id = g.system_id
                LEFT JOIN hr_employees creator ON creator.id = g.created_by AND creator.system_id = g.system_id
                WHERE g.id = :id AND g.system_id = :sid LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $row['employee_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        return $row;
    }

    public function getChildren(int $parentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()}
             WHERE parent_goal_id = :pid AND system_id = :sid
             ORDER BY id ASC"
        );
        $stmt->execute(['pid' => $parentId, 'sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function recalcProgress(int $id): void
    {
        $goal = $this->find($id);
        if (!$goal || empty($goal['target_value']) || (float) $goal['target_value'] <= 0) {
            return;
        }
        $progress = min(100, (int) round(((float) $goal['current_value'] / (float) $goal['target_value']) * 100));
        $this->update($id, ['progress' => $progress]);
    }
}