<?php

namespace App\Software\Babok\Services;

use App\Software\Babok\Core\Model;

/**
 * سرویس مدیریت اعلان‌ها و یادآوری‌های هوشمند
 */
class NotificationService extends Model
{
    protected $table = 'notifications';
    private $userId;

    public function __construct()
    {
        parent::__construct(); // فراخوانی constructor کلاس والد
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    /**
     * 🌟 تولید خودکار اعلان‌های هوشمند برای کاربر
     * با کش 5 دقیقه‌ای برای جلوگیری از اجرای مکرر
     */
    public function generateSmartNotifications()
    {
        if (!$this->userId) return;

        // بررسی کش: اگر در 5 دقیقه اخیر اجرا شده، دوباره اجرا نکن
        $lastRunKey = 'babok_notif_last_run_' . $this->userId;
        $lastRun = $_SESSION[$lastRunKey] ?? 0;
        
        if ((time() - $lastRun) < 300) { // 300 ثانیه = 5 دقیقه
            return;
        }

        // به‌روزرسانی زمان آخرین اجرا
        $_SESSION[$lastRunKey] = time();

        // اجرای بررسی‌ها
        $this->checkStaleTasks();
        $this->checkLowQualityRequirements();
        $this->checkPhaseReminders();
        $this->checkTraceabilitySuggestions();
    }

    /**
     * بررسی وظایف قدیمی در وضعیت in_progress
     */
    private function checkStaleTasks()
    {
        $sql = "SELECT pt.id as project_task_id, pt.project_id, pt.started_at, 
                       p.name as project_name, t.name as task_name
                FROM babok_project_tasks pt
                JOIN babok_projects p ON pt.project_id = p.id
                JOIN babok_tasks t ON pt.task_id = t.id
                WHERE p.user_id = ? 
                AND pt.status = 'in_progress'
                AND pt.started_at IS NOT NULL
                AND DATEDIFF(NOW(), pt.started_at) > 7
                AND NOT EXISTS (
                    SELECT 1 FROM babok_notifications n 
                    WHERE n.user_id = ? 
                    AND n.project_id = p.id 
                    AND n.type = 'reminder'
                    AND n.title LIKE '%وظیفه قدیمی%'
                    AND n.is_read = 0
                    AND n.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId, $this->userId]);
        $staleTasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($staleTasks as $task) {
            $days = floor((time() - strtotime($task['started_at'])) / 86400);
            $this->create([
                'user_id' => $this->userId,
                'project_id' => $task['project_id'],
                'type' => 'reminder',
                'priority' => $days > 14 ? 'high' : 'normal',
                'title' => "وظیفه قدیمی در پروژه «{$task['project_name']}»",
                'message' => "وظیفه «{$task['task_name']}» بیش از {$days} روز است که در حال انجام است. لطفاً وضعیت آن را بررسی کنید.",
                'link' => "?route=projects_view&id={$task['project_id']}"
            ]);
        }
    }

