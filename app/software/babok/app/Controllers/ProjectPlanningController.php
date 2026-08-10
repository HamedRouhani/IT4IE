<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Project;
use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\ProjectTask;

/**
 * کنترلر برنامه‌ریزی پروژه BABOK
 */
class ProjectPlanningController extends Controller
{
    private $projectModel;
    private $taskModel;
    private $projectTaskModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->taskModel = new Task();
        $this->projectTaskModel = new ProjectTask();
    }

    /**
     * صفحه برنامه‌ریزی پروژه
     */
    public function index($projectId)
    {
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        // دریافت وظایف انتخاب‌شده برای پروژه
        $selectedTasks = $this->projectTaskModel->getByProject($projectId);

        // دریافت تمام وظایف BABOK به تفکیک حوزه
        $allTasks = $this->taskModel->getAllWithKnowledgeArea();

        $this->view('project-planning/index', [
            'title' => 'برنامه‌ریزی پروژه: ' . $project['name'] . ' - BABOK Analyzer',
            'activePage' => 'projects',
            'project' => $project,
            'selectedTasks' => $selectedTasks,
            'allTasks' => $allTasks
        ]);
    }

    /**
     * افزودن وظیفه به پروژه
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function addTask()
    {
        // بررسی احراز هویت
        $this->requireAuth();

        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);

        // اعتبارسنجی
        if (!$projectId || !$taskId) {
            $this->flashError('اطلاعات ناقص است.');
            $this->redirect('planning&id=' . $projectId);
            return;
        }

        // بررسی وجود پروژه
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        // بررسی وجود وظیفه
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            $this->flashError('وظیفه مورد نظر یافت نشد.');
            $this->redirect('planning&id=' . $projectId);
            return;
        }

        // افزودن وظیفه به پروژه
        $result = $this->projectTaskModel->addTask($projectId, $taskId);

        if ($result) {
            // ثبت لاگ فعالیت
            $this->logActivity('add_task_to_project', 'project_task', $projectId, null, [
                'project_id' => $projectId,
                'task_id' => $taskId,
                'task_name' => $task['name']
            ]);

            $this->flashSuccess('وظیفه با موفقیت به پروژه اضافه شد.');
        } else {
            $this->flashError('خطا در افزودن وظیفه. ممکن است قبلاً اضافه شده باشد.');
        }

        $this->redirect('planning&id=' . $projectId);
    }

    /**
     * حذف وظیفه از پروژه
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function removeTask()
    {
        // بررسی احراز هویت
        $this->requireAuth();

        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);

        // اعتبارسنجی
        if (!$projectId || !$taskId) {
            $this->flashError('اطلاعات ناقص است.');
            $this->redirect('projects');
            return;
        }

        // حذف وظیفه از پروژه
        $result = $this->projectTaskModel->removeTask($projectId, $taskId);

        if ($result) {
            // ثبت لاگ فعالیت
            $this->logActivity('remove_task_from_project', 'project_task', $projectId, [
                'project_id' => $projectId,
                'task_id' => $taskId
            ], null);

            $this->flashSuccess('وظیفه با موفقیت از پروژه حذف شد.');
        } else {
            $this->flashError('خطا در حذف وظیفه.');
        }

        $this->redirect('planning&id=' . $projectId);
    }

    /**
     * به‌روزرسانی وضعیت وظیفه (AJAX)
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function updateTaskStatus()
    {
        // بررسی احراز هویت
        if (!isset($_SESSION['user_id'])) {
            return $this->json([
                'error' => 'برای انجام این عملیات لطفاً وارد شوید.',
                'requires_auth' => true,
                'redirect' => '/login'
            ]);
        }

        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.']);
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? 'not_started';

        // اعتبارسنجی وضعیت
        $validStatuses = ['not_started', 'in_progress', 'completed', 'deferred'];
        if (!in_array($status, $validStatuses)) {
            return $this->json(['error' => 'وضعیت نامعتبر است.']);
        }

        // به‌روزرسانی وضعیت
        $result = $this->projectTaskModel->updateStatus($projectId, $taskId, $status);

        if ($result) {
            // ثبت لاگ فعالیت
            $this->logActivity('update_task_status', 'project_task', $projectId, null, [
                'project_id' => $projectId,
                'task_id' => $taskId,
                'status' => $status
            ]);

            return $this->json([
                'success' => true,
                'message' => 'وضعیت با موفقیت به‌روزرسانی شد.'
            ]);
        } else {
            return $this->json(['error' => 'خطا در به‌روزرسانی وضعیت.']);
        }
    }

    /**
     * دریافت وظایف پیشنهادی برای پروژه (AJAX)
     */
    public function getRecommendedTasks($projectId)
    {
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            return $this->json(['error' => 'پروژه مورد نظر یافت نشد.']);
        }

        // دریافت همه وظایف
        $allTasks = $this->taskModel->getAllWithKnowledgeArea();

        // دریافت وظایف فعلی پروژه
        $currentTasks = $this->projectTaskModel->getByProject($projectId);
        $currentTaskIds = array_column($currentTasks, 'task_id');

        // فیلتر کردن وظایف پیشنهادی (وظایفی که در پروژه نیستند)
        $recommended = [];
        foreach ($allTasks as $task) {
            if (!in_array($task['id'], $currentTaskIds)) {
                $recommended[] = $task;
            }
        }

        // محدود کردن به ۱۰ مورد اول
        $recommended = array_slice($recommended, 0, 10);

        return $this->json([
            'project' => $project,
            'recommended' => $recommended,
            'current' => $currentTaskIds
        ]);
    }
}