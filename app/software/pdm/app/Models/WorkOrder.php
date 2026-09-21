<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * WorkOrder Model - مدل دستورکار (قلب سیستم)
 * ============================================================
 * مسیر: app/software/pdm/app/Models/WorkOrder.php
 * ============================================================
 */
class WorkOrder extends BaseModel
{
    protected $table = 'work_orders';

    protected $fillable = [
        'wo_number',
        'asset_id',
        'maintenance_type_id',
        'maintenance_plan_id',
        'title',
        'description',
        'priority',
        'status',
        'planned_date',
        'started_at',
        'completed_at',
        'resolution',
        'downtime_minutes',
        'cost',
    ];

    /**
     * دریافت همه دستورکارها با اطلاعات کامل
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT wo.*, 
                       a.name AS asset_name, 
                       a.asset_code,
                       a.criticality AS asset_criticality,
                       mt.name AS maintenance_type_name,
                       mt.code AS maintenance_type_code
                FROM {$this->tableName()} wo
                LEFT JOIN {$this->prefix}assets a ON wo.asset_id = a.id
                LEFT JOIN {$this->prefix}maintenance_types mt ON wo.maintenance_type_id = mt.id
                WHERE wo.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['status'])) {
            $sql .= " AND wo.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $sql .= " AND wo.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['asset_id'])) {
            $sql .= " AND wo.asset_id = ?";
            $params[] = (int) $filters['asset_id'];
        }

        if (!empty($filters['maintenance_type_id'])) {
            $sql .= " AND wo.maintenance_type_id = ?";
            $params[] = (int) $filters['maintenance_type_id'];
        }

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (wo.wo_number LIKE ? OR wo.title LIKE ? OR a.name LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY 
                    FIELD(wo.status, 'in_progress', 'open', 'completed', 'cancelled'),
                    FIELD(wo.priority, 'urgent', 'high', 'normal', 'low'),
                    wo.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAllWithDetails(): array
    {
        return $this->search();
    }

    /**
     * دریافت یک دستورکار با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT wo.*, 
                       a.name AS asset_name, 
                       a.asset_code,
                       a.criticality AS asset_criticality,
                       a.location_id,
                       l.name AS location_name,
                       mt.name AS maintenance_type_name,
                       mt.code AS maintenance_type_code
                FROM {$this->tableName()} wo
                LEFT JOIN {$this->prefix}assets a ON wo.asset_id = a.id
                LEFT JOIN {$this->prefix}locations l ON a.location_id = l.id
                LEFT JOIN {$this->prefix}maintenance_types mt ON wo.maintenance_type_id = mt.id
                WHERE wo.id = ? AND wo.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * آخرین دستورکارها
     */
    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT wo.*, a.name AS asset_name, a.asset_code
                FROM {$this->tableName()} wo
                LEFT JOIN {$this->prefix}assets a ON wo.asset_id = a.id
                WHERE wo.system_id = ?
                ORDER BY wo.created_at DESC
                LIMIT " . (int) $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * توزیع وضعیت
     */
    public function getStatusDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT status, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ?
             GROUP BY status"
        );
        $stmt->execute([$this->systemId]);

        $result = ['open' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * توزیع اولویت
     */
    public function getPriorityDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT priority, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ?
             GROUP BY priority"
        );
        $stmt->execute([$this->systemId]);

        $result = ['low' => 0, 'normal' => 0, 'high' => 0, 'urgent' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['priority']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * تولید شماره دستورکار یکتا
     * فرمت: WO-{YYYYMMDD}-{counter}
     */
    public function generateWoNumber(): string
    {
        $date = date('Ymd');
        $prefix = "WO-{$date}-";

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->tableName()} 
             WHERE system_id = ? AND wo_number LIKE ?"
        );
        $stmt->execute([$this->systemId, $prefix . '%']);
        $count = (int) $stmt->fetchColumn() + 1;

        return $prefix . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * تغییر وضعیت
     */
    public function changeStatus(int $id, string $newStatus, ?string $resolution = null): bool
    {
        $workOrder = $this->find($id);
        if (!$workOrder) {
            return false;
        }

        $data = ['status' => $newStatus];

        if ($newStatus === 'in_progress' && empty($workOrder['started_at'])) {
            $data['started_at'] = date('Y-m-d H:i:s');
        }

        if ($newStatus === 'completed' && empty($workOrder['completed_at'])) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        if ($resolution !== null) {
            $data['resolution'] = $resolution;
        }

        return $this->update($id, $data);
    }

    /**
     * محاسبه MTTR
     */
    public function calculateMTTR(): ?float
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) / 3600 AS mttr
                FROM {$this->tableName()}
                WHERE system_id = ?
                  AND status = 'completed'
                  AND started_at IS NOT NULL
                  AND completed_at IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['mttr'] !== null ? round((float) $result['mttr'], 2) : null;
    }

    /**
     * محاسبه MTBF
     */
    public function calculateMTBF(): ?float
    {
        $sql = "SELECT 
                    wo.asset_id,
                    wo.completed_at,
                    LAG(wo.completed_at) OVER (PARTITION BY wo.asset_id ORDER BY wo.completed_at) AS prev_completed
                FROM {$this->tableName()} wo
                WHERE wo.system_id = ?
                  AND wo.status = 'completed'
                  AND wo.completed_at IS NOT NULL
                ORDER BY wo.asset_id, wo.completed_at";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->systemId]);

        $intervals = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (!empty($row['prev_completed'])) {
                $diff = strtotime($row['completed_at']) - strtotime($row['prev_completed']);
                if ($diff > 0) {
                    $intervals[] = $diff / 3600;
                }
            }
        }

        if (empty($intervals)) {
            return null;
        }

        return round(array_sum($intervals) / count($intervals), 2);
    }

    /**
     * واحدهای اولویت
     */
    public static function getPriorityOptions(): array
    {
        return [
            'low'    => 'پایین',
            'normal' => 'عادی',
            'high'   => 'بالا',
            'urgent' => 'فوری',
        ];
    }

    /**
     * واحدهای وضعیت
     */
    public static function getStatusOptions(): array
    {
        return [
            'open'        => 'باز',
            'in_progress' => 'در حال انجام',
            'completed'   => 'تکمیل شده',
            'cancelled'   => 'لغو شده',
        ];
    }
}