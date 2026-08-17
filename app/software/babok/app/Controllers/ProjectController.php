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

        // 🌟 آمار هوشمند کیفیت نیازمندی‌ها (Dashboard Analytics)
        $qualityStatsStmt = $this->db->prepare("
            SELECT 
                ROUND(AVG(pt.quality_score), 1) as avg_quality,
                SUM(CASE WHEN pt.quality_score >= 80 THEN 1 ELSE 0 END) as excellent_count,
                SUM(CASE WHEN pt.quality_score BETWEEN 60 AND 79 THEN 1 ELSE 0 END) as good_count,
                SUM(CASE WHEN pt.quality_score < 60 AND pt.quality_score > 0 THEN 1 ELSE 0 END) as needs_improvement_count
            FROM {$this->prefix}project_tasks pt
            INNER JOIN {$this->prefix}projects p ON pt.project_id = p.id
            WHERE p.user_id = ?
        ");
        $qualityStatsStmt->execute([$this->userId]);
        $stats['quality'] = $qualityStatsStmt->fetch() ?: [
            'avg_quality' => 0, 'excellent_count' => 0, 'good_count' => 0, 'needs_improvement_count' => 0
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
            'user_id' => $this->userId,
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'phase' => $_POST['phase'] ?? 'initiation',
            'methodology' => $_POST['methodology'] ?? 'hybrid',
            'stakeholder_count' => (int) ($_POST['stakeholder_count'] ?? 0)
        ];

        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_create');
            return;
        }

        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

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

        if (empty($data['name'])) {
            $this->flashError('لطفاً نام پروژه را وارد کنید.');
            $this->redirect('projects_edit&id=' . $id);
            return;
        }

        $validPhases = ['initiation', 'planning', 'analysis', 'design', 'implementation', 'evaluation'];
        if (!in_array($data['phase'], $validPhases)) {
            $data['phase'] = 'initiation';
        }

        $validMethodologies = ['waterfall', 'agile', 'hybrid'];
        if (!in_array($data['methodology'], $validMethodologies)) {
            $data['methodology'] = 'hybrid';
        }

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

        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد یا شما به آن دسترسی ندارید.');
            $this->redirect('projects');
            return;
        }

        try {
            $stmt = $this->db->prepare("
                DELETE FROM {$this->prefix}project_tasks 
                WHERE project_id = ? AND user_id = ?
            ");
            $stmt->execute([$id, $this->userId]);
        } catch (\Exception $e) {
            error_log("Error deleting project tasks: " . $e->getMessage());
        }

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

    // ============================================================
    // 🌟 متدهای جدید برای هوشمندسازی و مدیریت وظایف
    // ============================================================

    /**
     * 🌟 به‌روزرسانی یادداشت و امتیاز کیفیت یک وظیفه پروژه (AJAX/Form)
     * 🔒 بررسی دقیق مالکیت پروژه قبل از ذخیره
     */
    public function updateTaskQuality()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.'], 405);
        }

        $projectTaskId = (int) ($_POST['project_task_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $qualityScore = (int) ($_POST['quality_score'] ?? 0);
        $status = $_POST['status'] ?? 'not_started';

        if (!$projectTaskId) {
            return $this->json(['error' => 'شناسه وظیفه نامعتبر است.']);
        }

        // 🔒 بررسی مالکیت: آیا این task متعلق به پروژه‌ای است که user_id آن برابر با کاربر فعلی است؟
        $checkStmt = $this->db->prepare("
            SELECT pt.id, p.user_id 
            FROM {$this->prefix}project_tasks pt
            INNER JOIN {$this->prefix}projects p ON pt.project_id = p.id
            WHERE pt.id = ?
        ");
        $checkStmt->execute([$projectTaskId]);
        $task = $checkStmt->fetch();

        if (!$task || $task['user_id'] != $this->userId) {
            return $this->json(['error' => 'شما مجاز به ویرایش این وظیفه نیستید.'], 403);
        }

        // ذخیره‌سازی
        $updateStmt = $this->db->prepare("
            UPDATE {$this->prefix}project_tasks 
            SET notes = :notes, 
                quality_score = :quality_score, 
                status = :status
            WHERE id = :id
        ");
        
        $result = $updateStmt->execute([
            'notes' => $notes,
            'quality_score' => $qualityScore,
            'status' => $status,
            'id' => $projectTaskId
        ]);

        if ($result) {
            $this->logActivity('update_task_quality', 'project_task', $projectTaskId, null, [
                'quality_score' => $qualityScore,
                'status' => $status
            ]);
            return $this->json([
                'success' => true, 
                'message' => 'یادداشت و امتیاز کیفیت با موفقیت ذخیره شد.',
                'score' => $qualityScore
            ]);
        }

        return $this->json(['error' => 'خطا در ذخیره‌سازی اطلاعات.']);
    }

    // ============================================================
    // متدهای کمکی خصوصی
    // ============================================================

    /**
     * 🔒 دریافت پروژه فقط اگر متعلق به کاربر فعلی باشد
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