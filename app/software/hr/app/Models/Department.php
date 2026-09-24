<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Department Model - مدل دپارتمان (درختی)
 * ============================================================
 * مسیر: app/software/hr/app/Models/Department.php
 * 
 * ساختار درختی: سازمان → معاونت → مدیریت → دپارتمان
 * ============================================================
 */
class Department extends BaseModel
{
    protected $table = 'departments';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'manager_id',
        'cost_center',
        'budget',
        'location',
        'description',
        'status',
        'sort_order',
    ];

    /**
     * دریافت همه دپارتمان‌ها به صورت درختی
     */
    public function getTree(): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, 
                    CONCAT(e.first_name, ' ', e.last_name) AS manager_name,
                    e.employee_code AS manager_code
             FROM {$this->tableName()} d
             LEFT JOIN {$this->prefix}employees e ON d.manager_id = e.id
             WHERE d.system_id = ?
             ORDER BY d.sort_order ASC, d.name ASC"
        );
        $stmt->execute([$this->systemId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->buildTree($rows, null);
    }

    /**
     * ساخت درخت بازگشتی
     */
    private function buildTree(array $rows, ?int $parentId): array
    {
        $branch = [];
        foreach ($rows as $row) {
            $rowParent = $row['parent_id'] !== null ? (int) $row['parent_id'] : null;
            if ($rowParent === $parentId) {
                $children = $this->buildTree($rows, (int) $row['id']);
                if (!empty($children)) {
                    $row['children'] = $children;
                }
                $branch[] = $row;
            }
        }
        return $branch;
    }

    /**
     * دریافت لیست تخت برای Select Box
     */
    public function getFlatList(): array
    {
        $tree = $this->getTree();
        $flat = [];
        $this->flattenTree($tree, $flat, 0);
        return $flat;
    }

    private function flattenTree(array $tree, array &$flat, int $level): void
    {
        foreach ($tree as $node) {
            $node['level'] = $level;
            $children = $node['children'] ?? [];
            unset($node['children']);
            $flat[] = $node;
            if (!empty($children)) {
                $this->flattenTree($children, $flat, $level + 1);
            }
        }
    }

    /**
     * دریافت دپارتمان‌های والد (برای Select در فرم)
     */
    public function getParentOptions(?int $exceptId = null): array
    {
        $flat = $this->getFlatList();
        if ($exceptId === null) {
            return $flat;
        }
        // حذف خود و زیرشاخه‌هایش
        return $this->filterDescendants($flat, $exceptId);
    }

    /**
     * حذف خود و فرزندان از لیست
     */
    private function filterDescendants(array $flat, int $exceptId): array
    {
        $excluded = [$exceptId];

        // پیدا کردن همه فرزندان
        $found = true;
        while ($found) {
            $found = false;
            foreach ($flat as $item) {
                if (in_array((int) $item['parent_id'], $excluded) 
                    && !in_array((int) $item['id'], $excluded)) {
                    $excluded[] = (int) $item['id'];
                    $found = true;
                }
            }
        }

        return array_filter($flat, function ($item) use ($excluded) {
            return !in_array((int) $item['id'], $excluded);
        });
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
     * بررسی فرزند داشتن
     */
    public function hasChildren(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->tableName()} 
             WHERE parent_id = ? AND system_id = ? LIMIT 1"
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
             WHERE department_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی استفاده در پست‌ها
     */
    public function isUsedInPositions(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}positions 
             WHERE department_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * دریافت لیست ساده برای Select Box
     */
    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, code, name 
             FROM {$this->tableName()} 
             WHERE system_id = ? AND status = 'active'
             ORDER BY name ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار دپارتمان‌ها
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(COALESCE(budget, 0)) AS total_budget
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'        => (int) ($row['total'] ?? 0),
            'active'       => (int) ($row['active'] ?? 0),
            'total_budget' => (float) ($row['total_budget'] ?? 0),
        ];
    }
}