<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Project;

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
        $this->view('projects/index', ['projects' => $projects]);
    }

    /**
     * نمایش فرم ایجاد پروژه جدید
     */
    public function create()
    {
        $this->view('projects/create');
    }

    /**
     * ذخیره پروژه جدید در دیتابیس
     */
    public function store()
    {
        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
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
            $_SESSION['flash_error'] = 'لطفاً نام پروژه را وارد کنید.';
            $this->redirect('/projects/create');
            return;
        }

        // ایجاد پروژه
        $id = $this->projectModel->create($data);
        
        if ($id) {
            $_SESSION['flash_success'] = 'پروژه با موفقیت ایجاد شد.';
            // اصلاح مسیر: استفاده از ?route=projects_view&id=
            $this->redirect('/projects/view/' . $id);
        } else {
            $_SESSION['flash_error'] = 'خطا در ایجاد پروژه. لطفاً مجدداً تلاش کنید.';
            $this->redirect('/projects/create');
        }
    }

    /**
     * نمایش جزئیات یک پروژه
     */
    public function show($id)
    {
        $project = $this->projectModel->find($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
            return;
        }

        // دریافت وظایف پروژه و پیشرفت
        $tasks = $this->projectModel->getTasks($id);
        $progress = $this->projectModel->getProgress($id);

        $this->view('projects/view', [
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
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
            return;
        }
        $this->view('projects/edit', ['project' => $project]);
    }

    /**
     * به‌روزرسانی اطلاعات پروژه
     */
    public function update($id)
    {
        // بررسی متد درخواست
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects');
            return;
        }

        // بررسی وجود پروژه
        $project = $this->projectModel->find($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
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
            $_SESSION['flash_error'] = 'لطفاً نام پروژه را وارد کنید.';
            $this->redirect('/projects/edit/' . $id);
            return;
        }

        // به‌روزرسانی پروژه
        $result = $this->projectModel->update($id, $data);
        
        if ($result) {
            $_SESSION['flash_success'] = 'پروژه با موفقیت به‌روزرسانی شد.';
        } else {
            $_SESSION['flash_error'] = 'خطا در به‌روزرسانی پروژه. لطفاً مجدداً تلاش کنید.';
        }
        
        $this->redirect('/projects/view/' . $id);
    }

    /**
     * حذف پروژه
     */
    public function delete($id)
    {
        // بررسی وجود پروژه
        $project = $this->projectModel->find($id);
        if (!$project) {
            $_SESSION['flash_error'] = 'پروژه مورد نظر یافت نشد.';
            $this->redirect('/projects');
            return;
        }

        // حذف پروژه
        $result = $this->projectModel->delete($id);
        
        if ($result) {
            $_SESSION['flash_success'] = 'پروژه با موفقیت حذف شد.';
        } else {
            $_SESSION['flash_error'] = 'خطا در حذف پروژه. لطفاً مجدداً تلاش کنید.';
        }
        
        $this->redirect('/projects');
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