<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Project;

/**
 * کنترلر مدیریت پروژه‌های BABOK
 */
class ProjectController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        $this->projectModel = new Project();
    }

    /**
     * لیست همه پروژه‌ها
     */
    public function index()
    {
        $projects = $this->projectModel->getAll();

        $this->view('projects/index', [
            'title' => 'مدیریت پروژه‌ها - BABOK Analyzer',
            'activePage' => 'projects',
            'projects' => $projects
        ]);
    }

    /**
     * نمایش فرم ایجاد پروژه جدید
     */
    public function create()
    {
        $this->view('projects/create', [
            'title' => 'ایجاد پروژه جدید - BABOK Analyzer',
            'activePage' => 'projects'
        ]);
    }

    /**
     * ذخیره پروژه جدید در دیتابیس
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function store()
    {
        // بررسی احراز هویت
        $this->requireAuth();

        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        // دریافت و پاکسازی داده‌ها
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phase' => $_POST['phase'] ?? 'initiation',
            'methodology' => $_POST['methodology'] ?? 'hybrid',
            'stakeholder_count' => (int) ($_POST['stakeholder_count'] ?? 0)
        ];

        // اعتبارسنجی
        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_create');
            return;
        }

        // اعتبارسنجی فاز و متدولوژی
        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

        // ایجاد پروژه
        $id = $this->projectModel->create($data);

        if ($id) {
            // ثبت لاگ فعالیت
            $this->logActivity('create_project', 'project', $id, null, $data);

            $this->flashSuccess('پروژه با موفقیت ایجاد شد.');
            $this->redirect('projects_view&id=' . $id);
        } else {
            $this->flashError('خطا در ایجاد پروژه. لطفاً مجدداً تلاش کنید.');
            $this->redirect('projects_create');
        }
    }

    /**
     * نمایش جزئیات یک پروژه
     */
    public function show($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        // دریافت وظایف پروژه و پیشرفت
        $tasks = $this->projectModel->getTasks($id);
        $progress = $this->projectModel->getProgress($id);

        $this->view('projects/view', [
            'title' => $project['name'] . ' - BABOK Analyzer',
            'activePage' => 'projects',
            'project' => $project,
            'tasks' => $tasks,
            'progress' => $progress
        ]);
    }

    /**
     * نمایش فرم ویرایش پروژه
     */
    public function edit($id)
    {
        $project = $this->projectModel->find($id);

        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        $this->view('projects/edit', [
            'title' => 'ویرایش پروژه - BABOK Analyzer',
            'activePage' => 'projects',
            'project' => $project
        ]);
    }

    /**
     * به‌روزرسانی اطلاعات پروژه
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function update($id)
    {
        // بررسی احراز هویت
        $this->requireAuth();

        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        // بررسی وجود پروژه
        $project = $this->projectModel->find($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        // دریافت و پاکسازی داده‌ها
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phase' => $_POST['phase'] ?? 'initiation',
            'methodology' => $_POST['methodology'] ?? 'hybrid',
            'stakeholder_count' => (int) ($_POST['stakeholder_count'] ?? 0)
        ];

        // اعتبارسنجی
        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_edit&id=' . $id);
            return;
        }

        // اعتبارسنجی فاز و متدولوژی
        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

        // به‌روزرسانی پروژه
        $result = $this->projectModel->update($id, $data);

        if ($result) {
            // ثبت لاگ فعالیت
            $this->logActivity('update_project', 'project', $id, $project, $data);

            $this->flashSuccess('پروژه با موفقیت به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی پروژه. لطفاً مجدداً تلاش کنید.');
        }

        $this->redirect('projects_view&id=' . $id);
    }

    /**
     * حذف پروژه
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function delete($id)
    {
        // بررسی احراز هویت
        $this->requireAuth();

        // بررسی وجود پروژه
        $project = $this->projectModel->find($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('projects');
            return;
        }

        // حذف وظایف مرتبط با پروژه (برای جلوگیری از خطای foreign key)
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare("DELETE FROM babok_project_tasks WHERE project_id = ?");
            $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Error deleting project tasks: " . $e->getMessage());
        }

        // حذف پروژه
        $result = $this->projectModel->delete($id);

        if ($result) {
            // ثبت لاگ فعالیت
            $this->logActivity('delete_project', 'project', $id, $project, null);

            $this->flashSuccess('پروژه با موفقیت حذف شد.');
        } else {
            $this->flashError('خطا در حذف پروژه. لطفاً مجدداً تلاش کنید.');
        }

        $this->redirect('projects');
    }

    /**
     * دریافت لیست پروژه‌ها به صورت JSON (برای AJAX)
     */
    public function getProjectsJson()
    {
        $projects = $this->projectModel->getAll();
        return $this->json($projects);
    }

    /**
     * دریافت اطلاعات یک پروژه به صورت JSON (برای AJAX)
     */
    public function getProjectJson($id)
    {
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->json(['error' => 'پروژه یافت نشد.']);
        }
        return $this->json($project);
    }

    /**
     * دریافت وضعیت پیشرفت پروژه به صورت JSON (برای AJAX)
     */
    public function getProgressJson($id)
    {
        $project = $this->projectModel->find($id);
        if (!$project) {
            return $this->json(['error' => 'پروژه یافت نشد.']);
        }
        $progress = $this->projectModel->getProgress($id);
        return $this->json($progress);
    }
}