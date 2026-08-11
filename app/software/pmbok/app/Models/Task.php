<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class Task extends Model
{
    protected $table = 'tasks';
    
    public function getByKnowledgeArea($kaId)
    {
        $table = $this->getTableName();
        $sql = "SELECT t.*, ka.name as ka_name, ka.code as ka_code
                FROM {$table} t
                JOIN {$this->tablePrefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE t.knowledge_area_id = ?
                ORDER BY t.code ASC";
        return $this->query($sql, [$kaId]);
    }
    
    public function getWithStats($kaId = 0, $search = '')
    {
        $tTable = $this->getTableName();
        $kaTable = $this->tablePrefix . 'knowledge_areas';
        $ttTable = $this->tablePrefix . 'task_techniques';
        
        $sql = "SELECT t.*, ka.name as ka_name, ka.code as ka_code,
                       (SELECT COUNT(*) FROM {$ttTable} WHERE task_id = t.id) as technique_count
                FROM {$tTable} t
                JOIN {$kaTable} ka ON t.knowledge_area_id = ka.id
                WHERE 1=1";
        $params = [];
        
        if ($kaId > 0) {
            $sql .= " AND t.knowledge_area_id = ?";
            $params[] = $kaId;
        }
        if (!empty($search)) {
            $sql .= " AND (t.name LIKE ? OR t.description LIKE ? OR t.code LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " ORDER BY t.code";
        return $this->query($sql, $params);
    }
    
    public function getByProject($projectId)
    {
        $tTable = $this->getTableName();
        $ptTable = $this->tablePrefix . 'project_tasks';
        $sql = "SELECT t.*, pt.status, pt.started_at, pt.completed_at, pt.notes as project_notes
                FROM {$tTable} t
                INNER JOIN {$ptTable} pt ON t.id = pt.task_id
                WHERE pt.project_id = ?
                ORDER BY t.code ASC";
        return $this->query($sql, [$projectId]);
    }
}