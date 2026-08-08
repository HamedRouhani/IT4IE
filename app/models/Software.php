<?php
namespace App\Models;

use App\Core\Model;

class Software extends Model
{
    protected $table = 'software';
    protected $primaryKey = 'id';
    
    /**
     * Get all active software
     */
    public function getActiveSoftware()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get software by slug
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE slug = :slug 
                AND is_active = 1 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch();
    }
    
    /**
     * Get total downloads count
     */
    public function getTotalDownloads()
    {
        $sql = "SELECT SUM(download_count) as total FROM {$this->table} WHERE is_active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
    
    /**
     * Increment download count
     */
    public function incrementDownload($id)
    {
        $sql = "UPDATE {$this->table} SET download_count = download_count + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}