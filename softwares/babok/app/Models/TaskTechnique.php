<?php
namespace App\Models;

use App\Core\Model;

class TaskTechnique extends Model
{
    protected $table = 'task_techniques';

    public function getTechniquesByTask($taskId)
    {
        $sql = "SELECT 
                    t.*
                FROM techniques t
                JOIN task_techniques tt ON t.id = tt.technique_id
                WHERE tt.task_id = ?
                ORDER BY t.name";
        return $this->query($sql, [$taskId]);
    }

    public function getTasksByTechnique($techniqueId)
    {
        $sql = "SELECT 
                    t.*
                FROM tasks t
                JOIN task_techniques tt ON t.id = tt.task_id
                WHERE tt.technique_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$techniqueId]);
    }

    public function addRelation($taskId, $techniqueId)
    {
        $checkSql = "SELECT COUNT(*) as count FROM task_techniques WHERE task_id = ? AND technique_id = ?";
        $check = $this->queryOne($checkSql, [$taskId, $techniqueId]);
        if ($check['count'] > 0) {
            return false;
        }

        $sql = "INSERT INTO task_techniques (task_id, technique_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $techniqueId]);
    }

    public function removeRelation($taskId, $techniqueId)
    {
        $sql = "DELETE FROM task_techniques WHERE task_id = ? AND technique_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $techniqueId]);
    }

    public function exists($taskId, $techniqueId)
    {
        $sql = "SELECT COUNT(*) as count FROM task_techniques WHERE task_id = ? AND technique_id = ?";
        $result = $this->queryOne($sql, [$taskId, $techniqueId]);
        return $result['count'] > 0;
    }
}