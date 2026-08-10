<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل حوزه‌های دانشی BABOK
 * جدول: babok_knowledge_areas (۶ رکورد: KA1 تا KA6)
 */
class KnowledgeArea extends Model
{
    protected $table = 'knowledge_areas';

    /**
     * دریافت وظایف یک حوزه دانشی
     */
    public function getTasks($knowledgeAreaId)
    {
        $sql = "SELECT * FROM babok_tasks WHERE knowledge_area_id = ? ORDER BY code";
        return $this->query($sql, [$knowledgeAreaId]);
    }

    /**
     * دریافت وظایف یک حوزه به همراه تکنیک‌های آن‌ها
     */
    public function getTasksWithTechniques($id)
    {
        $sql = "SELECT 
                    t.*,
                    GROUP_CONCAT(tc.name SEPARATOR ', ') as techniques
                FROM babok_tasks t
                LEFT JOIN babok_task_techniques tt ON t.id = tt.task_id
                LEFT JOIN babok_techniques tc ON tt.technique_id = tc.id
                WHERE t.knowledge_area_id = ?
                GROUP BY t.id
                ORDER BY t.code";
        return $this->query($sql, [$id]);
    }

    /**
     * دریافت تمام حوزه‌ها به همراه تعداد وظایف هر کدام
     */
    public function getAllWithCount()
    {
        $sql = "SELECT 
                    ka.*,
                    COUNT(t.id) as task_count
                FROM babok_knowledge_areas ka
                LEFT JOIN babok_tasks t ON ka.id = t.knowledge_area_id
                GROUP BY ka.id
                ORDER BY ka.code";
        return $this->query($sql);
    }

    /**
     * دریافت حوزه بر اساس کد (KA1 تا KA6)
     */
    public function getByCode($code)
    {
        $sql = "SELECT * FROM babok_knowledge_areas WHERE code = ? LIMIT 1";
        return $this->queryOne($sql, [$code]);
    }
}