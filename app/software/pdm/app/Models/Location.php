<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * Location Model - مدل مکان (درخت)
 * ============================================================
 * مسیر: app/software/pdm/app/Models/Location.php
 * 
 * ساختار درختی: کارخانه → سالن → خط تولید
 * ============================================================
 */
class Location extends BaseModel
{
    protected $table = 'locations';

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'description',
    ];

    /**
     * دریافت همه مکان‌ها به صورت درختی
     */
    public function getTree(): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE system_id = ? 
             ORDER BY name ASC"
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
     * دریافت همه مکان‌ها به صورت لیست تخت (برای Select Box)
     */
    public function getFlatList(): array
    {
        $tree = $this->getTree();
        $flat = [];
        $this->flattenTree($tree, $flat, 0);
        return $flat;
    }

    /**
     * تبدیل درخت به لیست تخت با indent
     */
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
     * دریافت مکان‌های والد (برای Select در فرم)
     */
    public function getParentOptions(?int $exceptId = null): array
    {
        $flat = $this->getFlatList();
        if ($exceptId === null) {
            return $flat;
        }
        // حذف خود و زیرشاخه‌هایش
        return array_filter($flat, function ($item) use ($exceptId) {
            return (int) $item['id'] !== $exceptId;
        });
    }

    /**
     * بررسی وجود مکان
     */
    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->tableName()} WHERE id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی تکراری بودن کد مکان
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
     * بررسی اینکه آیا مکان فرزند دارد؟
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
     * بررسی اینکه آیا مکان در دارایی‌ها استفاده شده؟
     */
    public function isUsedInAssets(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}assets 
             WHERE location_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }
}