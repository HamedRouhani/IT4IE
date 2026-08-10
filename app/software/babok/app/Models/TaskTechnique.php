<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل رابطه وظیفه-تکنیک
 * جدول: babok_task_techniques
 * ⚠️ این جدول کلید اصلی مرکب دارد (task_id, technique_id) و ستون id ندارد
 */
class TaskTechnique extends Model
{
    protected $table = 'task_techniques';
    protected $primaryKey = 'task_id'; // فقط برای جلوگیری از خطا، استفاده نمی‌شود

    /**
     * دریافت تکنیک‌های یک وظیفه
     */
    public function getTechniquesByTask($taskId)
    {
        $sql = "SELECT t.*
                FROM babok_techniques t
                JOIN babok_task_techniques tt ON t.id = tt.technique_id
                WHERE tt.task_id = ?
                ORDER BY t.name";
        return $this->query($sql, [$taskId]);
    }

    /**
     * دریافت وظایفی که از یک تکنیک استفاده می‌کنند
     */
    public function getTasksByTechnique($techniqueId)
    {
        $sql = "SELECT t.*
                FROM babok_tasks t
                JOIN babok_task_techniques tt ON t.id = tt.task_id
                WHERE tt.technique_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$techniqueId]);
    }

    /**
     * افزودن رابطه وظیفه-تکنیک
     */
    public function addRelation($taskId, $techniqueId)
    {
        // بررسی تکراری نبودن
        $checkSql = "SELECT COUNT(*) as count FROM babok_task_techniques WHERE task_id = ? AND technique_id = ?";
        $check = $this->queryOne($checkSql, [$taskId, $techniqueId]);
        
        if ($check['count'] > 0) {
            return false;
        }
        
        $sql = "INSERT INTO babok_task_techniques (task_id, technique_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $techniqueId]);
    }

    /**
     * حذف رابطه وظیفه-تکنیک
     */
    public function removeRelation($taskId, $techniqueId)
    {
        $sql = "DELETE FROM babok_task_techniques WHERE task_id = ? AND technique_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$taskId, $techniqueId]);
    }

    /**
     * بررسی وجود رابطه
     */
    public function exists($taskId, $techniqueId)
    {
        $sql = "SELECT COUNT(*) as count FROM babok_task_techniques WHERE task_id = ? AND technique_id = ?";
        $result = $this->queryOne($sql, [$taskId, $techniqueId]);
        return $result['count'] > 0;
    }
}