<?php
namespace App\Models;

use App\Core\Model;

class ProjectTask extends Model
{
    protected $table = 'babok_project_tasks';

    public function getByProject($projectId)
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

    public function addTask($projectId, $taskId)
    {
        $checkSql = "SELECT COUNT(*) as count FROM project_tasks WHERE project_id = ? AND task_id = ?";
        $check = $this->queryOne($checkSql, [$projectId, $taskId]);
        if ($check['count'] > 0) {
            return false;
        }

        $sql = "INSERT INTO project_tasks (project_id, task_id, status) VALUES (?, ?, 'not_started')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $taskId]);
    }

    public function removeTask($projectId, $taskId)
    {
        $sql = "DELETE FROM project_tasks WHERE project_id = ? AND task_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $taskId]);
    }

    public function updateStatus($projectId, $taskId, $status)
    {
        $validStatuses = ['not_started', 'in_progress', 'completed', 'deferred'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

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

    public function getRecentCompleted($projectId, $limit = 5)
    {
        $sql = "SELECT 
                    pt.*,
                    t.name as task_name,
                    t.code as task_code
                FROM project_tasks pt
                JOIN tasks t ON pt.task_id = t.id
                WHERE pt.project_id = ? AND pt.status = 'completed'
                ORDER BY pt.completed_at DESC
                LIMIT ?";
        return $this->query($sql, [$projectId, $limit]);
    }
}