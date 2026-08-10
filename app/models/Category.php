<?php
namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    
    public function getAllActive()
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function getBySlug($slug)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_active = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $slug]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function getTree()
    {
        $categories = $this->getAllActive();
        return $this->buildTree($categories);
    }
    
    private function buildTree($categories, $parentId = null)
    {
        $tree = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }
        return $tree;
    }

    /**
     * یافتن دسته‌بندی بر اساس اسلاگ
     */
    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ? LIMIT 1";
        return $this->queryOne($sql, [$slug]);
    }
}