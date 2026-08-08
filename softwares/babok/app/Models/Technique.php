<?php
namespace App\Models;

use App\Core\Model;

class Technique extends Model
{
    protected $table = 'babok_techniques';

    public function getByCategory($category)
    {
        $sql = "SELECT * FROM techniques WHERE category = ? ORDER BY name";
        return $this->query($sql, [$category]);
    }

    public function getForTask($taskId)
    {
        $sql = "SELECT 
                    t.*
                FROM techniques t
                JOIN task_techniques tt ON t.id = tt.technique_id
                WHERE tt.task_id = ?
                ORDER BY t.name";
        return $this->query($sql, [$taskId]);
    }

    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM techniques ORDER BY category";
        return $this->query($sql);
    }

    public function getAllWithTaskCount()
    {
        $sql = "SELECT 
                    t.*,
                    COUNT(tt.task_id) as task_count
                FROM techniques t
                LEFT JOIN task_techniques tt ON t.id = tt.technique_id
                GROUP BY t.id
                ORDER BY t.name";
        return $this->query($sql);
    }

    public function search($keyword)
    {
        $sql = "SELECT * FROM techniques 
                WHERE name LIKE ? 
                OR description LIKE ? 
                OR purpose LIKE ?
                ORDER BY name";
        $search = "%{$keyword}%";
        return $this->query($sql, [$search, $search, $search]);
    }
}