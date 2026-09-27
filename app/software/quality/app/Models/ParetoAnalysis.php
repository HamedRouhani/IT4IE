<?php
namespace App\Software\Quality\Models;

use App\Core\Model;

class ParetoAnalysis extends Model
{
    protected $table = 'qc_pareto_analyses';

    /**
     * دریافت تحلیل‌های یک سیستم
     */
    public function findBySystem($systemId, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE system_id = :system_id 
                ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        return $this->query($sql, ['system_id' => $systemId]);
    }

    /**
     * دریافت تحلیل‌های یک پروژه
     */
    public function findByProject($projectId)
    {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE project_id = :project_id 
             ORDER BY created_at DESC",
            ['project_id' => $projectId]
        );
    }

    /**
     * شمارش تحلیل‌های یک سیستم
     */
    public function countBySystem($systemId)
    {
        return $this->count('system_id = :system_id', ['system_id' => $systemId]);
    }

    /**
     * شمارش بر اساس وضعیت
     */
    public function countByStatus($systemId, $status)
    {
        return $this->count(
            'system_id = :system_id AND status = :status',
            ['system_id' => $systemId, 'status' => $status]
        );
    }

    /**
     * آمار کلی برای داشبورد
     */
    public function getStatsBySystem($systemId)
    {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft,
                    SUM(CASE WHEN status = 'analyzed' THEN 1 ELSE 0 END) AS analyzed,
                    SUM(CASE WHEN status = 'action_taken' THEN 1 ELSE 0 END) AS action_taken,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed,
                    SUM(total_value) AS total_value
                FROM {$this->table} 
                WHERE system_id = :system_id";
        
        $result = $this->queryOne($sql, ['system_id' => $systemId]);
        
        return $result ?: [
            'total' => 0, 'draft' => 0, 'analyzed' => 0,
            'action_taken' => 0, 'closed' => 0, 'total_value' => 0
        ];
    }

    /**
     * حذف با cascade به items
     */
    public function deleteWithItems($id)
    {
        $this->beginTransaction();
        try {
            // حذف items
            $this->query(
                "DELETE FROM qc_pareto_items WHERE analysis_id = :id",
                ['id' => $id]
            );
            // حذف analysis
            $this->delete($id);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }
}