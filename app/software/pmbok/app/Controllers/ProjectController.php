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
        
        if (!empty($phase)) { $sql .= " AND p.phase = ?"; $params[] = $phase; }
        if (!empty($methodology)) { $sql .= " AND p.methodology = ?"; $params[] = $methodology; }
        if (!empty($search)) { $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $projects = $stmt->fetchAll();
        
        $this->view('project/index', [
            'pageTitle' => 'پروژه‌های من',
            'currentPage' => 'project',
            'projects' => $projects,
            'phase' => $phase,
            'methodology' => $methodology,
            'search' => $search,
        ]);
    }
    
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=project'); return; }
        
        $project = $this->getUserProject($id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا شما به آن دسترسی ندارید.');
            $this->redirect('controller=project');
            return;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}deliverables WHERE project_id = ? AND user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $deliverables = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}risks WHERE project_id = ? AND user_id = ? ORDER BY risk_score DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $risks = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}project_stakeholders WHERE project_id = ? AND user_id = ? ORDER BY influence DESC, interest DESC");
        $stmt->execute([$id, $this->currentUserId]);
        $stakeholders = $stmt->fetchAll();
        
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
            $industry = trim($_POST['industry'] ?? 'services');
            $stakeholder_count = intval($_POST['stakeholder_count'] ?? 5);
            
            if (empty($name)) {
                $this->flashError('نام پروژه الزامی است.');
                $this->redirect('controller=project&action=create');
                return;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}projects (user_id, name, description, phase, methodology, industry, stakeholder_count, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$this->currentUserId, $name, $description, $phase, $methodology, $industry, $stakeholder_count]);
            
            $projectId = $this->db->lastInsertId();
            $this->applyIndustryTemplate($projectId, $industry);
            
            $this->logActivity('create', 'project', $projectId);
            $this->flashSuccess('پروژه با موفقیت ایجاد شد و فرآیندهای پیشنهادی صنعت اضافه گردید. ✅');
            $this->redirect('controller=project&action=show&id=' . $projectId);
            return;
        }
        
        $this->view('project/create', ['pageTitle' => 'ایجاد پروژه جدید', 'currentPage' => 'project']);
    }

    private function applyIndustryTemplate($projectId, $industry)
    {
        $templates = [
            'manufacturing' => ['tasks' => [1, 11, 24, 25, 26], 'risks' => ['خرابی تجهیزات خط تولید', 'تأخیر در تأمین مواد اولیه']],
            'oil_gas' => ['tasks' => [1, 6, 36, 37, 43], 'risks' => ['نشت مواد خطرناک و حوادث HSE', 'تأخیر در مجوزهای رگولاتوری']],
            'steel' => ['tasks' => [11, 24, 27, 37], 'risks' => ['مصرف بالای انرژی و قطعی برق', 'آلودگی محیط زیست']],
            'fmcg' => ['tasks' => [2, 5, 12, 24], 'risks' => ['تغییر ناگهانی سلیقه مشتری', 'فساد محصولات در زنجیره سرد']],
            'services' => ['tasks' => [2, 9, 33, 46], 'risks' => ['کمبود نیروی انسانی متخصص', 'چرخش بالای مشتریان']]
        ];

        $template = $templates[$industry] ?? $templates['services'];

        foreach ($template['tasks'] as $taskId) {
            $check = $this->db->prepare("SELECT id FROM {$this->prefix}project_tasks WHERE project_id = ? AND task_id = ? AND user_id = ?");
            $check->execute([$projectId, $taskId, $this->currentUserId]);
            if (!$check->fetch()) {
                $this->db->prepare("INSERT INTO {$this->prefix}project_tasks (user_id, project_id, task_id, status) VALUES (?, ?, ?, 'not_started')")
                     ->execute([$this->currentUserId, $projectId, $taskId]);
            }
        }

        foreach ($template['risks'] as $riskTitle) {
            $this->db->prepare("INSERT INTO {$this->prefix}risks (user_id, project_id, title, probability, impact, risk_score, status, created_at, updated_at) VALUES (?, ?, ?, 'medium', 'medium', 9, 'identified', NOW(), NOW())")
                 ->execute([$this->currentUserId, $projectId, $riskTitle]);
        }
    }

    public function edit($id = null)
    {
        $this->requireAuth();
        if (!$id) { $this->redirect('controller=project'); return; }
        $project = $this->getUserProject($id);
        if (!$project) { $this->redirect('controller=project'); return; }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $phase = trim($_POST['phase'] ?? 'initiation');
            $methodology = trim($_POST['methodology'] ?? 'hybrid');
            
            $this->db->prepare("UPDATE {$this->prefix}projects SET name = ?, description = ?, phase = ?, methodology = ?, updated_at = NOW() WHERE id = ? AND user_id = ?")
                 ->execute([$name, $description, $phase, $methodology, $id, $this->currentUserId]);
            
            $this->flashSuccess('پروژه با موفقیت بروزرسانی شد. ✅');
            $this->redirect('controller=project&action=show&id=' . $id);
        }
        
        $this->view('project/edit', ['pageTitle' => 'ویرایش پروژه', 'currentPage' => 'project', 'project' => $project]);
    }

    // =========================================================================
    // مدیریت تحویل‌دادنی‌ها (Deliverables) - ✅ اصلاح شده
    // =========================================================================
    public function addDeliverable($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=project&action=show&id=' . $id); return; }
        $project = $this->getUserProject($id);
        if (!$project) { $this->redirect('controller=project'); return; }
        
        $name = trim($_POST['deliverable_name'] ?? '');
        $description = trim($_POST['deliverable_description'] ?? '');
        $status = trim($_POST['deliverable_status'] ?? 'pending');
        $planned_date = !empty($_POST['deliverable_planned_date']) ? $_POST['deliverable_planned_date'] : null;

        if (empty($name)) { 
            $this->flashError('نام تحویل‌دادنی الزامی است.'); 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=deliverables'); 
            return; 
        }
        
        $this->db->prepare("INSERT INTO {$this->prefix}deliverables (user_id, project_id, name, description, status, planned_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
             ->execute([$this->currentUserId, $id, $name, $description, $status, $planned_date]);
        
        $this->flashSuccess('تحویل‌دادنی با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id . '&tab=deliverables');
    }

    public function updateDeliverable()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');
        $planned_date = !empty($_POST['planned_date']) ? $_POST['planned_date'] : null;

        $this->db->prepare("UPDATE {$this->prefix}deliverables SET name=?, description=?, status=?, planned_date=? WHERE id=? AND user_id=?")
                 ->execute([$name, $description, $status, $planned_date, $id, $this->currentUserId]);
        
        $this->flashSuccess('تحویل‌دادنی با موفقیت بروزرسانی شد. ✅');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=deliverables");
    }

    public function deleteDeliverable()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        $this->db->prepare("DELETE FROM {$this->prefix}deliverables WHERE id = ? AND project_id = ? AND user_id = ?")
                 ->execute([$id, $projectId, $this->currentUserId]);
        
        $this->flashSuccess('تحویل‌دادنی حذف شد.');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=deliverables");
    }

    // =========================================================================
    // مدیریت ریسک‌ها (Risks) - ✅ اصلاح شده
    // =========================================================================
    public function addRisk($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=risks'); 
            return; 
        }
        $project = $this->getUserProject($id);
        if (!$project) { 
            $this->redirect('controller=project'); 
            return; 
        }
        
        $title = trim($_POST['risk_title'] ?? '');
        $probability = trim($_POST['risk_probability'] ?? 'medium');
        $impact = trim($_POST['risk_impact'] ?? 'medium');
        
        if (empty($title)) { 
            $this->flashError('عنوان ریسک الزامی است.'); 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=risks'); 
            return; 
        }
        
        $risk_score = \App\Software\Pmbok\Models\Risk::calculateRiskScore($probability, $impact);
        $this->db->prepare("INSERT INTO {$this->prefix}risks (user_id, project_id, title, probability, impact, risk_score, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'identified', NOW(), NOW())")
             ->execute([$this->currentUserId, $id, $title, $probability, $impact, $risk_score]);
        
        $this->flashSuccess('ریسک ثبت شد. امتیاز: ' . $risk_score . ' ⚠️');
        $this->redirect('controller=project&action=show&id=' . $id . '&tab=risks');
    }

    public function updateRisk()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }

        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $probability = $_POST['probability'] ?? 'medium';
        $impact = $_POST['impact'] ?? 'medium';

        if (empty($title)) {
            $this->flashError('عنوان ریسک الزامی است.');
            $this->redirect("controller=project&action=show&id={$projectId}&tab=risks");
            return;
        }

        $risk_score = \App\Software\Pmbok\Models\Risk::calculateRiskScore($probability, $impact);
        $this->db->prepare("UPDATE {$this->prefix}risks SET title=?, probability=?, impact=?, risk_score=?, updated_at=NOW() WHERE id=? AND project_id=? AND user_id=?")
                 ->execute([$title, $probability, $impact, $risk_score, $id, $projectId, $this->currentUserId]);

        $this->flashSuccess('ریسک با موفقیت بروزرسانی شد. ✅');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=risks");
    }

    public function deleteRisk()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['risk_id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['risk_id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        $this->db->prepare("DELETE FROM {$this->prefix}risks WHERE id = ? AND project_id = ? AND user_id = ?")
                 ->execute([$id, $projectId, $this->currentUserId]);
        
        $this->flashSuccess('ریسک حذف شد.');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=risks");
    }

    // =========================================================================
    // مدیریت ذی‌نفعان (Stakeholders) - ✅ اصلاح شده
    // =========================================================================
    public function addStakeholder($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=stakeholders'); 
            return; 
        }
        $project = $this->getUserProject($id);
        if (!$project) { 
            $this->redirect('controller=project'); 
            return; 
        }
        
        $name = trim($_POST['stakeholder_name'] ?? '');
        $role = trim($_POST['stakeholder_role'] ?? '');
        $email = trim($_POST['stakeholder_email'] ?? '');
        $influence = trim($_POST['stakeholder_influence'] ?? 'medium');
        $interest = trim($_POST['stakeholder_interest'] ?? 'medium');
        $engagement = trim($_POST['stakeholder_engagement'] ?? 'neutral');
        
        if (empty($name) || empty($role)) { 
            $this->flashError('نام و نقش ذی‌نفع الزامی است.'); 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=stakeholders'); 
            return; 
        }
        
        $this->db->prepare("INSERT INTO {$this->prefix}project_stakeholders (user_id, project_id, name, role, email, influence, interest, engagement_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())")
             ->execute([$this->currentUserId, $id, $name, $role, $email, $influence, $interest, $engagement]);
        
        $this->flashSuccess('ذی‌نفع با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id . '&tab=stakeholders');
    }

    public function updateStakeholder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }

        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $influence = $_POST['influence'] ?? 'medium';
        $interest = $_POST['interest'] ?? 'medium';

        if (empty($name) || empty($role)) {
            $this->flashError('نام و نقش ذی‌نفع الزامی است.');
            $this->redirect("controller=project&action=show&id={$projectId}&tab=stakeholders");
            return;
        }

        $this->db->prepare("UPDATE {$this->prefix}project_stakeholders SET name=?, role=?, email=?, influence=?, interest=?, updated_at=NOW() WHERE id=? AND project_id=? AND user_id=?")
                 ->execute([$name, $role, $email, $influence, $interest, $id, $projectId, $this->currentUserId]);

        $this->flashSuccess('ذی‌نفع با موفقیت بروزرسانی شد. ✅');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=stakeholders");
    }

    public function deleteStakeholder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['stakeholder_id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['stakeholder_id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        $this->db->prepare("DELETE FROM {$this->prefix}project_stakeholders WHERE id = ? AND project_id = ? AND user_id = ?")
                 ->execute([$id, $projectId, $this->currentUserId]);
        
        $this->flashSuccess('ذی‌نفع حذف شد.');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=stakeholders");
    }

    // =========================================================================
    // مدیریت فرآیندها (Project Tasks) - ✅ اصلاح شده
    // =========================================================================
    public function addTask($id = null)
    {
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=tasks'); 
            return; 
        }
        $project = $this->getUserProject($id);
        if (!$project) { 
            $this->redirect('controller=project'); 
            return; 
        }
        
        $task_id = intval($_POST['task_id'] ?? 0);
        $status = trim($_POST['task_status'] ?? 'not_started');
        
        if ($task_id <= 0) { 
            $this->flashError('انتخاب فرآیند الزامی است.'); 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=tasks'); 
            return; 
        }
        
        $check = $this->db->prepare("SELECT id FROM {$this->prefix}project_tasks WHERE project_id = ? AND task_id = ? AND user_id = ?");
        $check->execute([$id, $task_id, $this->currentUserId]);
        if ($check->fetch()) { 
            $this->flashError('این فرآیند قبلاً اضافه شده است.'); 
            $this->redirect('controller=project&action=show&id=' . $id . '&tab=tasks'); 
            return; 
        }
        
        $this->db->prepare("INSERT INTO {$this->prefix}project_tasks (user_id, project_id, task_id, status) VALUES (?, ?, ?, ?)")
             ->execute([$this->currentUserId, $id, $task_id, $status]);
        
        $this->flashSuccess('فرآیند با موفقیت اضافه شد. ✅');
        $this->redirect('controller=project&action=show&id=' . $id . '&tab=tasks');
    }

    public function updateTask()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? 'not_started';
        
        $this->db->prepare("UPDATE {$this->prefix}project_tasks SET status=? WHERE id=? AND project_id=? AND user_id=?")
                 ->execute([$status, $id, $projectId, $this->currentUserId]);
        
        $this->flashSuccess('وضعیت فرآیند بروزرسانی شد.');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=tasks");
    }

    public function updateProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }

        $id = (int)$_POST['id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? 'not_started';
        $notes = trim($_POST['notes'] ?? '');

        // منطق مدیریت زمان‌ها بر اساس وضعیت جدید
        $now = date('Y-m-d H:i:s');
        $started_at = ($status === 'in_progress' || $status === 'completed') ? $now : null;
        $completed_at = ($status === 'completed') ? $now : null;

        // حذف updated_at به دلیل عدم وجود در جدول
        // استفاده از COALESCE برای started_at تا اگر تسک از قبل زمان شروعی دارد، آن را حفظ کند و بازنویسی نشود
        $sql = "UPDATE {$this->prefix}project_tasks 
                SET status = ?, 
                    notes = ?, 
                    started_at = COALESCE(started_at, ?), 
                    completed_at = ? 
                WHERE id = ? AND project_id = ? AND user_id = ?";
                    
        $this->db->prepare($sql)
                ->execute([$status, $notes, $started_at, $completed_at, $id, $projectId, $this->currentUserId]);

        $this->flashSuccess('وضعیت تسک با موفقیت بروزرسانی شد. ✅');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=tasks");
    }

    public function deleteTask()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['pt_id'])) { 
            $this->redirect('controller=project'); 
            return; 
        }
        $id = (int)$_POST['pt_id'];
        $projectId = (int)($_POST['project_id'] ?? 0);
        
        $this->db->prepare("DELETE FROM {$this->prefix}project_tasks WHERE id = ? AND project_id = ? AND user_id = ?")
                 ->execute([$id, $projectId, $this->currentUserId]);
        
        $this->flashSuccess('فرآیند از پروژه حذف شد.');
        $this->redirect("controller=project&action=show&id={$projectId}&tab=tasks");
    }

    private function getUserProject($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->currentUserId]);
        return $stmt->fetch();
    }

    public function updateTaskInline()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'درخواست نامعتبر است.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'not_started');
        $notes = trim($_POST['notes'] ?? '');

        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'شناسه فرآیند نامعتبر است.']);
            exit;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}project_tasks 
            SET status = ?, notes = ?, updated_at = NOW() 
            WHERE id = ? AND user_id = ?
        ");
        
        $result = $stmt->execute([$status, $notes, $id, $this->currentUserId]);

        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'تغییرات با موفقیت ذخیره شد. ✅']);
        } else {
            echo json_encode(['success' => false, 'message' => 'خطا در ذخیره‌سازی تغییرات.']);
        }
        exit;
    }
}