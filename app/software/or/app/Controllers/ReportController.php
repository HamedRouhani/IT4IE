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
        $this->requireAuth();
        $userId = (int) $this->currentUserId;
        // دریافت لیست پروژه‌ها از طریق مدل
        $projects = $userId > 0 ? $this->projectModel->getWithType($userId) : [];

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
        $this->requireAuth();
        $project = $this->projectModel->getByIdWithProblemType((int)$id);

        if (!$project || (int) $project['user_id'] !== (int) $this->currentUserId) {
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
