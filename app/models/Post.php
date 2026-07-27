<?php
namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    
    public function getPublished()
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE status = 'published' ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            // Return empty array if table doesn't exist yet
            return [];
        }
    }
    
    public function getBySlug($slug)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND status = 'published' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['slug' => $slug]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function incrementView($postId)
    {
        try {
            $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $postId]);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getByCategory($categoryId, $limit = 10)
    {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE category_id = :category_id 
                    AND status = 'published' 
                    ORDER BY published_at DESC 
                    LIMIT " . (int)$limit;
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['category_id' => $categoryId]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}