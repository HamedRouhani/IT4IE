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
        public function addTask($projectId, $taskId, $userId) // 🌟 اضافه شدن $userId
        {
            // بررسی تکراری نبودن
            $checkSql = "SELECT COUNT(*) as count FROM babok_project_tasks WHERE project_id = ? AND task_id = ?";
            $check = $this->queryOne($checkSql, [$projectId, $taskId]);
            
            if ($check['count'] > 0) {
                return false;
            }
            
            // 🌟 اضافه شدن user_id به کوئری INSERT
            $sql = "INSERT INTO babok_project_tasks (user_id, project_id, task_id, status) VALUES (?, ?, ?, 'not_started')";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId, $projectId, $taskId]);
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

    /**
     * به‌روزرسانی یادداشت و امتیاز کیفیت یک وظیفه پروژه
     */
    public function updateQuality($id, $notes, $qualityScore, $status = null)
    {
        $sql = "UPDATE {$this->table} SET notes = :notes, quality_score = :quality_score";
        $params = [
            ':notes' => $notes,
            ':quality_score' => $qualityScore,
            ':id' => $id
        ];

        if ($status !== null) {
            $sql .= ", status = :status";
            $params[':status'] = $status;
        }

        $sql .= " WHERE id = :id";
        
        return $this->db->execute($sql, $params); // یا $this->query بسته به Core Model شما
    }

    /**
     * به‌روزرسانی یادداشت و امتیاز کیفیت یک وظیفه پروژه
     */
    public function updateTaskQuality($id, $notes, $qualityScore, $status = null)
    {
        $sql = "UPDATE babok_project_tasks SET notes = :notes, quality_score = :quality_score";
        $params = [
            ':notes' => $notes,
            ':quality_score' => $qualityScore,
            ':id' => $id
        ];

        if ($status !== null) {
            $sql .= ", status = :status";
            $params[':status'] = $status;
        }

        $sql .= " WHERE id = :id";
        
        // فرض بر این است که کلاس Model پایه شما متد execute یا query دارد
        // اگر از PDO مستقیم در کنترلر استفاده می‌کنید، این منطق را آنجا پیاده کنید
        $stmt = $this->db->prepare($sql); 
        return $stmt->execute($params);
    }

    /**
     * دریافت آمار کیفیت پروژه (برای داشبورد)
     */
    public function getQualityStats($projectId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_tasks,
                    ROUND(AVG(quality_score), 1) as avg_score,
                    SUM(CASE WHEN quality_score >= 80 THEN 1 ELSE 0 END) as excellent_count,
                    SUM(CASE WHEN quality_score BETWEEN 60 AND 79 THEN 1 ELSE 0 END) as good_count,
                    SUM(CASE WHEN quality_score < 60 AND quality_score > 0 THEN 1 ELSE 0 END) as needs_improvement_count
                FROM babok_project_tasks 
                WHERE project_id = :project_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':project_id' => $projectId]);
        return $stmt->fetch();
    }

    /**
     * 🌟 پیشنهاد هوشمند ردیابی (Traceability) بین وظایف پروژه
     * بر اساس تطابق خروجی (Output) یک وظیفه با ورودی (Input) وظیفه دیگر
     */
    public function getTraceabilitySuggestions($projectId)
    {
        // ۱. دریافت تمام وظایف استاندارد BABOK برای دسترسی به inputs و outputs
        $stmt = $this->db->prepare("SELECT id, name, inputs, outputs FROM babok_tasks");
        $stmt->execute();
        $allStandardTasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // تبدیل به آرایه انجمنی برای جستجوی سریع با task_id
        $taskDictionary = [];
        foreach ($allStandardTasks as $t) {
            $taskDictionary[$t['id']] = $t;
        }

        // ۲. دریافت وظایف اختصاص داده شده به این پروژه به همراه وضعیت
        $stmt = $this->db->prepare("
            SELECT pt.id as project_task_id, pt.task_id, pt.status, t.name as task_name
            FROM babok_project_tasks pt
            JOIN babok_tasks t ON pt.task_id = t.id
            WHERE pt.project_id = ?
        ");
        $stmt->execute([$projectId]);
        $projectTasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $suggestions = [];

        // ۳. جداسازی وظایف به دو دسته: منبع (تکمیل شده/در حال انجام) و هدف (شروع نشده)
        $sourceTasks = array_filter($projectTasks, fn($pt) => in_array($pt['status'], ['completed', 'in_progress']));
        $targetTasks = array_filter($projectTasks, fn($pt) => $pt['status'] === 'not_started');

        // ۴. الگوریتم تطابق هوشمند
        foreach ($sourceTasks as $source) {
            $stdSource = $taskDictionary[$source['task_id']] ?? null;
            if (!$stdSource || empty($stdSource['outputs'])) continue;

            // تبدیل خروجی‌ها به آرایه کلمات کلیدی (جدا شده با کاما یا فاصله)
            $outputs = preg_split('/[،,\s]+/u', $stdSource['outputs']);

            foreach ($targetTasks as $target) {
                $stdTarget = $taskDictionary[$target['task_id']] ?? null;
                if (!$stdTarget || empty($stdTarget['inputs'])) continue;

                $inputs = preg_split('/[،,\s]+/u', $stdTarget['inputs']);
                
                // یافتن اشتراک بین ورودی‌ها و خروجی‌ها
                $matches = array_intersect(array_map('trim', $outputs), array_map('trim', $inputs));
                
                // حذف مقادیر خالی یا تک‌حرفی
                $matches = array_filter($matches, fn($val) => mb_strlen(trim($val)) > 2);

                if (!empty($matches)) {
                    $suggestions[] = [
                        'source_task_name' => $source['task_name'],
                        'target_task_name' => $target['task_name'],
                        'shared_artifacts' => implode('، ', array_unique($matches)),
                        'recommendation' => "خروجی «{$source['task_name']}» می‌تواند مستقیماً به عنوان ورودی «{$target['task_name']}» استفاده شود."
                    ];
                }
            }
        }

        return $suggestions;
    }
    
    /**
     * 🌟 دریافت آمار تحلیلی پیشرفته برای داشبورد
     * شامل توزیع کیفیت، پیشرفت بر اساس حوزه دانشی، و KPIs
     */
    public function getAdvancedAnalytics($projectId)
    {
        // ۱. آمار کلی کیفیت
        $qualityStats = $this->getQualityStats($projectId);
        
        // ۲. توزیع وضعیت وظایف
        $statusSql = "SELECT status, COUNT(*) as count 
                      FROM babok_project_tasks 
                      WHERE project_id = ?
                      GROUP BY status";
        $stmt = $this->db->prepare($statusSql);
        $stmt->execute([$projectId]);
        $statusDistribution = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // ۳. پیشرفت بر اساس حوزه دانشی
        $kaProgressSql = "SELECT 
                            ka.id,
                            ka.code,
                            ka.name,
                            COUNT(pt.id) as total_tasks,
                            SUM(CASE WHEN pt.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                            ROUND(AVG(pt.quality_score), 1) as avg_quality
                          FROM babok_project_tasks pt
                          JOIN babok_tasks t ON pt.task_id = t.id
                          JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
                          WHERE pt.project_id = ?
                          GROUP BY ka.id, ka.code, ka.name
                          ORDER BY ka.code";
        $stmt = $this->db->prepare($kaProgressSql);
        $stmt->execute([$projectId]);
        $knowledgeAreaProgress = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // ۴. محاسبه KPIs
        $totalTasks = array_sum(array_column($statusDistribution, 'count'));
        $completedTasks = 0;
        foreach ($statusDistribution as $s) {
            if ($s['status'] === 'completed') {
                $completedTasks = $s['count'];
                break;
            }
        }
        
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
        $avgQuality = $qualityStats['avg_score'] ?? 0;
        
        // ۵. شاخص سلامت پروژه (Project Health Index)
        // ترکیبی از نرخ تکمیل، میانگین کیفیت، و پوشش حوزه‌های دانشی
        $kaCoverage = count($knowledgeAreaProgress) / 6 * 100; // 6 حوزه دانشی در BABOK
        $healthIndex = round(($completionRate * 0.4) + ($avgQuality * 0.4) + ($kaCoverage * 0.2), 1);
        
        return [
            'quality_stats' => $qualityStats,
            'status_distribution' => $statusDistribution,
            'knowledge_area_progress' => $knowledgeAreaProgress,
            'kpis' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $completionRate,
                'avg_quality' => $avgQuality,
                'ka_coverage' => round($kaCoverage, 1),
                'health_index' => min(100, $healthIndex) // حداکثر ۱۰۰
            ]
        ];
    }
}