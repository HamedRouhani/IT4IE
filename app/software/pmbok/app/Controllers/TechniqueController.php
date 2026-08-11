<?php

namespace App\Software\Pmbok\Controllers;

use App\Software\Pmbok\Core\Controller;

class TechniqueController extends Controller
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
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $ka_id = isset($_GET['ka_id']) ? intval($_GET['ka_id']) : 0;
        
        $sql = "SELECT te.*, 
                       COUNT(DISTINCT tt.task_id) as task_count,
                       GROUP_CONCAT(DISTINCT ka.name SEPARATOR '، ') as ka_names
                FROM {$this->prefix}techniques te
                LEFT JOIN {$this->prefix}task_techniques tt ON te.id = tt.technique_id
                LEFT JOIN {$this->prefix}tasks t ON tt.task_id = t.id
                LEFT JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($category)) {
            $sql .= " AND te.category = ?";
            $params[] = $category;
        }
        if ($ka_id > 0) {
            $sql .= " AND t.knowledge_area_id = ?";
            $params[] = $ka_id;
        }
        if (!empty($search)) {
            $sql .= " AND (te.name LIKE ? OR te.description LIKE ? OR te.purpose LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql .= " GROUP BY te.id ORDER BY te.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $techniques = $stmt->fetchAll();
        
        $knowledgeAreas = $this->db->query("SELECT * FROM {$this->prefix}knowledge_areas ORDER BY id")->fetchAll();
        $categories = $this->db->query("SELECT DISTINCT category FROM {$this->prefix}techniques WHERE category IS NOT NULL AND category != '' ORDER BY category")->fetchAll();
        
        $stats = [
            'total' => count($techniques),
            'all_techniques' => $this->db->query("SELECT COUNT(*) FROM {$this->prefix}techniques")->fetchColumn(),
            'categories_count' => count($categories),
        ];
        
        $this->view('technique/index', [
            'pageTitle' => 'تکنیک‌ها',
            'currentPage' => 'technique',
            'techniques' => $techniques,
            'knowledgeAreas' => $knowledgeAreas,
            'categories' => $categories,
            'stats' => $stats,
            'category' => $category,
            'search' => $search,
            'ka_id' => $ka_id,
        ]);
    }
    
    // ✅ تغییر نام از view به show
    public function show($id = null)
    {
        if (!$id) { $this->redirect('controller=technique'); return; }
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}techniques WHERE id = ?");
        $stmt->execute([$id]);
        $technique = $stmt->fetch();
        
        if (!$technique) { $this->redirect('controller=technique'); return; }
        
        $stmt = $this->db->prepare("
            SELECT t.*, ka.name as ka_name, ka.code as ka_code, ka.id as ka_id
            FROM {$this->prefix}tasks t
            JOIN {$this->prefix}task_techniques tt ON t.id = tt.task_id
            JOIN {$this->prefix}knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE tt.technique_id = ?
            ORDER BY t.code
        ");
        $stmt->execute([$id]);
        $tasks = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT ka.*, COUNT(t.id) as task_count
            FROM {$this->prefix}knowledge_areas ka
            JOIN {$this->prefix}tasks t ON ka.id = t.knowledge_area_id
            JOIN {$this->prefix}task_techniques tt ON t.id = tt.task_id
            WHERE tt.technique_id = ?
            GROUP BY ka.id
            ORDER BY ka.code
        ");
        $stmt->execute([$id]);
        $knowledgeAreas = $stmt->fetchAll();
        
        $this->view('technique/view', [
            'pageTitle' => $technique['name'],
            'currentPage' => 'technique',
            'technique' => $technique,
            'tasks' => $tasks,
            'knowledgeAreas' => $knowledgeAreas,
        ]);
    }
}