<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;

class ReportController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
    }

    /**
     * نمایش لیست گزارش‌های کلی پروژه‌ها
     */
    public function index()
    {
        // دریافت لیست پروژه‌ها از طریق مدل
        $projects = $this->projectModel->getAllWithProblemType();

        $this->view('report/index', [
            'pageTitle'   => 'گزارش‌ها و آمار پروژه‌ها',
            'currentPage' => 'report',
            'projects'    => $projects
        ]);
    }

    /**
     * نمایش گزارش تفصیلی یک پروژه خاص
     */
    public function show($id)
    {
        $project = $this->projectModel->getByIdWithProblemType((int)$id);

        if (!$project) {
            $this->flashError('پروژه مورد نظر یافت نشد.');
            $this->redirect('controller=report');
        }

        $this->view('report/show', [
            'pageTitle'   => 'گزارش تفصیلی: ' . $project['name'],
            'currentPage' => 'report',
            'project'     => $project
        ]);
    }
}