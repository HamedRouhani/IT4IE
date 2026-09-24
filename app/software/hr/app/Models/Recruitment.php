<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Recruitment Model - مدل نیازهای استخدامی
 * ============================================================
 */
class Recruitment extends BaseModel
{
    protected $table = 'recruitments';

    protected $fillable = [
        'position_id',
        'department_id',
        'request_number',
        'title',
        'description',
        'requirements',
        'headcount',
        'filled_count',
        'employment_type',
        'min_salary',
        'max_salary',
        'priority',
        'status',
        'opened_date',
        'target_date',
        'closed_date',
        'created_by',
    ];

    /**
     * جستجو و فیلتر
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT r.*, 
                       p.title AS position_title,
                       d.name AS department_name,
                       (r.headcount - r.filled_count) AS open_count,
                       (SELECT COUNT(*) FROM {$this->prefix}candidates c 
                        WHERE c.recruitment_id = r.id) AS candidates_count
                FROM {$this->tableName()} r
                LEFT JOIN {$this->prefix}positions p ON r.position_id = p.id
                LEFT JOIN {$this->prefix}departments d ON r.department_id = d.id
                WHERE r.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (r.title LIKE ? OR r.request_number LIKE ?)";
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND r.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND r.department_id = ?";
            $params[] = (int) $filters['department_id'];
        }

        $sql .= " ORDER BY 
                    FIELD(r.status, 'open', 'in_progress', 'draft', 'closed', 'cancelled'),
                    FIELD(r.priority, 'urgent', 'high', 'normal', 'low'),
                    r.opened_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT r.*, 
                       p.title AS position_title,
                       d.name AS department_name,
                       (r.headcount - r.filled_count) AS open_count
                FROM {$this->tableName()} r
                LEFT JOIN {$this->prefix}positions p ON r.position_id = p.id
                LEFT JOIN {$this->prefix}departments d ON r.department_id = d.id
                WHERE r.id = ? AND r.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * تولید شماره درخواست خودکار
     */
    public function generateRequestNumber(): string
    {
        $year = date('Y');
        $prefix = "REQ-{$year}-";

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->tableName()} 
             WHERE system_id = ? AND request_number LIKE ?"
        );
        $stmt->execute([$this->systemId, $prefix . '%']);
        $count = (int) $stmt->fetchColumn() + 1;

        return $prefix . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * بررسی تکراری بودن شماره درخواست
     */
    public function requestNumberExists(string $requestNumber, ?int $exceptId = null): bool
    {
        if (empty($requestNumber)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND request_number = ?";
        $params = [$this->systemId, $requestNumber];

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
     * آمار
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total_count,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count,
                SUM(headcount) AS total_headcount,
                SUM(filled_count) AS total_filled,
                SUM(headcount - filled_count) AS total_open
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'             => (int) ($row['total_count'] ?? 0),
            'open'              => (int) ($row['open_count'] ?? 0),
            'in_progress'       => (int) ($row['in_progress_count'] ?? 0),
            'closed'            => (int) ($row['closed_count'] ?? 0),
            'total_headcount'   => (int) ($row['total_headcount'] ?? 0),
            'total_filled'      => (int) ($row['total_filled'] ?? 0),
            'total_open'        => (int) ($row['total_open'] ?? 0),
        ];
    }

    /**
     * لیست ساده
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, request_number, title, status
             FROM {$this->tableName()} 
             WHERE system_id = ? AND status IN ('open', 'in_progress')
             ORDER BY opened_date DESC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * به‌روزرسانی filled_count
     */
    public function updateFilledCount(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE {$this->tableName()} 
             SET filled_count = (
                 SELECT COUNT(*) FROM {$this->prefix}candidates 
                 WHERE recruitment_id = ? AND status = 'hired'
             )
             WHERE id = ? AND system_id = ?"
        );
        $stmt->execute([$id, $id, $this->systemId]);
    }

    /**
     * برچسب‌ها
     */
    public static function getStatusOptions(): array
    {
        return [
            'draft'       => 'پیش‌نویس',
            'open'        => 'باز',
            'in_progress' => 'در جریان',
            'closed'      => 'بسته شده',
            'cancelled'   => 'لغو شده',
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            'low'    => 'پایین',
            'normal' => 'عادی',
            'high'   => 'بالا',
            'urgent' => 'فوری',
        ];
    }
}