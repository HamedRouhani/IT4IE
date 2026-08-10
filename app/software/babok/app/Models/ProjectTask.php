<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل رابطه پروژه-وظیفه
 * جدول: babok_project_tasks
 */
class ProjectTask extends Model
{
    protected $table = 'project_tasks';

    /**
     * دریافت وظایف یک پروژه به همراه اطلاعات کامل
     */
    public function getByProject($projectId)
    {
        $sql = "SELECT 
                    pt.*,
                    t.name as task_name,
                    t.code as task_code,
                    t.description as task_description,
                    ka.name as knowledge_area_name,
                    ka.code as knowledge_area_code
                FROM babok_project_tasks pt
                JOIN babok_tasks t ON pt.task_id = t.id
                JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE pt.project_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$projectId]);
    }

    /**
     * افزودن وظیفه به پروژه
     */
    public function addTask($projectId, $taskId)
    {
        // بررسی تکراری نبودن
        $checkSql = "SELECT COUNT(*) as count FROM babok_project_tasks WHERE project_id = ? AND task_id = ?";
        $check = $this->queryOne($checkSql, [$projectId, $taskId]);
        
        if ($check['count'] > 0) {
            return false;
        }
        
        $sql = "INSERT INTO babok_project_tasks (project_id, task_id, status) VALUES (?, ?, 'not_started')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $taskId]);
    }

    /**
     * حذف وظیفه از پروژه
     */
    public function removeTask($projectId, $taskId)
    {
        $sql = "DELETE FROM babok_project_tasks WHERE project_id = ? AND task_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$projectId, $taskId]);
    }

    /**
     * به‌روزرسانی وضعیت وظیفه در پروژه
     */
    public function updateStatus($projectId, $taskId, $status)
    {
        $validStatuses = ['not_started', 'in_progress', 'completed', 'deferred'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $sql = "UPDATE babok_project_tasks 
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

    /**
     * دریافت آخرین وظایف تکمیل‌شده یک پروژه
     */
    public function getRecentCompleted($projectId, $limit = 5)
    {
        $sql = "SELECT 
                    pt.*,
                    t.name as task_name,
                    t.code as task_code
                FROM babok_project_tasks pt
                JOIN babok_tasks t ON pt.task_id = t.id
                WHERE pt.project_id = ? AND pt.status = 'completed'
                ORDER BY pt.completed_at DESC
                LIMIT ?";
        return $this->query($sql, [$projectId, (int)$limit]);
    }

    /**
     * بررسی وجود یک وظیفه در پروژه
     */
    public function exists($projectId, $taskId)
    {
        $sql = "SELECT COUNT(*) as count FROM babok_project_tasks WHERE project_id = ? AND task_id = ?";
        $result = $this->queryOne($sql, [$projectId, $taskId]);
        return $result['count'] > 0;
    }
}