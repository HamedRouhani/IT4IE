<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Project;

class ReportController extends Controller
{
    public function index()
    {
        $projectModel = new Project();
        $projects = $projectModel->getWithMethod($this->currentUserId);
        
        $this->view('report/index', [
            'pageTitle' => 'گزارش‌ها',
            'currentPage' => 'report',
            'projects' => $projects,
        ]);
    }
    
    public function projectReport($id)
    {
        $projectModel = new Project();
        $project = $projectModel->getWithDetails((int)$id);
        
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=project');
        }
        
        $criteria = $projectModel->getCriteria((int)$id);
        $alternatives = $projectModel->getAlternatives((int)$id);
        $results = $projectModel->getResults((int)$id);
        
        $this->view('report/project', [
            'pageTitle' => 'گزارش: ' . $project['name'],
            'currentPage' => 'report',
            'project' => $project,
            'criteria' => $criteria,
            'alternatives' => $alternatives,
            'results' => $results,
        ]);
    }
}