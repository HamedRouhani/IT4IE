<?php
namespace App\Models;

use App\Core\Model;

class Project extends Model
{
    protected $table = 'babok_projects';

    public function getTasks($projectId)
    {
        $sql = "SELECT 
                    pt.*,
                    t.name as task_name,
                    t.code as task_code,
                    t.description as task_description,
                    ka.name as knowledge_area_name,
                    ka.code as knowledge_area_code
                FROM project_tasks pt
                JOIN tasks t ON pt.task_id = t.id
                JOIN knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE pt.project_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$projectId]);
    }

    public function getProgress($projectId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'not_started' THEN 1 ELSE 0 END) as not_started
                FROM project_tasks
                WHERE project_id = ?";
        $result = $this->queryOne($sql, [$projectId]);
        
        if ($result && $result['total'] > 0) {
            $result['completion_percentage'] = round(($result['completed'] / $result['total']) * 100, 2);
        } else {
            $result['completion_percentage'] = 0;
        }
        
        return $result;
    }

    public function addTask($projectId, $taskId)
    {
        $sql = "INSERT INTO project_tasks (project_id, task_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $taskId]);
    }

    public function updateTaskStatus($projectId, $taskId, $status)
    {
        $sql = "UPDATE project_tasks 
                SET status = ?,
                    started_at = CASE 
                        WHEN ? = 'in_progress' AND started_at IS NULL THEN NOW() 
                        ELSE started_at 
                    END,
                    completed_at = CASE 
                        WHEN ? = 'completed' THEN NOW() 
                        ELSE NULL 
                    END
                WHERE project_id = ? AND task_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$status, $status, $status, $projectId, $taskId]);
    }
}