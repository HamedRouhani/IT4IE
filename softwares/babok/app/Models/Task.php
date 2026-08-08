<?php
namespace App\Models;

use App\Core\Model;

class Task extends Model
{
    protected $table = 'babok_tasks';

    public function getByKnowledgeArea($knowledgeAreaId)
    {
        $sql = "SELECT * FROM tasks WHERE knowledge_area_id = ? ORDER BY code";
        return $this->query($sql, [$knowledgeAreaId]);
    }

    public function getAllWithKnowledgeArea()
    {
        $sql = "SELECT 
                    t.*,
                    ka.name as knowledge_area_name,
                    ka.code as knowledge_area_code
                FROM tasks t
                JOIN knowledge_areas ka ON t.knowledge_area_id = ka.id
                ORDER BY t.code";
        return $this->query($sql);
    }

    public function getWithTechniques($taskId)
    {
        $sql = "SELECT 
                    t.*,
                    GROUP_CONCAT(tc.name SEPARATOR ', ') as techniques,
                    GROUP_CONCAT(tc.id SEPARATOR ',') as technique_ids
                FROM tasks t
                LEFT JOIN task_techniques tt ON t.id = tt.task_id
                LEFT JOIN techniques tc ON tt.technique_id = tc.id
                WHERE t.id = ?
                GROUP BY t.id";
        return $this->queryOne($sql, [$taskId]);
    }

    public function getTechniques($taskId)
    {
        $sql = "SELECT 
                    tc.*
                FROM techniques tc
                JOIN task_techniques tt ON tc.id = tt.technique_id
                WHERE tt.task_id = ?
                ORDER BY tc.name";
        return $this->query($sql, [$taskId]);
    }

    public function getByProject($projectId)
    {
        $sql = "SELECT 
                    t.*,
                    pt.status as project_status,
                    pt.started_at,
                    pt.completed_at,
                    pt.notes
                FROM tasks t
                JOIN project_tasks pt ON t.id = pt.task_id
                WHERE pt.project_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$projectId]);
    }

    public function getRecommended($methodology, $phase)
    {
        $sql = "SELECT * FROM tasks ORDER BY code";
        return $this->query($sql);
    }
}