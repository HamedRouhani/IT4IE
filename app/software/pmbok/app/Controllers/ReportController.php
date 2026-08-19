<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class ReportController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * لیست پروژه‌ها برای انتخاب گزارش
     */
    public function index()
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.phase, p.methodology, p.created_at,
                   (SELECT COUNT(*) FROM pmbok_project_tasks WHERE project_id = p.id) as task_count
            FROM pmbok_projects p
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $projects = $stmt->fetchAll();

        $this->view('report/index', [
            'pageTitle' => 'گزارش‌های هوشمند - PMBOK Analyzer',
            'currentPage' => 'report',
            'projects' => $projects
        ]);
    }

    /**
     * گزارش کامل پروژه (HTML قابل چاپ)
     */
    public function projectReport($id)
    {
        $this->requireAuth();

        // ۱. دریافت اطلاعات پروژه
        $stmt = $this->db->prepare("SELECT * FROM pmbok_projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $project = $stmt->fetch();

        if (!$project) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=report');
            return;
        }

        // ۲. دریافت وظایف (با اصلاح ستون‌های code و ka_code)
        $stmt = $this->db->prepare("
            SELECT 
                pt.*, 
                t.code, 
                t.name as task_name, 
                ka.code as ka_code, 
                ka.name as ka_name
            FROM pmbok_project_tasks pt
            JOIN pmbok_tasks t ON pt.task_id = t.id
            JOIN pmbok_knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE pt.project_id = ? AND pt.user_id = ?
            ORDER BY t.code
        ");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $tasks = $stmt->fetchAll();

        // ۳. دریافت ریسک‌ها
        $stmt = $this->db->prepare("SELECT * FROM pmbok_risks WHERE project_id = ? AND user_id = ? ORDER BY risk_score DESC");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $risks = $stmt->fetchAll();

        // ۴. دریافت تحویل‌دادنی‌ها
        $stmt = $this->db->prepare("SELECT * FROM pmbok_deliverables WHERE project_id = ? AND user_id = ? ORDER BY planned_date ASC");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $deliverables = $stmt->fetchAll();

        // ۵. دریافت ذی‌نفعان
        $stmt = $this->db->prepare("SELECT * FROM pmbok_project_stakeholders WHERE project_id = ? AND user_id = ? ORDER BY influence DESC");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $stakeholders = $stmt->fetchAll();

        // ۶. محاسبه شاخص‌های پیشرفت
        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));
        $inProgressTasks = count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress'));
        $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        // ۷. ارسال داده‌ها به ویو
        $this->view('report/project_report', [
            'pageTitle' => 'گزارش پروژه: ' . $project['name'],
            'currentPage' => 'report',
            'project' => $project,
            'tasks' => $tasks,
            'risks' => $risks,
            'deliverables' => $deliverables,
            'stakeholders' => $stakeholders,
            'progress' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'in_progress' => $inProgressTasks,
                'percent' => $progressPercent
            ]
        ]);
    }

    /**
     * 🌟 خروجی CSV سازگار با Primavera P6 (نام متد دقیقاً منطبق با URL)
     */
    public function exportPrimavera($id = null)
    {
        $this->requireAuth();
        $project = $this->getUserProject($id);
        
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=report');
            return;
        }

        $stmt = $this->db->prepare("
            SELECT pt.id, t.code, t.name as task_name, pt.status, pt.started_at, pt.completed_at, ka.name as ka_name
            FROM pmbok_project_tasks pt
            JOIN pmbok_tasks t ON pt.task_id = t.id
            JOIN pmbok_knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE pt.project_id = ? AND pt.user_id = ?
            ORDER BY t.code
        ");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $tasks = $stmt->fetchAll();

        // تنظیم هدرهای دانلود فایل
        $filename = "P6_Export_Project_{$project['id']}_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // افزودن BOM برای نمایش صحیح حروف فارسی در اکسل و P6
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // سرستون‌های استاندارد P6
        fputcsv($output, [
            'Project ID', 'Project Name', 'WBS Code', 'Activity ID', 'Activity Name',
            'Original Duration', 'Start Date', 'Finish Date', 'Percent Complete', 'Status', 'Knowledge Area'
        ], ',');

        $wbsCounter = 1;
        foreach ($tasks as $task) {
            // محاسبه مدت زمان (پیش‌فرض ۵ روز در صورت نبود تاریخ)
            $duration = 5;
            if (!empty($task['started_at']) && !empty($task['completed_at'])) {
                $start = new \DateTime($task['started_at']);
                $end = new \DateTime($task['completed_at']);
                $duration = max(1, $start->diff($end)->days + 1);
            }

            $percentComplete = ($task['status'] === 'completed') ? 100 : (($task['status'] === 'in_progress') ? 50 : 0);
            $p6Status = ($task['status'] === 'completed') ? 'Completed' : (($task['status'] === 'in_progress') ? 'In Progress' : 'Not Started');

            fputcsv($output, [
                $project['id'],
                $project['name'],
                'WBS-' . str_pad($wbsCounter++, 3, '0', STR_PAD_LEFT),
                'ACT-' . $task['id'],
                $task['task_name'],
                $duration,
                $task['started_at'] ? date('Y-m-d', strtotime($task['started_at'])) : '',
                $task['completed_at'] ? date('Y-m-d', strtotime($task['completed_at'])) : '',
                $percentComplete,
                $p6Status,
                $task['ka_name'] ?? ''
            ], ',');
        }

        fclose($output);
        exit; // ⚠️ بسیار حیاتی: توقف اجرای اسکریپت پس از ارسال فایل
    }

    /**
     * 🌟 خروجی XML سازگار با Microsoft Project
     */
    public function exportMSProject($id = null)
    {
        $this->requireAuth();
        $project = $this->getUserProject($id);
        
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=report');
            return;
        }

        $stmt = $this->db->prepare("
            SELECT pt.id, t.code, t.name as task_name, pt.status, pt.started_at, pt.completed_at
            FROM pmbok_project_tasks pt
            JOIN pmbok_tasks t ON pt.task_id = t.id
            WHERE pt.project_id = ? AND pt.user_id = ?
            ORDER BY t.code
        ");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $tasks = $stmt->fetchAll();

        $filename = "MSProject_Project_{$project['id']}_" . date('Ymd') . ".xml";
        header('Content-Type: application/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        echo '<Project xmlns="http://schemas.microsoft.com/project">' . "\n";
        echo '  <Name>' . htmlspecialchars($project['name']) . '</Name>' . "\n";
        echo '  <Title>' . htmlspecialchars($project['name']) . '</Title>' . "\n";
        echo '  <CreationDate>' . date('Y-m-d\TH:i:s') . '</CreationDate>' . "\n";
        echo '  <Tasks>' . "\n";
        
        $uid = 1;
        foreach ($tasks as $task) {
            $percentComplete = ($task['status'] === 'completed') ? 100 : (($task['status'] === 'in_progress') ? 50 : 0);
            $durationHours = 40; // پیش‌فرض ۵ روز کاری × ۸ ساعت
            
            $startDate = !empty($task['started_at']) ? date('Y-m-d\TH:i:s', strtotime($task['started_at'])) : date('Y-m-d\TH:i:s');
            $finishDate = !empty($task['completed_at']) ? date('Y-m-d\TH:i:s', strtotime($task['completed_at'])) : date('Y-m-d\TH:i:s', strtotime('+5 days'));

            echo '    <Task>' . "\n";
            echo '      <UID>' . $uid . '</UID>' . "\n";
            echo '      <ID>' . $uid . '</ID>' . "\n";
            echo '      <Name>' . htmlspecialchars($task['code'] . ' - ' . $task['task_name']) . '</Name>' . "\n";
            echo '      <Duration>PT' . $durationHours . 'H0M0S</Duration>' . "\n";
            echo '      <Start>' . $startDate . '</Start>' . "\n";
            echo '      <Finish>' . $finishDate . '</Finish>' . "\n";
            echo '      <PercentComplete>' . $percentComplete . '</PercentComplete>' . "\n";
            echo '      <Priority>500</Priority>' . "\n";
            echo '      <Active>1</Active>' . "\n";
            echo '    </Task>' . "\n";
            $uid++;
        }
        
        echo '  </Tasks>' . "\n";
        echo '</Project>';
        exit; // ⚠️ بسیار حیاتی: توقف اجرای اسکریپت پس از ارسال فایل
    }

    /**
     * متد کمکی برای بررسی مالکیت پروژه
     */
    private function getUserProject($id)
    {
        if (!$id) return null;
        
        $stmt = $this->db->prepare("SELECT * FROM pmbok_projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        return $stmt->fetch();
    }
}