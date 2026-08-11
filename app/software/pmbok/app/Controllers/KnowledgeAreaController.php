<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class KnowledgeAreaController extends Controller
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
        // لیست حوزه‌های دانشی با آمار
        $sql = "SELECT ka.*, 
                       COUNT(DISTINCT t.id) as task_count,
                       COUNT(DISTINCT tt.technique_id) as technique_count
                FROM {$this->prefix}knowledge_areas ka 
                LEFT JOIN {$this->prefix}tasks t ON ka.id = t.knowledge_area_id
                LEFT JOIN {$this->prefix}task_techniques tt ON t.id = tt.task_id
                GROUP BY ka.id
                ORDER BY ka.id";
        
        $knowledgeAreas = $this->db->query($sql)->fetchAll();
        
        // ✅ اصلاح شده: استفاده صحیح از prepare برای کوئری‌های پارامتردار
        $totalProjects = 0;
        if ($this->currentUserId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->prefix}projects WHERE user_id = ?");
            $stmt->execute([$this->currentUserId]);
            $totalProjects = (int)$stmt->fetchColumn();
        }
        
        $stats = [
            'total_ka' => count($knowledgeAreas),
            'total_tasks' => $this->db->query("SELECT COUNT(*) FROM {$this->prefix}tasks")->fetchColumn(),
            'total_techniques' => $this->db->query("SELECT COUNT(*) FROM {$this->prefix}techniques")->fetchColumn(),
            'total_projects' => $totalProjects,
        ];
        
        $this->view('knowledge-area/index', [
            'pageTitle' => 'حوزه‌های دانشی',
            'currentPage' => 'knowledgeArea',
            'knowledgeAreas' => $knowledgeAreas,
            'stats' => $stats,
        ]);
    }
    
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=knowledgearea'); return; }
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}knowledge_areas WHERE id = ?");
        $stmt->execute([$id]);
        $ka = $stmt->fetch();
        
        if (!$ka) { $this->redirect('controller=knowledgearea'); return; }
        
        // تسک‌ها
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM {$this->prefix}task_techniques tt WHERE tt.task_id = t.id) as technique_count,
                   (SELECT COUNT(*) FROM {$this->prefix}project_tasks pt WHERE pt.task_id = t.id) as project_count
            FROM {$this->prefix}tasks t 
            WHERE t.knowledge_area_id = ? 
            ORDER BY t.code
        ");
        $stmt->execute([$id]);
        $tasks = $stmt->fetchAll();
        
        // تکنیک‌ها
        $stmt = $this->db->prepare("
            SELECT DISTINCT te.*, COUNT(DISTINCT tt.task_id) as task_count
            FROM {$this->prefix}techniques te
            JOIN {$this->prefix}task_techniques tt ON te.id = tt.technique_id
            JOIN {$this->prefix}tasks t ON tt.task_id = t.id
            WHERE t.knowledge_area_id = ?
            GROUP BY te.id
            ORDER BY te.name
        ");
        $stmt->execute([$id]);
        $techniques = $stmt->fetchAll();
        
        // پروژه‌ها - ✅ فقط پروژه‌های کاربر فعلی
        $projects = [];
        if ($this->currentUserId) {
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.*, COUNT(pt.id) as task_count
                FROM {$this->prefix}projects p
                JOIN {$this->prefix}project_tasks pt ON p.id = pt.project_id
                JOIN {$this->prefix}tasks t ON pt.task_id = t.id
                WHERE t.knowledge_area_id = ? AND p.user_id = ?
                GROUP BY p.id
                ORDER BY p.name
            ");
            $stmt->execute([$id, $this->currentUserId]);
            $projects = $stmt->fetchAll();
        }
        
        $this->view('knowledge-area/view', [
            'pageTitle' => $ka['name'],
            'currentPage' => 'knowledgeArea',
            'ka' => $ka,
            'tasks' => $tasks,
            'techniques' => $techniques,
            'projects' => $projects,
        ]);
    }
}