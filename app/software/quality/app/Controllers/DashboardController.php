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
        $allCharts = $chartModel->findBySystem($systemId);
        $recentCharts = array_slice($allCharts, 0, 5);
        $outOfControl = $chartModel->countOutOfControl($systemId);

        $capModel = new CapabilityStudy();
        $allCapabilities = $capModel->findBySystem($systemId);
        $recentCapabilities = array_slice($allCapabilities, 0, 5);
        $avgCpk = $capModel->averageCpk($systemId);

        $msaModel = new MsaStudy();
        $allMsa = $msaModel->findBySystem($systemId);
        $recentMsa = array_slice($allMsa, 0, 5);
        $avgGrr = $msaModel->averagePctGrr($systemId);

        $alerts = [];
        foreach ($allCharts as $chart) {
            if ((int) ($chart['in_control'] ?? 1) !== 1) {
                $alerts[] = [
                    'level' => 'danger',
                    'icon' => 'fa-chart-line',
                    'message' => 'نمودار «' . ($chart['dataset_name'] ?? 'بدون نام') . '» خارج از کنترل است.',
                    'url' => CURRENT_MODULE_URL . '?controller=' . (in_array(($chart['chart_type'] ?? ''), ['p', 'np', 'c', 'u'], true) ? 'attribute_chart' : 'control_chart') . '&action=show&id=' . (int) $chart['id'],
                ];
            }
        }
        foreach ($allCapabilities as $study) {
            if ($study['cpk'] === null) {
                continue;
            }
            $cpk = (float) $study['cpk'];
            if ($cpk < 1.33) {
                $alerts[] = [
                    'level' => $cpk < 1.00 ? 'danger' : 'warning',
                    'icon' => 'fa-bullseye',
                    'message' => 'Cpk مطالعه «' . ($study['dataset_name'] ?? 'بدون نام') . '» کمتر از حد پیش‌فرض 1.33 است (' . number_format($cpk, 2) . ').',
                    'url' => CURRENT_MODULE_URL . '?controller=capability&action=show&id=' . (int) $study['id'],
                ];
            }
        }
        foreach ($allMsa as $study) {
            if ($study['pct_grr'] === null) {
                continue;
            }
            $grr = (float) $study['pct_grr'];
            if ($grr >= 10.0) {
                $alerts[] = [
                    'level' => $grr > 30.0 ? 'danger' : 'warning',
                    'icon' => 'fa-ruler-combined',
                    'message' => '%GRR مطالعه «' . ($study['name'] ?? 'بدون نام') . '» به حد هشدار پیش‌فرض 10٪ رسیده یا از آن عبور کرده است (' . number_format($grr, 2) . '%).',
                    'url' => CURRENT_MODULE_URL . '?controller=msa&action=show&id=' . (int) $study['id'],
                ];
            }
        }

        $projectModel = new Project();
        $recentProjects = array_slice($projectModel->findBySystemWithStats($systemId), 0, 5);

        $this->renderSoftware('dashboard/index', [
            'title'              => 'داشبورد کیفیت',
            'system'             => $system,
            'stats'              => $stats,
            'recentCharts'       => $recentCharts,
            'outOfControl'       => $outOfControl,
            'alerts'             => $alerts,
            'recentCapabilities' => $recentCapabilities,
            'avgCpk'             => $avgCpk,
            'recentMsa'          => $recentMsa,
            'avgGrr'             => $avgGrr,
            'recentProjects'     => $recentProjects,
        ], 'quality');
    }
}
