<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Helpers\SensitivityAnalyzer;

class SensitivityController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->requireAuth();
        $projects = $this->model->getSolvedProjects($this->currentUserId);
        
        $this->view('sensitivity/index', [
            'pageTitle'   => 'تحلیل حساسیت',
            'currentPage' => 'sensitivity',
            'projects'    => $projects,
        ]);
    }

    public function report($id)
    {
        $this->requireAuth();
        $project = $this->model->getWithDetails((int)$id);
        
        if (!$project || $project['user_id'] != $this->currentUserId) {
            $this->flashError('دسترسی مجاز نیست.');
            $this->redirect('controller=sensitivity');
            return;
        }

        if ($project['status'] !== 'solved') {
            $this->flashError('این پروژه هنوز حل نشده است.');
            $this->redirect('controller=sensitivity');
            return;
        }

        $solution = json_decode($project['solution_data'] ?? '{}', true);
        $modelData = json_decode($project['model_data'] ?? '{}', true);
        $problemType = $project['problem_type_code'] ?? '';

        // تحلیل حساسیت بر اساس نوع مسئله
        $analysis = SensitivityAnalyzer::analyze($problemType, $solution, $modelData);

        $this->view('sensitivity/report', [
            'pageTitle'    => 'تحلیل حساسیت: ' . $project['name'],
            'currentPage'  => 'sensitivity',
            'project'      => $project,
            'problemType'  => $problemType,
            'solution'     => $solution,
            'modelData'    => $modelData,
            'analysis'     => $analysis,
        ]);
    }
}