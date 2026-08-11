<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class KnowledgeArea extends Model
{
    protected $table = 'knowledge_areas';
    
    public function getWithTaskCount()
    {
        $table = $this->getTableName();
        $tasksTable = $this->tablePrefix . 'tasks';
        $sql = "SELECT ka.*, COUNT(t.id) as task_count
                FROM {$table} ka
                LEFT JOIN {$tasksTable} t ON ka.id = t.knowledge_area_id
                GROUP BY ka.id
                ORDER BY ka.id ASC";
        return $this->query($sql);
    }
    
    public function getWithStats()
    {
        $kaTable = $this->getTableName();
        $tasksTable = $this->tablePrefix . 'tasks';
        $ttTable = $this->tablePrefix . 'task_techniques';
        $sql = "SELECT ka.*, 
                       COUNT(DISTINCT t.id) as task_count,
                       COUNT(DISTINCT tt.technique_id) as technique_count
                FROM {$kaTable} ka 
                LEFT JOIN {$tasksTable} t ON ka.id = t.knowledge_area_id
                LEFT JOIN {$ttTable} tt ON t.id = tt.task_id
                GROUP BY ka.id
                ORDER BY ka.id";
        return $this->query($sql);
    }
}