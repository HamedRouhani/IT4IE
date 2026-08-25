<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;

class DashboardController extends Controller
{
    private $db;
    private $prefix = 'mcdm_';

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database::getInstance();
    }

    public function index()
    {
        $p = $this->prefix;

        $stats = [
            'knowledge_areas' => $this->db->query("SELECT COUNT(*) FROM {$p}knowledge_areas")->fetchColumn(),
            'methods'         => $this->db->query("SELECT COUNT(*) FROM {$p}methods")->fetchColumn(),
            'techniques'      => $this->db->query("SELECT COUNT(*) FROM {$p}techniques")->fetchColumn(),
            'projects'        => 0,
        ];

        if ($this->isAuthenticated()) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$p}projects WHERE user_id = ?");
            $stmt->execute([$this->currentUserId]);
            $stats['projects'] = $stmt->fetchColumn();
        }

        $knowledgeAreas = $this->db->query("
            SELECT ka.*,
                (SELECT COUNT(*) FROM {$p}methods m WHERE m.knowledge_area_id = ka.id) as method_count
            FROM {$p}knowledge_areas ka
            ORDER BY ka.sort_order ASC
        ")->fetchAll();

        $recentProjects = [];
        if ($this->isAuthenticated()) {
            $stmt = $this->db->prepare("
                SELECT p.*, m.name as method_name
                FROM {$p}projects p
                LEFT JOIN {$p}methods m ON p.method_id = m.id
                WHERE p.user_id = ?
                ORDER BY p.updated_at DESC
                LIMIT 5
            ");
            $stmt->execute([$this->currentUserId]);
            $recentProjects = $stmt->fetchAll();
        }

        $this->view('dashboard/index', [
            'pageTitle'      => 'داشبورد',
            'currentPage'    => 'dashboard',
            'stats'          => $stats,
            'knowledgeAreas' => $knowledgeAreas,
            'recentProjects' => $recentProjects,
        ]);
    }
}