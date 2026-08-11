<?php

namespace App\Software\Pmbok\Models;

use App\Software\Pmbok\Core\Model;

class Risk extends Model
{
    protected $table = 'risks';
    
    public function getByProject($projectId)
    {
        $table = $this->getTableName();
        $sql = "SELECT r.*, p.name as project_name 
                FROM {$table} r
                JOIN {$this->tablePrefix}projects p ON r.project_id = p.id
                WHERE r.project_id = ?
                ORDER BY r.risk_score DESC";
        return $this->query($sql, [$projectId]);
    }
    
    public function getHighPriority($limit = 5)
    {
        $table = $this->getTableName();
        $sql = "SELECT r.*, p.name as project_name 
                FROM {$table} r 
                JOIN {$this->tablePrefix}projects p ON r.project_id = p.id 
                WHERE r.risk_score >= 15 
                ORDER BY r.risk_score DESC 
                LIMIT {$limit}";
        return $this->query($sql);
    }
    
    public function search($projectId = 0, $status = '', $probability = '', $search = '')
    {
        $rTable = $this->getTableName();
        $pTable = $this->tablePrefix . 'projects';
        
        $sql = "SELECT r.*, p.name as project_name, p.phase as project_phase
                FROM {$rTable} r
                JOIN {$pTable} p ON r.project_id = p.id
                WHERE 1=1";
        $params = [];
        
        if ($projectId > 0) {
            $sql .= " AND r.project_id = ?";
            $params[] = $projectId;
        }
        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        if (!empty($probability)) {
            $sql .= " AND r.probability = ?";
            $params[] = $probability;
        }
        if (!empty($search)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.owner LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " ORDER BY r.risk_score DESC, r.created_at DESC";
        return $this->query($sql, $params);
    }
    
    public static function calculateRiskScore($probability, $impact)
    {
        $probScores = ['very_low' => 1, 'low' => 2, 'medium' => 3, 'high' => 4, 'very_high' => 5];
        $impactScores = ['very_low' => 1, 'low' => 2, 'medium' => 3, 'high' => 4, 'very_high' => 5];
        return ($probScores[$probability] ?? 3) * ($impactScores[$impact] ?? 3);
    }
}