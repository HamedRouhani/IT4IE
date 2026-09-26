<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\ControlChart;
use App\Software\Quality\Models\CapabilityStudy;
use App\Software\Quality\Models\MsaStudy;
use App\Software\Quality\Models\Project;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findActiveByUser($userId);

        if (!$system) {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
        }

        $systemId = (int) $system['id'];
        $stats = $systemModel->getStats($systemId);

        $chartModel = new ControlChart();
        $recentCharts = $chartModel->getLatestBySystem($systemId, 5);
        $outOfControl = $chartModel->countOutOfControl($systemId);

        $capModel = new CapabilityStudy();
        $recentCapabilities = array_slice($capModel->findBySystem($systemId), 0, 5);
        $avgCpk = $capModel->averageCpk($systemId);

        $msaModel = new MsaStudy();
        $recentMsa = array_slice($msaModel->findBySystem($systemId), 0, 5);
        $avgGrr = $msaModel->averagePctGrr($systemId);

        $projectModel = new Project();
        $recentProjects = array_slice($projectModel->findBySystemWithStats($systemId), 0, 5);

        $this->renderSoftware('dashboard/index', [
            'title'              => 'داشبورد کیفیت',
            'system'             => $system,
            'stats'              => $stats,
            'recentCharts'       => $recentCharts,
            'outOfControl'       => $outOfControl,
            'recentCapabilities' => $recentCapabilities,
            'avgCpk'             => $avgCpk,
            'recentMsa'          => $recentMsa,
            'avgGrr'             => $avgGrr,
            'recentProjects'     => $recentProjects,
        ], 'quality');
    }
}