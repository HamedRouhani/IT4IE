<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;
use App\Software\Pmbok\Models\Risk;

class RiskController extends Controller
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
        $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $probability = isset($_GET['probability']) ? trim($_GET['probability']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $sql = "SELECT r.*, p.name as project_name, p.phase as project_phase
                FROM {$this->prefix}risks r
                JOIN {$this->prefix}projects p ON r.project_id = p.id
                WHERE r.user_id = ?";
        $params = [$this->currentUserId];
        
        if ($project_id > 0) {
            $sql .= " AND r.project_id = ?";
            $params[] = $project_id;
        }
        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        if (!empty($probability)) {
            $sql .= " AND r.probability = ?";
            $params[] = $probability;
        }
        if (!empty($search)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " ORDER BY r.risk_score DESC, r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $risks = $stmt->fetchAll();
        
        $projects = $this->db->prepare("SELECT id, name FROM {$this->prefix}projects WHERE user_id = ? ORDER BY name");
        $projects->execute([$this->currentUserId]);
        $projects = $projects->fetchAll();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}risks WHERE user_id = ?");
        $stmt->execute([$this->currentUserId]);
        $allRisks = $stmt->fetchColumn();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}risks WHERE user_id = ? AND risk_score >= 20");
        $stmt->execute([$this->currentUserId]);
        $highRisks = $stmt->fetchColumn();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}risks WHERE user_id = ? AND risk_score >= 12 AND risk_score < 20");
        $stmt->execute([$this->currentUserId]);
        $mediumRisks = $stmt->fetchColumn();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}risks WHERE user_id = ? AND risk_score < 12");
        $stmt->execute([$this->currentUserId]);
        $lowRisks = $stmt->fetchColumn();
        
        $stats = [
            'total' => count($risks),
            'all_risks' => $allRisks,
            'high_risks' => $highRisks,
            'medium_risks' => $mediumRisks,
            'low_risks' => $lowRisks,
        ];
        
        $this->view('risk/index', [
            'pageTitle' => 'ریسک‌های من',
            'currentPage' => 'risk',
            'risks' => $risks,
            'projects' => $projects,
            'stats' => $stats,
            'project_id' => $project_id,
            'status' => $status,
            'probability' => $probability,
            'search' => $search,
        ]);
    }
    
    // ✅ تغییر نام از view به show
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=risk'); return; }
        
        $risk = $this->getUserRisk($id);
        if (!$risk) {
            $this->flashError('ریسک یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=risk');
            return;
        }
        
        $this->view('risk/view', [
            'pageTitle' => $risk['title'],
            'currentPage' => 'risk',
            'risk' => $risk,
        ]);
    }
    
    public function create()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $project_id = intval($_POST['project_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $probability = trim($_POST['probability'] ?? 'medium');
            $impact = trim($_POST['impact'] ?? 'medium');
            $response_strategy = trim($_POST['response_strategy'] ?? '');
            $response_plan = trim($_POST['response_plan'] ?? '');
            $owner = trim($_POST['owner'] ?? '');
            
            if (empty($title) || $project_id <= 0) {
                $this->flashError('عنوان ریسک و انتخاب پروژه الزامی است.');
                $this->redirect('controller=risk&action=create');
                return;
            }
            
            $project = $this->getUserProject($project_id);
            if (!$project) {
                $this->flashError('پروژه انتخابی معتبر نیست.');
                $this->redirect('controller=risk&action=create');
                return;
            }
            
            $risk_score = Risk::calculateRiskScore($probability, $impact);
            
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}risks (user_id, project_id, title, description, probability, impact, risk_score, response_strategy, response_plan, status, owner, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'identified', ?, NOW(), NOW())
            ");
            $stmt->execute([$this->currentUserId, $project_id, $title, $description, $probability, $impact, $risk_score, $response_strategy, $response_plan, $owner]);
            
            $this->logActivity('create', 'risk', $this->db->lastInsertId());
            $this->flashSuccess('ریسک با موفقیت ایجاد شد. امتیاز: ' . $risk_score);
            $this->redirect('controller=risk');
            return;
        }
        
        $stmt = $this->db->prepare("SELECT id, name FROM {$this->prefix}projects WHERE user_id = ? ORDER BY name");
        $stmt->execute([$this->currentUserId]);
        $projects = $stmt->fetchAll();
        
        $this->view('risk/create', [
            'pageTitle' => 'ثبت ریسک جدید',
            'currentPage' => 'risk',
            'projects' => $projects,
        ]);
    }
    
    public function edit($id = null)
    {
        $this->requireAuth();
        if (!$id) { $this->redirect('controller=risk'); return; }
        
        $risk = $this->getUserRisk($id);
        if (!$risk) {
            $this->flashError('ریسک یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=risk');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $probability = trim($_POST['probability'] ?? 'medium');
            $impact = trim($_POST['impact'] ?? 'medium');
            $response_strategy = trim($_POST['response_strategy'] ?? '');
            $response_plan = trim($_POST['response_plan'] ?? '');
            $owner = trim($_POST['owner'] ?? '');
            $status = trim($_POST['status'] ?? 'identified');
            
            $risk_score = Risk::calculateRiskScore($probability, $impact);
            
            $stmt = $this->db->prepare("
                UPDATE {$this->prefix}risks 
                SET title = ?, description = ?, probability = ?, impact = ?, risk_score = ?, 
                    response_strategy = ?, response_plan = ?, status = ?, owner = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $description, $probability, $impact, $risk_score, $response_strategy, $response_plan, $status, $owner, $id, $this->currentUserId]);
            
            $this->logActivity('update', 'risk', $id);
            $this->flashSuccess('ریسک با موفقیت بروزرسانی شد.');
            $this->redirect('controller=risk&action=show&id=' . $id);
            return;
        }
        
        $stmt = $this->db->prepare("SELECT id, name FROM {$this->prefix}projects WHERE user_id = ? ORDER BY name");
        $stmt->execute([$this->currentUserId]);
        $projects = $stmt->fetchAll();
        
        $this->view('risk/edit', [
            'pageTitle' => 'ویرایش ریسک',
            'currentPage' => 'risk',
            'risk' => $risk,
            'projects' => $projects,
        ]);
    }
    
    public function delete($id = null)
    {
        $this->requireAuth();
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('controller=risk'); return; }
        
        $risk = $this->getUserRisk($id);
        if (!$risk) {
            $this->flashError('ریسک یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=risk');
            return;
        }
        
        $this->db->prepare("DELETE FROM {$this->prefix}risks WHERE id = ? AND user_id = ?")->execute([$id, $this->currentUserId]);
        
        $this->logActivity('delete', 'risk', $id);
        $this->flashSuccess('ریسک با موفقیت حذف شد.');
        $this->redirect('controller=risk');
    }
    
    private function getUserRisk($id)
    {
        $stmt = $this->db->prepare("
            SELECT r.*, p.name as project_name, p.phase as project_phase
            FROM {$this->prefix}risks r
            JOIN {$this->prefix}projects p ON r.project_id = p.id
            WHERE r.id = ? AND r.user_id = ?
        ");
        $stmt->execute([$id, $this->currentUserId]);
        return $stmt->fetch();
    }
    
    private function getUserProject($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $this->currentUserId]);
        return $stmt->fetch();
    }
}