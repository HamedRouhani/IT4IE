<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class ProjectController extends Controller
{
    private $db;
    private $prefix = 'pmbok_';
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function index()
    {
        $phase = isset($_GET['phase']) ? trim($_GET['phase']) : '';
        $methodology = isset($_GET['methodology']) ? trim($_GET['methodology']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM {$this->prefix}deliverables WHERE project_id = p.id) as deliverable_count,
                       (SELECT COUNT(*) FROM {$this->prefix}risks WHERE project_id = p.id) as risk_count,
                       (SELECT COUNT(*) FROM {$this->prefix}project_stakeholders WHERE project_id = p.id) as stakeholder_count_actual,
                       (SELECT COUNT(*) FROM {$this->prefix}project_tasks WHERE project_id = p.id) as task_count
                FROM {$this->prefix}projects p
                WHERE p.user_id = ?";
        $params = [$this->currentUserId];
        
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
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();
        
        $stats = [
            'total' => count($projects),
            'all_projects' => $this->getUserProjectCount(),
            'active' => $this->getUserProjectCount(['phase' => ['execution', 'planning']]),
            'completed' => $this->getUserProjectCount(['phase' => ['closure']]),
        ];
        
        $this->view('project/index', [
            'pageTitle' => 'پروژه‌های من',
            'currentPage' => 'project',
            'projects' => $projects,
            'stats' => $stats,
            'phase' => $phase,
            'methodology' => $methodology,
            'search' => $search,
        ]);
    }
    
    // ✅ تغییر نام از view به show
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا شما به آن دسترسی ندارید.');
            $this->redirect('controller=project');
            return;
        }
        
        // تحویل‌دادنی‌ها
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}deliverables WHERE project_id = ? AND user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $deliverables = $stmt->fetchAll();
        
        // ریسک‌ها
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}risks WHERE project_id = ? AND user_id = ? ORDER BY risk_score DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $risks = $stmt->fetchAll();
        
        // ذی‌نفعان
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}project_stakeholders WHERE project_id = ? AND user_id = ? ORDER BY influence DESC, interest DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $stakeholders = $stmt->fetchAll();
        
        // تسک‌های پروژه
        $stmt = $this->db->prepare("
            SELECT pt.*, t.name as task_name, t.code as task_code, ka.name as ka_name
            FROM {$this->prefix}project_tasks pt
            JOIN {$this->prefix}tasks t ON pt.task_id = t.id
            JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE pt.project_id = ? AND pt.user_id = ?
            ORDER BY t.code
        ");
        $stmt->execute([$id, $this->currentUserId]);
        $projectTasks = $stmt->fetchAll();
        
        // همه تسک‌ها (عمومی)
        $allTasks = $this->db->query("
            SELECT t.*, ka.name as ka_name 
            FROM {$this->prefix}tasks t 
            JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id 
            ORDER BY t.code
        ")->fetchAll();
        
        $this->view('project/view', [
            'pageTitle' => $project['name'],
            'currentPage' => 'project',
            'project' => $project,
            'deliverables' => $deliverables,
            'risks' => $risks,
            'stakeholders' => $stakeholders,
            'projectTasks' => $projectTasks,
            'allTasks' => $allTasks,
        ]);
    }
    
    public function create()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $phase = trim($_POST['phase'] ?? 'initiation');
            $methodology = trim($_POST['methodology'] ?? 'hybrid');
            
            if (empty($name)) {
                $this->flashError('نام پروژه الزامی است.');
                $this->redirect('controller=project&action=create');
                return;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}projects (user_id, name, description, phase, methodology, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$this->currentUserId, $name, $description, $phase, $methodology]);
            
            $this->logActivity('create', 'project', $this->db->lastInsertId());
            $this->flashSuccess('پروژه با موفقیت ایجاد شد. ✅');
            $this->redirect('controller=project');
            return;
        }
        
        $this->view('project/create', [
            'pageTitle' => 'ایجاد پروژه جدید',
            'currentPage' => 'project',
        ]);
    }
    
    public function edit($id = null)
    {
        $this->requireAuth();
        if (!$id) { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=project');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $phase = trim($_POST['phase'] ?? 'initiation');
            $methodology = trim($_POST['methodology'] ?? 'hybrid');
            
            if (empty($name)) {
                $this->flashError('نام پروژه الزامی است.');
                $this->redirect('controller=project&action=edit&id=' . $id);
                return;
            }
            
            $stmt = $this->db->prepare("
                UPDATE {$this->prefix}projects 
                SET name = ?, description = ?, phase = ?, methodology = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$name, $description, $phase, $methodology, $id, $this->currentUserId]);
            
            $this->logActivity('update', 'project', $id);
            $this->flashSuccess('پروژه با موفقیت بروزرسانی شد. ✅');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $this->view('project/edit', [
            'pageTitle' => 'ویرایش پروژه',
            'currentPage' => 'project',
            'project' => $project,
        ]);
    }
    
    public function delete($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=project');
            return;
        }
        
        $this->db->prepare("DELETE FROM {$this->prefix}deliverables WHERE project_id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        $this->db->prepare("DELETE FROM {$this->prefix}risks WHERE project_id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        $this->db->prepare("DELETE FROM {$this->prefix}project_stakeholders WHERE project_id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        $this->db->prepare("DELETE FROM {$this->prefix}project_tasks WHERE project_id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        $this->db->prepare("DELETE FROM {$this->prefix}projects WHERE id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        
        $this->logActivity('delete', 'project', $id);
        $this->flashSuccess('پروژه با موفقیت حذف شد. 🗑️');
        $this->redirect('controller=project');
    }
    
    public function addDeliverable($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) { $this->flashError('دسترسی ندارید.'); $this->redirect('controller=project'); return; }
        
        $name = trim($_POST['deliverable_name'] ?? '');
        $description = trim($_POST['deliverable_description'] ?? '');
        $status = trim($_POST['deliverable_status'] ?? 'pending');
        $planned_date = trim($_POST['deliverable_planned_date'] ?? '');
        
        if (empty($name)) {
            $this->flashError('نام تحویل‌دادنی الزامی است.');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}deliverables (user_id, project_id, name, description, status, planned_date, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$this->currentUserId, $id, $name, $description, $status, $planned_date]);
        
        $this->logActivity('create', 'deliverable', $this->db->lastInsertId());
        $this->flashSuccess('تحویل‌دادنی با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function addStakeholder($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) { $this->flashError('دسترسی ندارید.'); $this->redirect('controller=project'); return; }
        
        $name = trim($_POST['stakeholder_name'] ?? '');
        $role = trim($_POST['stakeholder_role'] ?? '');
        $email = trim($_POST['stakeholder_email'] ?? '');
        $influence = trim($_POST['stakeholder_influence'] ?? 'medium');
        $interest = trim($_POST['stakeholder_interest'] ?? 'medium');
        $engagement = trim($_POST['stakeholder_engagement'] ?? 'neutral');
        
        if (empty($name) || empty($role)) {
            $this->flashError('نام و نقش ذی‌نفع الزامی است.');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}project_stakeholders (user_id, project_id, name, role, email, influence, interest, engagement_status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$this->currentUserId, $id, $name, $role, $email, $influence, $interest, $engagement]);
        
        $count = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}project_stakeholders WHERE project_id = ?");
        $count->execute([$id]);
        $total = $count->fetchColumn();
        $this->db->prepare("UPDATE {$this->prefix}projects SET stakeholder_count = ?, updated_at = NOW() WHERE id = ?")->execute([$total, $id]);
        
        $this->flashSuccess('ذی‌نفع با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function addRisk($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) { $this->flashError('دسترسی ندارید.'); $this->redirect('controller=project'); return; }
        
        $title = trim($_POST['risk_title'] ?? '');
        $probability = trim($_POST['risk_probability'] ?? 'medium');
        $impact = trim($_POST['risk_impact'] ?? 'medium');
        
        if (empty($title)) {
            $this->flashError('عنوان ریسک الزامی است.');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $risk_score = \App\Software\Pmbok\Models\Risk::calculateRiskScore($probability, $impact);
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}risks (user_id, project_id, title, probability, impact, risk_score, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 'identified', NOW(), NOW())
        ");
        $stmt->execute([$this->currentUserId, $id, $title, $probability, $impact, $risk_score]);
        
        $this->logActivity('create', 'risk', $this->db->lastInsertId());
        $this->flashSuccess('ریسک ثبت شد. امتیاز: ' . $risk_score . ' ⚠️');
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function addTask($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) { $this->flashError('دسترسی ندارید.'); $this->redirect('controller=project'); return; }
        
        $task_id = intval($_POST['task_id'] ?? 0);
        $status = trim($_POST['task_status'] ?? 'not_started');
        
        if ($task_id <= 0) {
            $this->flashError('انتخاب فرآیند الزامی است.');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $check = $this->db->prepare("SELECT id FROM {$this->prefix}project_tasks WHERE project_id = ? AND task_id = ? AND user_id = ?");
        $check->execute([$id, $task_id, $this->currentUserId]);
        if ($check->fetch()) {
            $this->flashError('این فرآیند قبلاً اضافه شده است.');
            $this->redirect('controller=project&action=show&id=' . $id);
            return;
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}project_tasks (user_id, project_id, task_id, status) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$this->currentUserId, $id, $task_id, $status]);
        
        $this->flashSuccess('فرآیند با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function deleteDeliverable($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $did = intval($_POST['deliverable_id'] ?? 0);
        if ($did > 0) {
            $this->db->prepare("DELETE FROM {$this->prefix}deliverables WHERE id = ? AND project_id = ? AND user_id = ?")->execute([$did, $id, $this->currentUserId]);
            $this->flashSuccess('تحویل‌دادنی حذف شد.');
        }
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function deleteStakeholder($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $sid = intval($_POST['stakeholder_id'] ?? 0);
        if ($sid > 0) {
            $this->db->prepare("DELETE FROM {$this->prefix}project_stakeholders WHERE id = ? AND project_id = ? AND user_id = ?")->execute([$sid, $id, $this->currentUserId]);
            
            $count = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}project_stakeholders WHERE project_id = ?");
            $count->execute([$id]);
            $this->db->prepare("UPDATE {$this->prefix}projects SET stakeholder_count = ?, updated_at = NOW() WHERE id = ?")->execute([$count->fetchColumn(), $id]);
            
            $this->flashSuccess('ذی‌نفع حذف شد.');
        }
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function deleteRisk($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $rid = intval($_POST['risk_id'] ?? 0);
        if ($rid > 0) {
            $this->db->prepare("DELETE FROM {$this->prefix}risks WHERE id = ? AND project_id = ? AND user_id = ?")->execute([$rid, $id, $this->currentUserId]);
            $this->flashSuccess('ریسک حذف شد.');
        }
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    public function deleteTask($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project'); return; }
        
        $ptId = intval($_POST['pt_id'] ?? 0);
        if ($ptId > 0) {
            $this->db->prepare("DELETE FROM {$this->prefix}project_tasks WHERE id = ? AND project_id = ? AND user_id = ?")->execute([$ptId, $id, $this->currentUserId]);
            $this->flashSuccess('فرآیند حذف شد.');
        }
        $this->redirect('controller=project&action=show&id=' . $id);
    }
    
    private function getUserProject($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->currentUserId]);
        return $stmt->fetch();
    }
    
    private function getUserProjectCount($filters = [])
    {
        $sql = "SELECT COUNT(*) FROM {$this->prefix}projects WHERE user_id = ?";
        $params = [$this->currentUserId];
        
        if (isset($filters['phase'])) {
            if (is_array($filters['phase'])) {
                $placeholders = implode(',', array_fill(0, count($filters['phase']), '?'));
                $sql .= " AND phase IN ({$placeholders})";
                $params = array_merge($params, $filters['phase']);
            } else {
                $sql .= " AND phase = ?";
                $params[] = $filters['phase'];
            }
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}