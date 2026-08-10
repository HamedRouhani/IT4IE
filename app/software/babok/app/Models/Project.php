<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل پروژه‌های BABOK
 * جدول: babok_projects
 */
class Project extends Model
{
    protected $table = 'projects';

    /**
     * دریافت وظایف یک پروژه به همراه اطلاعات کامل
     */
    public function getTasks($projectId)
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
     * دریافت وضعیت پیشرفت یک پروژه
     */
    public function getProgress($projectId)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'not_started' THEN 1 ELSE 0 END) as not_started,
                    SUM(CASE WHEN status = 'deferred' THEN 1 ELSE 0 END) as deferred
                FROM babok_project_tasks
                WHERE project_id = ?";
        
        $result = $this->queryOne($sql, [$projectId]);
        
        if ($result && $result['total'] > 0) {
            $result['completion_percentage'] = round(($result['completed'] / $result['total']) * 100, 2);
        } else {
            $result['completion_percentage'] = 0;
        }
        
        return $result;
    }

    /**
     * دریافت پروژه‌های فعال (غیر از فاز ارزیابی)
     */
    public function getActiveProjects()
    {
        $sql = "SELECT * FROM babok_projects WHERE phase != 'evaluation' ORDER BY created_at DESC";
        return $this->query($sql);
    }

    /**
     * دریافت آخرین پروژه‌ها
     */
    public function getLatest($limit = 5)
    {
        $sql = "SELECT * FROM babok_projects ORDER BY created_at DESC LIMIT ?";
        return $this->query($sql, [(int)$limit]);
    }

    /**
     * دریافت پروژه بر اساس نام (برای جلوگیری از تکرار)
     */
    public function getByName($name)
    {
        $sql = "SELECT * FROM babok_projects WHERE name = ? LIMIT 1";
        return $this->queryOne($sql, [$name]);
    }
}