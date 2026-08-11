<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class Project extends Model
{
    protected $table = 'projects';
    
    public function getRecent($limit = 5)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT {$limit}";
        return $this->query($sql);
    }
    
    public function getActive()
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE phase != 'closure' ORDER BY created_at DESC";
        return $this->query($sql);
    }
    
    public function getByPhase($phase)
    {
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE phase = ?";
        return $this->query($sql, [$phase]);
    }
    
    public function getWithStats($id)
    {
        $pTable = $this->getTableName();
        $dTable = $this->tablePrefix . 'deliverables';
        $rTable = $this->tablePrefix . 'risks';
        $psTable = $this->tablePrefix . 'project_stakeholders';
        $ptTable = $this->tablePrefix . 'project_tasks';
        
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM {$dTable} WHERE project_id = p.id) as deliverable_count,
                       (SELECT COUNT(*) FROM {$rTable} WHERE project_id = p.id) as risk_count,
                       (SELECT COUNT(*) FROM {$psTable} WHERE project_id = p.id) as stakeholder_count,
                       (SELECT COUNT(*) FROM {$ptTable} WHERE project_id = p.id) as task_count,
                       (SELECT COUNT(*) FROM {$ptTable} WHERE project_id = p.id AND status = 'completed') as completed_tasks
                FROM {$pTable} p
                WHERE p.id = ?";
        return $this->queryOne($sql, [$id]);
    }
    
    public function search($keyword, $phase = '', $methodology = '')
    {
        $table = $this->getTableName();
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM {$this->tablePrefix}deliverables WHERE project_id = p.id) as deliverable_count,
                       (SELECT COUNT(*) FROM {$this->tablePrefix}risks WHERE project_id = p.id) as risk_count,
                       (SELECT COUNT(*) FROM {$this->tablePrefix}project_stakeholders WHERE project_id = p.id) as stakeholder_count_actual,
                       (SELECT COUNT(*) FROM {$this->tablePrefix}project_tasks WHERE project_id = p.id) as task_count
                FROM {$table} p WHERE 1=1";
        $params = [];
        
        if (!empty($phase)) {
            $sql .= " AND p.phase = ?";
            $params[] = $phase;
        }
        if (!empty($methodology)) {
            $sql .= " AND p.methodology = ?";
            $params[] = $methodology;
        }
        if (!empty($keyword)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$keyword}%";
            $params[] = "%{$keyword}%";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        return $this->query($sql, $params);
    }
}