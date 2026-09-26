<?php
/**
 * ============================================================
 * Quality Analyzer — ReportController
 * ============================================================
 * مسیر: app/software/quality/app/Controllers/ReportController.php
 * 
 * گزارش‌های تجمیعی ماژول Quality
 * ============================================================
 */

namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\Dataset;
use App\Software\Quality\Models\ControlChart;
use App\Software\Quality\Models\CapabilityStudy;
use App\Software\Quality\Models\MsaStudy;
use App\Software\Quality\Models\SamplingPlan;

class ReportController extends Controller
{
    protected function requireSystem(): array
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $systemModel = new System();
        $system = $systemModel->findActiveByUser($userId);
        if (!$system) {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
        }
        return $system;
    }

    /**
     * گزارش کلی سیستم
     */
    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        // ── آمار کلی
        $systemModel = new System();
        $stats = $systemModel->getStats($systemId);

        // ── پروژه‌ها با آمار
        $projectModel = new Project();
        $projects = $projectModel->findBySystemWithStats($systemId);

        // ── نمودارها
        $chartModel = new ControlChart();
        $allCharts = $chartModel->findBySystemWithStats($systemId);
        $outOfControlCount = 0;
        $chartTypeCount = ['variable' => 0, 'attribute' => 0];
        foreach ($allCharts as $c) {
            if ((int) $c['in_control'] === 0) $outOfControlCount++;
            if (in_array($c['chart_type'], ['p', 'np', 'c', 'u'], true)) {
                $chartTypeCount['attribute']++;
            } else {
                $chartTypeCount['variable']++;
            }
        }

        // ── تحلیل قابلیت
        $capModel = new CapabilityStudy();
        $capabilities = $capModel->findBySystemWithStats($systemId);
        $avgCpk = $capModel->averageCpk($systemId);
        $capableCount = 0;
        $marginalCount = 0;
        $notCapableCount = 0;
        foreach ($capabilities as $s) {
            $cpk = $s['cpk'] !== null ? (float) $s['cpk'] : null;
            if ($cpk === null) continue;
            if ($cpk >= 1.33) $capableCount++;
            elseif ($cpk >= 1.00) $marginalCount++;
            else $notCapableCount++;
        }

        // ── MSA
        $msaModel = new MsaStudy();
        $msaStudies = $msaModel->findBySystem($systemId);
        $avgGrr = $msaModel->averagePctGrr($systemId);
        $msaAcceptable = 0;
        $msaMarginal = 0;
        $msaUnacceptable = 0;
        foreach ($msaStudies as $m) {
            $grr = $m['pct_grr'] !== null ? (float) $m['pct_grr'] : null;
            if ($grr === null) continue;
            if ($grr < 10) $msaAcceptable++;
            elseif ($grr <= 30) $msaMarginal++;
            else $msaUnacceptable++;
        }

        // ── نمونه‌گیری
        $samplingModel = new SamplingPlan();
        $samplingPlans = $samplingModel->findBySystem($systemId);

        // ── دیتاست‌ها
        $datasetModel = new Dataset();
        $datasets = $datasetModel->findBySystem($systemId);

        $this->renderSoftware('report/index', [
            'title'  => 'گزارش‌های کیفیت',
            'system' => $system,
            'stats'  => $stats,
            'projects'       => $projects,
            'allCharts'      => $allCharts,
            'outOfControl'   => $outOfControlCount,
            'chartTypes'     => $chartTypeCount,
            'capabilities'   => $capabilities,
            'avgCpk'         => $avgCpk,
            'capableCount'   => $capableCount,
            'marginalCount'  => $marginalCount,
            'notCapableCount'=> $notCapableCount,
            'msaStudies'     => $msaStudies,
            'avgGrr'         => $avgGrr,
            'msaAcceptable'  => $msaAcceptable,
            'msaMarginal'    => $msaMarginal,
            'msaUnacceptable'=> $msaUnacceptable,
            'samplingPlans'  => $samplingPlans,
            'datasets'       => $datasets,
        ], 'quality');
    }
}