    /**
     * بررسی نیازمندی‌های با کیفیت پایین
     */
    private function checkLowQualityRequirements()
    {
        $sql = "SELECT pt.project_id, p.name as project_name, 
                       COUNT(*) as low_quality_count,
                       ROUND(AVG(pt.quality_score), 1) as avg_low_quality
                FROM babok_project_tasks pt
                JOIN babok_projects p ON pt.project_id = p.id
                WHERE p.user_id = ? 
                AND pt.quality_score > 0 
                AND pt.quality_score < 60
                GROUP BY pt.project_id, p.name
                HAVING low_quality_count >= 2
                AND NOT EXISTS (
                    SELECT 1 FROM babok_notifications n 
                    WHERE n.user_id = ? 
                    AND n.project_id = pt.project_id 
                    AND n.type = 'quality'
                    AND n.is_read = 0
                    AND n.created_at > DATE_SUB(NOW(), INTERVAL 3 DAY)
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId, $this->userId]);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($projects as $project) {
            $this->create([
                'user_id' => $this->userId,
                'project_id' => $project['project_id'],
                'type' => 'quality',
                'priority' => 'high',
                'title' => "هشدار کیفیت در پروژه «{$project['project_name']}»",
                'message' => "{$project['low_quality_count']} نیازمندی با میانگین امتیاز {$project['avg_low_quality']} نیاز به بازنگری دارند.",
                'link' => "?route=projects_view&id={$project['project_id']}"
            ]);
        }
    }

    /**
     * یادآوری بر اساس فاز پروژه (با جلوگیری قوی از تکرار)
     */
    private function checkPhaseReminders()
    {
        $sql = "SELECT id, name, phase, methodology, updated_at
                FROM babok_projects 
                WHERE user_id = ? 
                AND updated_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId]);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $phaseReminders = [
            'initiation' => [
                'title' => 'پروژه در فاز آغازین است',
                'message' => 'پیشنهاد می‌شود وظایف حوزه دانشی KA1 (برنامه‌ریزی تحلیل کسب‌وکار) را تکمیل کنید.',
                'priority' => 'normal'
            ],
            'planning' => [
                'title' => 'پروژه در فاز برنامه‌ریزی است',
                'message' => 'زمان مناسبی برای استفاده از تکنیک‌های مدل‌سازی و مستندسازی است.',
                'priority' => 'normal'
            ],
            'analysis' => [
                'title' => 'پروژه در فاز تحلیل است',
                'message' => 'تمرکز بر تکنیک‌های تحقیقاتی و مدل‌سازی را فراموش نکنید.',
                'priority' => 'normal'
            ],
            'evaluation' => [
                'title' => 'پروژه در فاز ارزیابی است',
                'message' => 'زمان مناسبی برای تولید گزارش نهایی و مستندسازی درس‌آموخته‌هاست.',
                'priority' => 'high'
            ]
        ];

        foreach ($projects as $project) {
            if (!isset($phaseReminders[$project['phase']])) continue;

            $reminder = $phaseReminders[$project['phase']];

            // 🌟 بررسی قوی‌تر: فقط یک اعلان reminder برای این پروژه در 7 روز اخیر
            // بدون وابستگی به title (که ممکن است با نام پروژه تغییر کند)
            $checkSql = "SELECT COUNT(*) FROM babok_notifications 
                         WHERE user_id = ? 
                         AND project_id = ? 
                         AND type = 'reminder'
                         AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$this->userId, $project['id']]);
            
            // اگر حتی یک اعلان reminder برای این پروژه در 7 روز اخیر وجود دارد، ادامه نده
            if ((int)$checkStmt->fetchColumn() > 0) continue;

            $this->create([
                'user_id' => $this->userId,
                'project_id' => $project['id'],
                'type' => 'reminder',
                'priority' => $reminder['priority'],
                'title' => $reminder['title'] . " - «{$project['name']}»",
                'message' => $reminder['message'],
                'link' => "?route=projects_view&id={$project['id']}"
            ]);
        }
    }

    /**
     * بررسی پیشنهادات ردیابی جدید
     */
    private function checkTraceabilitySuggestions()
    {
        $recommendationService = new RecommendationService();

        $sql = "SELECT id, name FROM babok_projects WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId]);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($projects as $project) {
            $suggestions = $recommendationService->getTraceabilitySuggestions($project['id']);
            
            if (count($suggestions) >= 3) {
                $checkSql = "SELECT COUNT(*) FROM babok_notifications 
                             WHERE user_id = ? 
                             AND project_id = ? 
                             AND type = 'traceability'
                             AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute([$this->userId, $project['id']]);
                
                if ($checkStmt->fetchColumn() > 0) continue;

                $this->create([
                    'user_id' => $this->userId,
                    'project_id' => $project['id'],
                    'type' => 'traceability',
                    'priority' => 'normal',
                    'title' => "پیشنهادات ردیابی جدید برای «{$project['name']}»",
                    'message' => count($suggestions) . " ارتباط جدید بین وظایف پروژه شناسایی شده است.",
                    'link' => "?route=projects_view&id={$project['id']}"
                ]);
            }
        }
    }

    /**
     * ایجاد اعلان جدید
     */
    public function create($data)
    {
        $sql = "INSERT INTO babok_notifications 
                (user_id, project_id, type, title, message, link, priority) 
                VALUES (:user_id, :project_id, :type, :title, :message, :link, :priority)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':project_id' => $data['project_id'] ?? null,
            ':type' => $data['type'],
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':link' => $data['link'] ?? null,
            ':priority' => $data['priority'] ?? 'normal'
        ]);
    }

    /**
     * دریافت اعلان‌های کاربر (برای bell icon)
     */
    public function getRecentNotifications($limit = 5)
    {
        $sql = "SELECT * FROM babok_notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId, (int)$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * تعداد اعلان‌های خوانده‌نشده
     */
    public function getUnreadCount()
    {
        $sql = "SELECT COUNT(*) FROM babok_notifications 
                WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * علامت‌گذاری اعلان به عنوان خوانده‌شده
     */
    public function markAsRead($id)
    {
        $sql = "UPDATE babok_notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $this->userId]);
    }

    /**
     * علامت‌گذاری همه به عنوان خوانده‌شده
     */
    public function markAllAsRead()
    {
        $sql = "UPDATE babok_notifications 
                SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$this->userId]);
    }

    /**
     * حذف یک اعلان
     */
    public function delete($id)
    {
        $sql = "DELETE FROM babok_notifications WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id, $this->userId]);
    }

    /**
     * دریافت تمام اعلان‌های کاربر (برای صفحه مدیریت)
     */
    public function getAllForUser($filter = 'all', $limit = 50)
    {
        $sql = "SELECT n.*, p.name as project_name 
                FROM babok_notifications n
                LEFT JOIN babok_projects p ON n.project_id = p.id
                WHERE n.user_id = ?";
        
        $params = [$this->userId];
        
        if ($filter === 'unread') {
            $sql .= " AND n.is_read = 0";
        } elseif ($filter !== 'all' && in_array($filter, ['system', 'reminder', 'quality', 'traceability', 'recommendation'])) {
            $sql .= " AND n.type = ?";
            $params[] = $filter;
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT ?";
        $params[] = (int)$limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}