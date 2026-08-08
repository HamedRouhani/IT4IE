<?php
namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    
    public function getPublished()
    {
        $sql = "SELECT p.*, 
                    c.name as category_name, 
                    c.slug as category_slug,
                    u.name as author_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published' 
                AND p.published_at <= NOW()
                ORDER BY p.published_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getBySlug($slug)
    {
        $sql = "SELECT p.*, 
                       c.name as category_name, 
                       c.slug as category_slug,
                       u.name as author_name
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.slug = :slug 
                AND p.status = 'published' 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }

    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
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