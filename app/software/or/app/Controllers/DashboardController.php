<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\Method; 

class DashboardController extends Controller
{
    private $projectModel;
    private $problemTypeModel;
    private $methodModel; 

    public function __construct()
    {
        parent::__construct();
        $this->projectModel     = new Project();
        $this->problemTypeModel = new ProblemType();
        $this->methodModel      = new Method(); 
    }

    public function index()
    {
        $this->requireAuth();

        // ✅ آمار کلی با خواندن واقعی از دیتابیس
        $stats = [
            'total_projects'  => $this->projectModel->countByUser($this->currentUserId),
            'solved_projects' => $this->projectModel->countByUserAndStatus($this->currentUserId, 'solved'),
            'problem_types'   => $this->problemTypeModel->count(),
            'methods'         => $this->methodModel->count(),
        ];

        // دریافت آخرین پروژه‌های حل‌شده
        $recentProjects = $this->projectModel->getRecentSolvedProjects($this->currentUserId, 5);

        // دریافت انواع مسئله
        $problemTypes = $this->problemTypeModel->getAll();

        // ✅ آمار روش‌ها به تفکیک دسته (اختیاری ولی مفید)
        $methodsByCategory = $this->methodModel->countByCategory();

        $this->view('dashboard/index', [
            'pageTitle'          => 'داشبورد',
            'currentPage'        => 'dashboard',
            'stats'              => $stats,
            'recentProjects'     => $recentProjects,
            'problemTypes'       => $problemTypes,
            'methodsByCategory'  => $methodsByCategory, 
        ]);
    }
}