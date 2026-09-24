<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Competency Model - فهرست شایستگی‌ها
 * ============================================================
 * مسیر: app/software/hr/app/Models/Competency.php
 *
 * ستون‌های جدول hr_competencies:
 *   id, system_id, parent_id, code, name, category, competency_type,
 *   description, levels, is_core, status, sort_order,
 *   created_at, updated_at
 * ============================================================
 */
class Competency extends BaseModel
{
    protected $table = 'competencies';

    protected $fillable = [
        'parent_id', 'code', 'name', 'category', 'competency_type',
        'description', 'levels', 'is_core', 'status', 'sort_order',
    ];

    /**
     * دسته‌بندی‌ها
     */
    public static function getCategoryOptions(): array
    {
        return [
            'technical'  => 'فنی',
            'behavioral' => 'رفتاری',
            'managerial' => 'مدیریتی',
            'leadership' => 'رهبری',
            'digital'    => 'دیجیتال',
            'language'   => 'زبان',
            'other'      => 'سایر',
        ];
    }

    /**
     * نوع شایستگی
     */
    public static function getTypeOptions(): array
    {
        return [
            'knowledge' => 'دانش',
            'skill'     => 'مهارت',
            'ability'   => 'توانایی',
            'attitude'  => 'نگرش',
            'other'     => 'سایر',
        ];
    }

    /**
     * وضعیت
     */
    public static function getStatusOptions(): array
    {
        return [
            'active'   => 'فعال',
            'inactive' => 'غیرفعال',
        ];
    }

    /**
     * برچسب‌های کلاس CSS
     */
    public static function getCategoryClass(string $category): string
    {
        $map = [
            'technical'  => 'hr-status-info',
            'behavioral' => 'hr-status-active',
            'managerial' => 'hr-status-warning',
            'leadership' => 'hr-status-danger',
            'digital'    => 'hr-status-info',
            'language'   => 'hr-status-active',
            'other'      => 'hr-status-inactive',
        ];
        return $map[$category] ?? 'hr-status-inactive';
    }

    /**
     * جستجو با فیلترها
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT c.*,
                       p.name AS parent_name
                FROM {$this->tableName()} c
                LEFT JOIN {$this->tableName()} p 
                    ON p.id = c.parent_id AND p.system_id = c.system_id
                WHERE c.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (c.name LIKE :q OR c.code LIKE :q2 OR c.description LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['category'])) {
            $sql .= " AND c.category = :cat";
            $params['cat'] = $filters['category'];
        }
        if (!empty($filters['competency_type'])) {
            $sql .= " AND c.competency_type = :ct";
            $params['ct'] = $filters['competency_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :st";
            $params['st'] = $filters['status'];
        }
        if (isset($filters['is_core']) && $filters['is_core'] !== '') {
            $sql .= " AND c.is_core = :ic";
            $params['ic'] = (int) $filters['is_core'];
        }

        $sql .= " ORDER BY c.sort_order ASC, c.name ASC LIMIT 500";

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
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='inactive' THEN 1 ELSE 0 END) AS inactive,
                    SUM(CASE WHEN is_core=1 THEN 1 ELSE 0 END) AS core_count
                FROM {$this->tableName()}
                WHERE system_id = :sid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'      => (int) ($row['total'] ?? 0),
            'active'     => (int) ($row['active'] ?? 0),
            'inactive'   => (int) ($row['inactive'] ?? 0),
            'core_count' => (int) ($row['core_count'] ?? 0),
        ];
    }

    /**
     * یافتن با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*,
                       p.name AS parent_name
                FROM {$this->tableName()} c
                LEFT JOIN {$this->tableName()} p 
                    ON p.id = c.parent_id AND p.system_id = c.system_id
                WHERE c.id = :id AND c.system_id = :sid LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * زیرشاخه‌ها
     */
    public function getChildren(int $parentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()}
             WHERE parent_id = :pid AND system_id = :sid
             ORDER BY sort_order ASC, name ASC"
        );
        $stmt->execute(['pid' => $parentId, 'sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * لیست برای select (همه شایستگی‌های فعال)
     */
    public function getSelectList(?int $excludeId = null): array
    {
        $sql = "SELECT id, name, code, category FROM {$this->tableName()}
                WHERE system_id = :sid AND status = 'active'";
        $params = ['sid' => $this->systemId];

        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }
        $sql .= " ORDER BY category ASC, sort_order ASC, name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * بررسی وجود کد تکراری
     */
    public function isCodeDuplicate(string $code, ?int $excludeId = null): bool
    {
        if ($code === '') return false;

        $sql = "SELECT COUNT(*) FROM {$this->tableName()}
                WHERE code = :code AND system_id = :sid";
        $params = ['code' => $code, 'sid' => $this->systemId];

        if ($excludeId !== null) {
            $sql .= " AND id != :xid";
            $params['xid'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * شمارش شایستگی‌های یک دسته (برای نمایش درخت)
     */
    public function countByCategory(): array
    {
        $stmt = $this->db->prepare(
            "SELECT category, COUNT(*) AS count
             FROM {$this->tableName()}
             WHERE system_id = :sid AND status = 'active'
             GROUP BY category"
        );
        $stmt->execute(['sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * ساخت درخت شایستگی‌ها (برای نمایش در View)
     */
    public function getTree(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()}
             WHERE system_id = :sid
             ORDER BY sort_order ASC, name ASC"
        );
        $stmt->execute(['sid' => $this->systemId]);
        $all = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // گروه‌بندی بر اساس parent_id
        $byParent = [];
        foreach ($all as $item) {
            $pid = $item['parent_id'] !== null ? (int) $item['parent_id'] : 0;
            $byParent[$pid][] = $item;
        }

        // ساخت بازگشتی
        $build = function ($parentId) use (&$build, $byParent) {
            $result = [];
            if (!empty($byParent[$parentId])) {
                foreach ($byParent[$parentId] as $item) {
                    $item['children'] = $build((int) $item['id']);
                    $result[] = $item;
                }
            }
            return $result;
        };

        return $build(0);
    }
}