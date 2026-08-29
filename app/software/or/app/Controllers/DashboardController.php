<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;

class DashboardController extends Controller
{
    private $projectModel;
    private $problemTypeModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
        $this->problemTypeModel = new ProblemType();
    }

    public function index()
    {
        $this->requireAuth();

        // آمار کلی با استفاده از متدهای عمومی مدل
        $stats = [
            'total_projects'  => $this->projectModel->countByUser($this->currentUserId),
            'solved_projects' => $this->projectModel->countByUserAndStatus($this->currentUserId, 'solved'),
            'problem_types'   => $this->problemTypeModel->count(),
            'methods'         => 5, // Simplex, Transportation, Assignment, Transshipment, Shortest Path
        ];

        // دریافت آخرین پروژه‌های حل‌شده از طریق متد عمومی مدل
        $recentProjects = $this->projectModel->getRecentSolvedProjects($this->currentUserId, 5);

        // ✅ اصلاح: استفاده از getAll() به جای all()
        $problemTypes = $this->problemTypeModel->getAll();

        $this->view('dashboard/index', [
            'pageTitle'      => 'داشبورد',
            'currentPage'    => 'dashboard',
            'stats'          => $stats,
            'recentProjects' => $recentProjects,
            'problemTypes'   => $problemTypes,
        ]);
    }
}