<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;
use App\Software\Statlab\Models\Result;

class DashboardController extends Controller
{
    private $projectModel;
    private $datasetModel;
    private $resultModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
        $this->datasetModel = new Dataset();
        $this->resultModel  = new Result();
    }

    public function index(): void
    {
        $this->requireAuth();

        $stats = [
            'total_projects' => $this->projectModel->countByUser($this->currentUserId),
            'completed'      => $this->projectModel->countByUserAndStatus($this->currentUserId, 'completed'),
            'total_datasets' => $this->datasetModel->count(['user_id' => $this->currentUserId]),
            'total_results'  => $this->resultModel->count(),
        ];

        $recentProjects = $this->projectModel->getRecentByUser($this->currentUserId, 5);

        $this->view('dashboard/index', [
            'pageTitle'      => 'داشبورد StatLab',
            'currentPage'    => 'dashboard',
            'stats'          => $stats,
            'recentProjects' => $recentProjects,
        ]);
    }
}