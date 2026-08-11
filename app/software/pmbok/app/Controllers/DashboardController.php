<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class DashboardController extends Controller
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
        $p = $this->prefix;
        
        // آمار عمومی (بدون فیلتر کاربر)
        $stats = [
            'knowledge_areas' => $this->db->query("SELECT COUNT(*) FROM {$p}knowledge_areas")->fetchColumn(),
            'tasks' => $this->db->query("SELECT COUNT(*) FROM {$p}tasks")->fetchColumn(),
            'techniques' => $this->db->query("SELECT COUNT(*) FROM {$p}techniques")->fetchColumn(),
            'projects' => 0,
            'risks' => 0,
            'deliverables' => 0,
        ];
        
        // آمار خصوصی (فقط کاربر وارد شده)
        if ($this->isAuthenticated()) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$p}projects WHERE user_id = ?");
            $stmt->execute([$this->currentUserId]);
            $stats['projects'] = $stmt->fetchColumn();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$p}risks WHERE user_id = ?");
            $stmt->execute([$this->currentUserId]);
            $stats['risks'] = $stmt->fetchColumn();
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$p}deliverables WHERE user_id = ?");
            $stmt->execute([$this->currentUserId]);
            $stats['deliverables'] = $stmt->fetchColumn();
        }
        
        // لیست حوزه‌های دانشی (عمومی)
        $knowledgeAreas = $this->db->query("
            SELECT ka.*, 
                   (SELECT COUNT(*) FROM {$p}tasks t WHERE t.knowledge_area_id = ka.id) as task_count
            FROM {$p}knowledge_areas ka 
            ORDER BY ka.id
        ")->fetchAll();
        
        // پروژه‌های اخیر (فقط کاربر)
        $recentProjects = [];
        $highRisks = [];
        
        if ($this->isAuthenticated()) {
            $stmt = $this->db->prepare("
                SELECT * FROM {$p}projects 
                WHERE user_id = ?
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $stmt->execute([$this->currentUserId]);
            $recentProjects = $stmt->fetchAll();
            
            $stmt = $this->db->prepare("
                SELECT r.*, p.name as project_name 
                FROM {$p}risks r 
                JOIN {$p}projects p ON r.project_id = p.id 
                WHERE r.user_id = ? AND r.risk_score >= 15 
                ORDER BY r.risk_score DESC 
                LIMIT 5
            ");
            $stmt->execute([$this->currentUserId]);
            $highRisks = $stmt->fetchAll();
        }
        
        $this->view('dashboard/index', [
            'pageTitle' => 'داشبورد',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'knowledgeAreas' => $knowledgeAreas,
            'recentProjects' => $recentProjects,
            'highRisks' => $highRisks,
        ]);
    }
}