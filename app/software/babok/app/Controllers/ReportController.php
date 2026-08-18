<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Project;
use App\Software\Babok\Models\ProjectTask;
use App\Software\Babok\Services\RecommendationService;

/**
 * کنترلر گزارش‌گیری هوشمند پروژه‌های BABOK
 */
class ReportController extends Controller
{
    private $projectModel;
    private $projectTaskModel;
    private $recommendationService;
    private $db;
    private $userId;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->projectTaskModel = new ProjectTask();
        $this->recommendationService = new RecommendationService();
        $this->db = \App\Core\Database::getInstance();
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    /**
     * صفحه اصلی گزارش‌ها - لیست پروژه‌ها برای انتخاب
     */
    public function index()
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM babok_project_tasks WHERE project_id = p.id) as task_count
            FROM babok_projects p
            WHERE p.user_id = ?
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute([$this->userId]);
        $projects = $stmt->fetchAll();

        $this->view('reports/index', [
            'title' => 'گزارش‌های هوشمند - BABOK Analyzer',
            'activePage' => 'reports',
            'projects' => $projects
        ]);
    }

    /**
     * 📊 گزارش کامل پروژه (HTML قابل پرینت)
     */
    public function projectReport($id)
    {
        $this->requireAuth();

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('reports');
            return;
        }

        // جمع‌آوری تمام داده‌ها
        $tasks = $this->projectModel->getTasks($id);
        $progress = $this->projectModel->getProgress($id);
        $qualityStats = $this->projectTaskModel->getQualityStats($id);
        $advancedAnalytics = $this->projectTaskModel->getAdvancedAnalytics($id);
        $traceabilitySuggestions = $this->recommendationService->getTraceabilitySuggestions($id);
        $smartTechniques = $this->recommendationService->getSmartRecommendations($id);

        // رندر ویو گزارش (بدون سایدبار و هدر اصلی - مخصوص پرینت)
        $this->view('reports/project_report', [
            'title' => 'گزارش پروژه: ' . $project['name'],
            'project' => $project,
            'tasks' => $tasks,
            'progress' => $progress,
            'qualityStats' => $qualityStats,
            'analytics' => $advancedAnalytics,
            'traceability' => $traceabilitySuggestions,
            'techniques' => $smartTechniques,
            'generatedAt' => date('Y-m-d H:i:s'),
            'generatedBy' => $_SESSION['user_name'] ?? 'کاربر سیستم'
        ]);
    }

    /**
     * 📥 خروجی CSV وظایف پروژه (سازگار با Excel فارسی)
     */
    public function exportTasksCsv($id)
    {
        $this->requireAuth();

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('reports');
            return;
        }

        $tasks = $this->projectModel->getTasks($id);

        // هدرهای HTTP برای دانلود CSV با encoding فارسی
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="report-' . $project['id'] . '-' . date('Y-m-d') . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM برای سازگاری با Excel فارسی
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // سرستون‌ها
        fputcsv($output, [
            'کد وظیفه',
            'نام وظیفه',
            'حوزه دانشی',
            'وضعیت',
            'یادداشت‌ها',
            'امتیاز کیفیت',
            'تاریخ شروع',
            'تاریخ تکمیل'
        ], ',');

        // داده‌ها
        foreach ($tasks as $task) {
            fputcsv($output, [
                $task['task_code'] ?? '',
                $task['task_name'] ?? '',
                $task['knowledge_area_name'] ?? '',
                \App\Software\Babok\Helpers\Utils::statusLabel($task['status'] ?? ''),
                $task['notes'] ?? '',
                $task['quality_score'] ?? 0,
                $task['started_at'] ?? '',
                $task['completed_at'] ?? ''
            ], ',');
        }

        fclose($output);
        exit;
    }

    /**
     * 📥 خروجی CSV پیشنهادات ردیابی
     */
    public function exportTraceabilityCsv($id)
    {
        $this->requireAuth();

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('reports');
            return;
        }

        $suggestions = $this->recommendationService->getTraceabilitySuggestions($id);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="traceability-' . $project['id'] . '-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['وظیفه مبدأ', 'وظیفه هدف', 'مستندات مشترک', 'توضیح پیشنهاد'], ',');

        foreach ($suggestions as $s) {
            fputcsv($output, [
                $s['source_task_name'] ?? '',
                $s['target_task_name'] ?? '',
                $s['shared_artifacts'] ?? '',
                $s['recommendation'] ?? ''
            ], ',');
        }

        fclose($output);
        exit;
    }

    /**
     * 🔒 دریافت پروژه فقط اگر متعلق به کاربر فعلی باشد
     */
    private function getUserProject($id)
    {
        if (!$this->userId) return false;

        $stmt = $this->db->prepare("
            SELECT * FROM babok_projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch();
    }
}