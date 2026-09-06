<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Result;
use App\Software\Statlab\Models\Dataset;

class ReportController extends Controller
{
    /**
     * صفحه اول: فقط لیست پروژه‌هایی که گزارش (نتیجه) دارند
     */
    public function index(): void
    {
        $this->requireAuth();

        $pm = new Project();
        $rm = new Result();
        $dm = new Dataset();

        // ✅ فقط پروژه‌هایی که حداقل یک نتیجه دارند
        $reportProjects = $rm->query(
            "SELECT p.id, p.name, p.category_code, p.status, p.significance_level,
                    COUNT(r.id) AS result_count,
                    SUM(CASE WHEN r.p_value IS NOT NULL AND r.p_value < 0.05 THEN 1 ELSE 0 END) AS sig_count,
                    MAX(r.created_at) AS last_analysis
             FROM `{$rm->getTableName()}` r
             JOIN `{$pm->getTableName()}` p ON r.project_id = p.id
             WHERE p.user_id = :uid
             GROUP BY p.id, p.name, p.category_code, p.status, p.significance_level
             ORDER BY last_analysis DESC",
            ['uid' => $this->currentUserId]
        );

        // آمار کلی
        $totalResults = 0; $totalSig = 0;
        foreach ($reportProjects as $rp) {
            $totalResults += (int)$rp['result_count'];
            $totalSig += (int)$rp['sig_count'];
        }

        $this->view('report/index', [
            'pageTitle'      => 'گزارش‌های StatLab',
            'currentPage'    => 'report',
            'reportProjects' => $reportProjects,
            'stats'          => [
                'with_report' => count($reportProjects),
                'results'     => $totalResults,
                'sig'         => $totalSig,
                'datasets'    => $dm->count(['user_id' => $this->currentUserId]),
            ],
        ]);
    }

    /**
     * گزارش تفصیلی یک پروژه
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $pm = new Project();
        $rm = new Result();
        $dm = new Dataset();

        $project = $pm->find($id);
        if (!$project || (int)$project['user_id'] !== (int)$this->currentUserId) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=report');
            return;
        }

        $results = $rm->query(
            "SELECT r.*, ds.name AS dataset_name
             FROM `{$rm->getTableName()}` r
             LEFT JOIN `{$dm->getTableName()}` ds ON r.dataset_id = ds.id
             WHERE r.project_id = :pid
             ORDER BY r.id ASC",
            ['pid' => $id]
        );

        if (empty($results)) {
            $this->flashError('این پروژه هنوز گزارشی ندارد.');
            $this->redirect('controller=report');
            return;
        }

        $datasets = $dm->findAll(['project_id' => $id], 'id ASC');

        $this->view('report/show', [
            'pageTitle'   => 'گزارش: ' . $project['name'],
            'currentPage' => 'report',
            'project'     => $project,
            'results'     => $results,
            'datasets'    => $datasets,
        ]);
    }
}