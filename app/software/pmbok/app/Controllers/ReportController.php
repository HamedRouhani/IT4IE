<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class ReportController extends Controller
{
    private $db;
    private $prefix = 'pmbok_';
    
    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function index()
    {
        $p = $this->prefix;
        $stats = [
            'total_projects' => $this->db->query("SELECT COUNT(*) FROM {$p}projects")->fetchColumn(),
            'total_tasks' => $this->db->query("SELECT COUNT(*) FROM {$p}tasks")->fetchColumn(),
            'total_techniques' => $this->db->query("SELECT COUNT(*) FROM {$p}techniques")->fetchColumn(),
            'total_risks' => $this->db->query("SELECT COUNT(*) FROM {$p}risks")->fetchColumn(),
            'total_deliverables' => $this->db->query("SELECT COUNT(*) FROM {$p}deliverables")->fetchColumn(),
            'total_stakeholders' => $this->db->query("SELECT COUNT(*) FROM {$p}project_stakeholders")->fetchColumn(),
            
            'projects_by_phase' => $this->db->query("
                SELECT phase, COUNT(*) as count FROM {$p}projects 
                GROUP BY phase 
                ORDER BY FIELD(phase, 'initiation', 'planning', 'execution', 'monitoring_controlling', 'closure')
            ")->fetchAll(),
            
            'projects_by_methodology' => $this->db->query("
                SELECT methodology, COUNT(*) as count FROM {$p}projects GROUP BY methodology
            ")->fetchAll(),
            
            'tasks_by_ka' => $this->db->query("
                SELECT ka.name, ka.code, COUNT(t.id) as count 
                FROM {$p}knowledge_areas ka 
                LEFT JOIN {$p}tasks t ON ka.id = t.knowledge_area_id 
                GROUP BY ka.id ORDER BY ka.id
            ")->fetchAll(),
            
            'techniques_by_category' => $this->db->query("
                SELECT category, COUNT(*) as count FROM {$p}techniques 
                WHERE category IS NOT NULL AND category != '' 
                GROUP BY category ORDER BY count DESC
            ")->fetchAll(),
            
            'risks_by_project' => $this->db->query("
                SELECT p.name, COUNT(r.id) as count, AVG(r.risk_score) as avg_score, MAX(r.risk_score) as max_score
                FROM {$p}projects p 
                LEFT JOIN {$p}risks r ON p.id = r.project_id 
                GROUP BY p.id ORDER BY count DESC
            ")->fetchAll(),
            
            'deliverables_by_status' => $this->db->query("
                SELECT status, COUNT(*) as count FROM {$p}deliverables GROUP BY status
            ")->fetchAll(),
            
            'project_tasks_by_status' => $this->db->query("
                SELECT status, COUNT(*) as count FROM {$p}project_tasks GROUP BY status
            ")->fetchAll(),
            
            'high_risks' => $this->db->query("
                SELECT r.*, p.name as project_name 
                FROM {$p}risks r JOIN {$p}projects p ON r.project_id = p.id 
                WHERE r.risk_score >= 15 
                ORDER BY r.risk_score DESC LIMIT 10
            ")->fetchAll(),
        ];
        
        $this->view('report/index', [
            'pageTitle' => 'گزارش‌ها',
            'currentPage' => 'report',
            'stats' => $stats,
        ]);
    }
}