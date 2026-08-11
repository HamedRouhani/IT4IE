<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class TaskController extends Controller
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
        $ka_id = isset($_GET['ka_id']) ? intval($_GET['ka_id']) : 0;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $sql = "SELECT t.*, ka.name as ka_name, ka.code as ka_code,
                       (SELECT COUNT(*) FROM {$this->prefix}task_techniques WHERE task_id = t.id) as technique_count
                FROM {$this->prefix}tasks t
                JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE 1=1";
        $params = [];
        
        if ($ka_id > 0) {
            $sql .= " AND t.knowledge_area_id = ?";
            $params[] = $ka_id;
        }
        if (!empty($search)) {
            $sql .= " AND (t.name LIKE ? OR t.description LIKE ? OR t.code LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " ORDER BY t.code";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll();
        
        $knowledgeAreas = $this->db->query("SELECT * FROM {$this->prefix}knowledge_areas ORDER BY id")->fetchAll();
        
        $stats = [
            'total' => count($tasks),
            'all_tasks' => $this->db->query("SELECT COUNT(*) FROM {$this->prefix}tasks")->fetchColumn(),
            'ka_count' => $this->db->query("SELECT COUNT(*) FROM {$this->prefix}knowledge_areas")->fetchColumn(),
        ];
        
        $this->view('task/index', [
            'pageTitle' => 'فرآیندها',
            'currentPage' => 'task',
            'tasks' => $tasks,
            'knowledgeAreas' => $knowledgeAreas,
            'stats' => $stats,
            'ka_id' => $ka_id,
            'search' => $search,
        ]);
    }
    
    // ✅ تغییر نام از view به show
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=task'); return; }
        
        $stmt = $this->db->prepare("
            SELECT t.*, ka.name as ka_name, ka.code as ka_code, ka.id as ka_id
            FROM {$this->prefix}tasks t
            JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $task = $stmt->fetch();
        
        if (!$task) { $this->redirect('controller=task'); return; }
        
        $stmt = $this->db->prepare("
            SELECT te.* 
            FROM {$this->prefix}techniques te
            JOIN {$this->prefix}task_techniques tt ON te.id = tt.technique_id
            WHERE tt.task_id = ?
            ORDER BY te.name
        ");
        $stmt->execute([$id]);
        $techniques = $stmt->fetchAll();
        
        $this->view('task/view', [
            'pageTitle' => $task['name'],
            'currentPage' => 'task',
            'task' => $task,
            'techniques' => $techniques,
        ]);
    }
}