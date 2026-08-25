<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Project;

class ReportController extends Controller
{
    public function projectReport($id)
    {
        $this->requireAuth();

        $model = new Project();
        $project = $model->getWithDetails((int)$id);

        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=project');
        }

        $this->view('report/project', [
            'pageTitle'    => 'گزارش - ' . $project['name'],
            'currentPage'  => 'report',
            'project'      => $project,
            'criteria'     => $model->getCriteria((int)$id),
            'alternatives' => $model->getAlternatives((int)$id),
            'results'      => $model->getResults((int)$id),
        ]);
    }
}