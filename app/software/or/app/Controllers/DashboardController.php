<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\Method;

class DashboardController extends Controller
{
    public function index()
    {
        $pm  = new Project();
        $ptm = new ProblemType();
        $mm  = new Method();

        $stats = [
            'total_projects'  => $this->isAuthenticated()
                ? $pm->count(['user_id' => $this->currentUserId])
                : $pm->count(),
            'solved_projects' => $this->isAuthenticated()
                ? $pm->count(['user_id' => $this->currentUserId, 'status' => 'solved'])
                : $pm->count(['status' => 'solved']),
            'problem_types'   => $ptm->count(),
            'methods'         => $mm->count(),
        ];

        $recent = $this->isAuthenticated()
            ? $pm->getWithType($this->currentUserId)
            : $pm->getWithType();

        $this->view('dashboard/index', [
            'pageTitle'      => 'داشبورد',
            'currentPage'    => 'dashboard',
            'stats'          => $stats,
            'recentProjects' => array_slice($recent, 0, 5),
            'problemTypes'   => $ptm->getAll(),
            'methods'        => $mm->getWithProblemType(),
        ]);
    }
}