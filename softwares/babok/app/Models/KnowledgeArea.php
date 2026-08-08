<?php
namespace App\Models;

use App\Core\Model;

class KnowledgeArea extends Model
{
    protected $table = 'babok_knowledge_areas';

    public function getTasks()
    {
        $sql = "SELECT * FROM tasks WHERE knowledge_area_id = ?";
        return $this->query($sql, [$this->id]);
    }

    public function getTasksWithTechniques($id)
    {
        $sql = "SELECT 
                    t.*,
                    GROUP_CONCAT(tc.name SEPARATOR ', ') as techniques
                FROM tasks t
                LEFT JOIN task_techniques tt ON t.id = tt.task_id
                LEFT JOIN techniques tc ON tt.technique_id = tc.id
                WHERE t.knowledge_area_id = ?
                GROUP BY t.id
                ORDER BY t.code";
        return $this->query($sql, [$id]);
    }

    public function getAllWithCount()
    {
        $sql = "SELECT 
                    ka.*,
                    COUNT(t.id) as task_count
                FROM knowledge_areas ka
                LEFT JOIN tasks t ON ka.id = t.knowledge_area_id
                GROUP BY ka.id
                ORDER BY ka.code";
        return $this->query($sql);
    }
}