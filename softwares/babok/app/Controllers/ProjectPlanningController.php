<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\ProjectTask;

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
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
            return;
        }

        // دریافت وظایف انتخاب‌شده برای پروژه
        $selectedTasks = $this->projectTaskModel->getByProject($projectId);
        
        // دریافت تمام وظایف BABOK به تفکیک حوزه
        $allTasks = $this->taskModel->getAllWithKnowledgeArea();

        $this->view('project-planning/index', [
            'project' => $project,
            'selectedTasks' => $selectedTasks,
            'allTasks' => $allTasks
        ]);
    }

    /**
     * افزودن وظیفه به پروژه
     */
    public function addTask()
    {
        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
            return;
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);

        // اعتبارسنجی
        if (!$projectId || !$taskId) {
            $_SESSION['flash_error'] = 'اطلاعات ناقص است.';
            $this->redirect('/planning/' . $projectId);
            return;
        }

        // بررسی وجود پروژه
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
            return;
        }

        // بررسی وجود وظیفه
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            $_SESSION['flash_error'] = 'وظیفه مورد نظر یافت نشد.';
            $this->redirect('/planning/' . $projectId);
            return;
        }

        // افزودن وظیفه به پروژه
        $result = $this->projectTaskModel->addTask($projectId, $taskId);
        
        if ($result) {
            $_SESSION['flash_success'] = 'وظیفه با موفقیت به پروژه اضافه شد.';
        } else {
            $_SESSION['flash_error'] = 'خطا در افزودن وظیفه. ممکن است قبلاً اضافه شده باشد.';
        }
        
        $this->redirect('/planning/' . $projectId);
    }

    /**
     * حذف وظیفه از پروژه
     */
    public function removeTask()
    {
        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
            return;
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);

        // اعتبارسنجی
        if (!$projectId || !$taskId) {
            $_SESSION['flash_error'] = 'اطلاعات ناقص است.';
            $this->redirect('/projects');
            return;
        }

        // حذف وظیفه از پروژه
        $result = $this->projectTaskModel->removeTask($projectId, $taskId);
        
        if ($result) {
            $_SESSION['flash_success'] = 'وظیفه با موفقیت از پروژه حذف شد.';
        } else {
            $_SESSION['flash_error'] = 'خطا در حذف وظیفه.';
        }
        
        $this->redirect('/planning/' . $projectId);
    }

    /**
     * به‌روزرسانی وضعیت وظیفه (AJAX)
     */
    public function updateTaskStatus()
    {
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