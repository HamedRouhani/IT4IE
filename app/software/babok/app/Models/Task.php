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

    /**
     * جستجوی معنایی (Full-Text Search) در وظایف و تکنیک‌های BABOK
     * 
     * @param string $query عبارت جستجو به زبان طبیعی
     * @return array نتایج جستجو
     */
    public function semanticSearch(string $query): array
    {
        $db = \App\Core\Database::getInstance()->getConnection(); // تنظیم بر اساس کلاس دیتابیس شما
        
        // پاکسازی ورودی برای جلوگیری از خطای سینتکس MySQL در AGAINST
        $cleanQuery = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $query);
        
        // جستجو در وظایف
        $sql = "SELECT id, code, name, description, knowledge_area_id, 
                MATCH(name, description, inputs, outputs) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance_score
                FROM babok_tasks 
                WHERE MATCH(name, description, inputs, outputs) AGAINST(:query IN NATURAL LANGUAGE MODE)
                ORDER BY relevance_score DESC
                LIMIT 5";
                
        $stmt = $db->prepare($sql);
        $stmt->execute(['query' => $cleanQuery]);
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // جستجو در تکنیک‌ها
        $sqlTech = "SELECT id, name, description, category,
                    MATCH(name, description, purpose) AGAINST(:query IN NATURAL LANGUAGE MODE) as relevance_score
                    FROM babok_techniques 
                    WHERE MATCH(name, description, purpose) AGAINST(:query IN NATURAL LANGUAGE MODE)
                    ORDER BY relevance_score DESC
                    LIMIT 5";
                    
        $stmtTech = $db->prepare($sqlTech);
        $stmtTech->execute(['query' => $cleanQuery]);
        $techniques = $stmtTech->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'tasks' => $tasks,
            'techniques' => $techniques
        ];
    }
}