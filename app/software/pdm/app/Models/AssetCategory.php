<?php
namespace App\Software\Pdm\Models;

/**
 * ============================================================
 * AssetCategory Model - مدل دسته‌بندی دارایی
 * ============================================================
 * مسیر: app/software/pdm/app/Models/AssetCategory.php
 * 
 * ساختار درختی: پمپ‌ها → پمپ سانتریفیوژ → پمپ افقی
 * ============================================================
 */
class AssetCategory extends BaseModel
{
    protected $table = 'asset_categories';

    protected $fillable = [
        'parent_id',
        'name',
        'description',
    ];

    /**
     * دریافت همه دسته‌بندی‌ها به صورت درختی
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
     * بررسی وجود دسته
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
     * بررسی استفاده در دارایی‌ها
     */
    public function isUsedInAssets(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM {$this->prefix}assets 
             WHERE category_id = ? AND system_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $this->systemId]);
        return (bool) $stmt->fetchColumn();
    }
}