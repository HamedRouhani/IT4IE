<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Project;

/**
 * کنترلر مدیریت پروژه‌های BABOK
 * 🔒 هر کاربر فقط به پروژه‌های خودش دسترسی دارد
 */
class ProjectController extends Controller
{
    private $projectModel;
    private $db;
    private $prefix = 'babok_';
    private $userId;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->db = \App\Core\Database::getInstance();
        $this->userId = $_SESSION['user_id'] ?? null;
    }

    /**
     * 🔒 لیست پروژه‌ها - فقط پروژه‌های کاربر فعلی
     * ورود اجباری
     */
    public function index()
    {
        $this->requireAuth();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $phase  = isset($_GET['phase'])  ? trim($_GET['phase'])  : '';
        $methodology = isset($_GET['methodology']) ? trim($_GET['methodology']) : '';

        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM {$this->prefix}project_tasks WHERE project_id = p.id) AS task_count,
                       (SELECT COUNT(*) FROM {$this->prefix}project_tasks WHERE project_id = p.id AND status = 'completed') AS completed_count
                FROM {$this->prefix}projects p
                WHERE p.user_id = ?";
        $params = [$this->userId];

        if (!empty($phase)) {
            $sql .= " AND p.phase = ?";
            $params[] = $phase;
        }
        if (!empty($methodology)) {
            $sql .= " AND p.methodology = ?";
            $params[] = $methodology;
        }
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY p.updated_at DESC, p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        // آمار پروژه‌های کاربر
        $stats = [
            'total' => count($projects),
            'all_projects' => $this->countUserProjects(),
            'active' => $this->countUserProjects(['phase_not' => 'evaluation']),
            'completed' => $this->countUserProjects(['phase' => 'evaluation']),
        ];

        $this->view('projects/index', [
            'title' => 'پروژه‌های من - BABOK Analyzer',
            'activePage' => 'projects',
            'projects' => $projects,
            'stats' => $stats,
            'search' => $search,
            'phase' => $phase,
            'methodology' => $methodology,
        ]);
    }

    /**
     * نمایش فرم ایجاد پروژه جدید - ورود اجباری
     */
    public function create()
    {
        $this->requireAuth();

        $this->view('projects/create', [
            'title' => 'ایجاد پروژه جدید - BABOK Analyzer',
            'activePage' => 'projects'
        ]);
    }

    /**
     * ذخیره پروژه جدید در دیتابیس
     * 🔒 ورود اجباری + ثبت user_id
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        $data = [
            'user_id' => $this->userId,  // 🔒 ثبت مالک
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phase' => $_POST['phase'] ?? 'initiation',
            'methodology' => $_POST['methodology'] ?? 'hybrid',
            'stakeholder_count' => (int) ($_POST['stakeholder_count'] ?? 0)
        ];

        // اعتبارسنجی نام
        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_create');
            return;
        }

        // اعتبارسنجی فاز
        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        // اعتبارسنجی متدولوژی
        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

        // ایجاد پروژه با user_id
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}projects 
            (user_id, name, description, phase, methodology, stakeholder_count) 
            VALUES (:user_id, :name, :description, :phase, :methodology, :stakeholder_count)
        ");
        $stmt->execute([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'phase' => $data['phase'],
            'methodology' => $data['methodology'],
            'stakeholder_count' => $data['stakeholder_count']
        ]);

        $id = $this->db->lastInsertId();

        if ($id) {
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
     * 🔒 فقط مالک می‌تواند ببیند
     */
    public function show($id)
    {
        $this->requireAuth();

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد یا شما به آن دسترسی ندارید.');
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
     * 🔒 فقط مالک می‌تواند ویرایش کند
     */
    public function edit($id)
    {
        $this->requireAuth();

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد یا شما به آن دسترسی ندارید.');
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
     * 🔒 ورود اجباری + فقط مالک
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('projects');
            return;
        }

        // 🔒 بررسی مالکیت
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد یا شما به آن دسترسی ندارید.');
            $this->redirect('projects');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phase' => $_POST['phase'] ?? 'initiation',
            'methodology' => $_POST['methodology'] ?? 'hybrid',
            'stakeholder_count' => (int) ($_POST['stakeholder_count'] ?? 0)
        ];

        // اعتبارسنجی نام
        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_edit&id=' . $id);
            return;
        }

        // اعتبارسنجی فاز
        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        // اعتبارسنجی متدولوژی
        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

        // 🔒 به‌روزرسانی فقط اگر مالک باشد
        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}projects 
            SET name = :name, description = :description, phase = :phase, 
                methodology = :methodology, stakeholder_count = :stakeholder_count,
                updated_at = NOW()
            WHERE id = :id AND user_id = :user_id
        ");
        $result = $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'],
            'phase' => $data['phase'],
            'methodology' => $data['methodology'],
            'stakeholder_count' => $data['stakeholder_count'],
            'id' => $id,
            'user_id' => $this->userId
        ]);

        if ($result && $stmt->rowCount() > 0) {
            $this->logActivity('update_project', 'project', $id, $project, $data);
            $this->flashSuccess('پروژه با موفقیت به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی پروژه یا تغییری اعمال نشد.');
        }

        $this->redirect('projects_view&id=' . $id);
    }

    /**
     * حذف پروژه
     * 🔒 ورود اجباری + فقط مالک
     */
    public function delete($id)
    {
        $this->requireAuth();

        // 🔒 بررسی مالکیت
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد یا شما به آن دسترسی ندارید.');
            $this->redirect('projects');
            return;
        }

        // 🔒 حذف وظایف مرتبط (فقط متعلق به خود کاربر)
        try {
            $stmt = $this->db->prepare("
                DELETE FROM {$this->prefix}project_tasks 
                WHERE project_id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $this->userId]);
        } catch (\Exception $e) {
            error_log("Error deleting project tasks: " . $e->getMessage());
        }

        // 🔒 حذف پروژه فقط اگر مالک باشد
        $stmt = $this->db->prepare("
            DELETE FROM {$this->prefix}projects 
            WHERE id = ? AND user_id = ?
        ");
        $result = $stmt->execute([$id, $this->userId]);

        if ($result && $stmt->rowCount() > 0) {
            $this->logActivity('delete_project', 'project', $id, $project, null);
            $this->flashSuccess('پروژه با موفقیت حذف شد.');
        } else {
            $this->flashError('خطا در حذف پروژه یا شما به آن دسترسی ندارید.');
        }

        $this->redirect('projects');
    }

    /**
     * دریافت لیست پروژه‌ها به صورت JSON
     * 🔒 فقط پروژه‌های کاربر فعلی
     */
    public function getProjectsJson()
    {
        if (!$this->userId) {
            return $this->json(['error' => 'ورود الزامی است.']);
        }

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->prefix}projects 
            WHERE user_id = ? 
            ORDER BY updated_at DESC
        ");
        $stmt->execute([$this->userId]);
        $projects = $stmt->fetchAll();

        return $this->json($projects);
    }

    /**
     * دریافت اطلاعات یک پروژه به صورت JSON
     * 🔒 فقط اگر مالک باشد
     */
    public function getProjectJson($id)
    {
        $project = $this->getUserProject($id);
        if (!$project) {
            return $this->json(['error' => 'پروژه یافت نشد یا دسترسی ندارید.']);
        }
        return $this->json($project);
    }

    /**
     * دریافت وضعیت پیشرفت پروژه به صورت JSON
     * 🔒 فقط اگر مالک باشد
     */
    public function getProgressJson($id)
    {
        $project = $this->getUserProject($id);
        if (!$project) {
            return $this->json(['error' => 'پروژه یافت نشد یا دسترسی ندارید.']);
        }
        $progress = $this->projectModel->getProgress($id);
        return $this->json($progress);
    }

    // ============================================================
    // متدهای کمکی خصوصی
    // ============================================================

    /**
     * 🔒 دریافت پروژه فقط اگر متعلق به کاربر فعلی باشد
     * @param int $id
     * @return array|false
     */
    private function getUserProject($id)
    {
        if (!$this->userId) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM {$this->prefix}projects 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$id, $this->userId]);
        return $stmt->fetch();
    }

    /**
     * شمارش پروژه‌های کاربر با فیلترهای اختیاری
     * @param array $filters
     * @return int
     */
    private function countUserProjects($filters = [])
    {
        if (!$this->userId) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$this->prefix}projects WHERE user_id = ?";
        $params = [$this->userId];

        if (isset($filters['phase'])) {
            $sql .= " AND phase = ?";
            $params[] = $filters['phase'];
        }
        if (isset($filters['phase_not'])) {
            $sql .= " AND phase != ?";
            $params[] = $filters['phase_not'];
        }
        if (isset($filters['methodology'])) {
            $sql .= " AND methodology = ?";
            $params[] = $filters['methodology'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}