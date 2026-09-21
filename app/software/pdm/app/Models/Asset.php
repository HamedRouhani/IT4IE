<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * Asset Model - مدل دارایی (تجهیزات)
 * ============================================================
 * مسیر: app/software/pdm/app/Models/Asset.php
 * ============================================================
 */
class Asset extends BaseModel
{
    protected $table = 'assets';

    protected $fillable = [
        'location_id',
        'category_id',
        'parent_asset_id',
        'asset_code',
        'name',
        'manufacturer',
        'model',
        'serial_number',
        'installation_date',
        'criticality',
        'status',
        'description',
    ];

    /**
     * دریافت دارایی‌ها با اطلاعات مکان و دسته
     * با پشتیبانی از فیلتر و جستجو
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT a.*, 
                       l.name AS location_name, 
                       c.name AS category_name,
                       p.name AS parent_name
                FROM {$this->tableName()} a
                LEFT JOIN {$this->prefix}locations l ON a.location_id = l.id
                LEFT JOIN {$this->prefix}asset_categories c ON a.category_id = c.id
                LEFT JOIN {$this->tableName()} p ON a.parent_asset_id = p.id
                WHERE a.system_id = ?";
        $params = [$this->systemId];

        // فیلتر وضعیت
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        // فیلتر بحرانیت
        if (!empty($filters['criticality'])) {
            $sql .= " AND a.criticality = ?";
            $params[] = $filters['criticality'];
        }

        // فیلتر مکان
        if (!empty($filters['location_id'])) {
            $sql .= " AND a.location_id = ?";
            $params[] = (int) $filters['location_id'];
        }

        // فیلتر دسته
        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = ?";
            $params[] = (int) $filters['category_id'];
        }

        // جستجو در نام/کد/سریال
        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (a.name LIKE ? OR a.asset_code LIKE ? OR a.serial_number LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY 
                    FIELD(a.criticality, 'critical', 'high', 'medium', 'low'),
                    a.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت همه دارایی‌ها (بدون فیلتر)
     */
    public function getAllWithDetails(): array
    {
        return $this->search();
    }

    /**
     * دریافت یک دارایی با جزئیات کامل
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT a.*, 
                       l.name AS location_name, 
                       c.name AS category_name,
                       p.name AS parent_name
                FROM {$this->tableName()} a
                LEFT JOIN {$this->prefix}locations l ON a.location_id = l.id
                LEFT JOIN {$this->prefix}asset_categories c ON a.category_id = c.id
                LEFT JOIN {$this->tableName()} p ON a.parent_asset_id = p.id
                WHERE a.id = ? AND a.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * توزیع بحرانیت دارایی‌ها
     */
    public function getCriticalityDistribution(): array
    {
        $stmt = $this->db->prepare(
            "SELECT criticality, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = ?
             GROUP BY criticality"
        );
        $stmt->execute([$this->systemId]);

        $result = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['criticality']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * توزیع وضعیت دارایی‌ها
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

        $result = ['active' => 0, 'inactive' => 0, 'maintenance' => 0, 'retired' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['count'];
        }
        return $result;
    }

    /**
     * بررسی تکراری بودن کد دارایی
     */
    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) {
            return false;
        }
        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND asset_code = ?";
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
     * بررسی استفاده در دستورکارها
     */
    public function isUsedInWorkOrders(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}work_orders 
             WHERE asset_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * دریافت دارایی‌های بحرانی
     */
    public function getCriticalAssets(int $limit = 10): array
    {
        return $this->where(
            "criticality IN ('critical', 'high') AND status = 'active'",
            [],
            "FIELD(criticality, 'critical', 'high'), name ASC",
            $limit
        );
    }

    /**
     * لیست دارایی‌ها برای Select Box
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, asset_code, name 
             FROM {$this->tableName()} 
             WHERE system_id = ? 
             ORDER BY name ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * شمارش بر اساس بحرانیت
     */
    public function countByCriticality(string $criticality): int
    {
        return $this->count("criticality = ?", [$criticality]);
    }

    /**
     * شمارش بر اساس وضعیت
     */
    public function countByStatus(string $status): int
    {
        return $this->count("status = ?", [$status]);
    }
}