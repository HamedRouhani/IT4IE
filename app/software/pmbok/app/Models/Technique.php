<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class Technique extends Model
{
    protected $table = 'techniques';
    
    public function getWithTaskCount($category = '', $kaId = 0, $search = '')
    {
        $teTable = $this->getTableName();
        $ttTable = $this->tablePrefix . 'task_techniques';
        $tTable = $this->tablePrefix . 'tasks';
        $kaTable = $this->tablePrefix . 'knowledge_areas';
        
        $sql = "SELECT te.*, 
                       COUNT(DISTINCT tt.task_id) as task_count,
                       GROUP_CONCAT(DISTINCT ka.name SEPARATOR '، ') as ka_names
                FROM {$teTable} te
                LEFT JOIN {$ttTable} tt ON te.id = tt.technique_id
                LEFT JOIN {$tTable} t ON tt.task_id = t.id
                LEFT JOIN {$kaTable} ka ON t.knowledge_area_id = ka.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($category)) {
            $sql .= " AND te.category = ?";
            $params[] = $category;
        }
        if ($kaId > 0) {
            $sql .= " AND t.knowledge_area_id = ?";
            $params[] = $kaId;
        }
        if (!empty($search)) {
            $sql .= " AND (te.name LIKE ? OR te.description LIKE ? OR te.purpose LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " GROUP BY te.id ORDER BY te.name";
        return $this->query($sql, $params);
    }
    
    public function getCategories()
    {
        $table = $this->getTableName();
        $sql = "SELECT DISTINCT category FROM {$table} WHERE category IS NOT NULL AND category != '' ORDER BY category";
        return $this->query($sql);
    }
}