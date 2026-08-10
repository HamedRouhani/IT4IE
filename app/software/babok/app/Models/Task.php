<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل وظایف BABOK
 * جدول: babok_tasks (۲۹ رکورد با کدهای 3.1 تا 8.5)
 */
class Task extends Model
{
    protected $table = 'tasks';

    /**
     * دریافت وظایف یک حوزه دانشی
     */
    public function getByKnowledgeArea($knowledgeAreaId)
    {
        $sql = "SELECT * FROM babok_tasks WHERE knowledge_area_id = ? ORDER BY code";
        return $this->query($sql, [$knowledgeAreaId]);
    }

    /**
     * دریافت تمام وظایف به همراه اطلاعات حوزه دانشی
     */
    public function getAllWithKnowledgeArea()
    {
        $sql = "SELECT 
                    t.*,
                    ka.name as knowledge_area_name,
                    ka.code as knowledge_area_code
                FROM babok_tasks t
                JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
                ORDER BY t.code";
        return $this->query($sql);
    }

    /**
     * دریافت یک وظیفه به همراه تکنیک‌های آن
     */
    public function getWithTechniques($taskId)
    {
        $sql = "SELECT 
                    t.*,
                    GROUP_CONCAT(tc.name SEPARATOR ', ') as techniques,
                    GROUP_CONCAT(tc.id SEPARATOR ',') as technique_ids
                FROM babok_tasks t
                LEFT JOIN babok_task_techniques tt ON t.id = tt.task_id
                LEFT JOIN babok_techniques tc ON tt.technique_id = tc.id
                WHERE t.id = ?
                GROUP BY t.id";
        return $this->queryOne($sql, [$taskId]);
    }

    /**
     * دریافت تکنیک‌های یک وظیفه
     */
    public function getTechniques($taskId)
    {
        $sql = "SELECT tc.*
                FROM babok_techniques tc
                JOIN babok_task_techniques tt ON tc.id = tt.technique_id
                WHERE tt.task_id = ?
                ORDER BY tc.name";
        return $this->query($sql, [$taskId]);
    }

    /**
     * دریافت وظایف یک پروژه به همراه وضعیت
     */
    public function getByProject($projectId)
    {
        $sql = "SELECT 
                    t.*,
                    pt.status as project_status,
                    pt.started_at,
                    pt.completed_at,
                    pt.notes
                FROM babok_tasks t
                JOIN babok_project_tasks pt ON t.id = pt.task_id
                WHERE pt.project_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$projectId]);
    }

    /**
     * دریافت وظایف پیشنهادی بر اساس متدولوژی و فاز
     * در نسخه فعلی تمام وظایف برگردانده می‌شود
     */
    public function getRecommended($methodology, $phase)
    {
        $sql = "SELECT * FROM babok_tasks ORDER BY code";
        return $this->query($sql);
    }

    /**
     * دریافت وظیفه بر اساس کد (مثل 3.1 یا 5.2)
     */
    public function getByCode($code)
    {
        $sql = "SELECT * FROM babok_tasks WHERE code = ? LIMIT 1";
        return $this->queryOne($sql, [$code]);
    }
}