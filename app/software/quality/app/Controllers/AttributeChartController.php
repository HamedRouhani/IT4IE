<?php
/**
 * ============================================================
 * Quality Analyzer — AttributeChartController
 * ============================================================
 * مسیر: app/software/quality/app/Controllers/AttributeChartController.php
 * 
 * مدیریت نمودارهای کنترل صفتی: p, np, c, u
 * ============================================================
 */

namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Dataset;
use App\Software\Quality\Models\ControlChart;
use App\Software\Quality\Services\ControlChartService;
use App\Software\Quality\Services\NelsonRulesService;

class AttributeChartController extends Controller
{
    /**
     * انواع نمودار صفتی
     */
    protected const ATTRIBUTE_TYPES = ['p', 'np', 'c', 'u'];

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

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

    protected function findOwnedDataset(int $datasetId, int $systemId): ?array
    {
        $model = new Dataset();
        $ds = $model->find($datasetId);
        if (!$ds || (int) $ds['system_id'] !== $systemId) {
            return null;
        }
        return $ds;
    }

    /**
     * لیست نمودارهای صفتی
     */
    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new ControlChart();
        $allCharts = $model->findBySystemWithStats($systemId);

        // 🎯 فیلتر فقط صفتی‌ها
        $charts = array_values(array_filter($allCharts, function ($c) {
            return in_array($c['chart_type'], self::ATTRIBUTE_TYPES, true);
        }));

        $this->renderSoftware('control-chart/index', [
            'title'       => 'نمودارهای صفتی',
            'system'      => $system,
            'charts'      => $charts,
            'isAttribute' => true,
        ], 'quality');
    }

    /**
     * نمایش یک نمودار صفتی
     */
    public function show($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new ControlChart();
        $chart = $model->find($id);

        if (!$chart || (int) $chart['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'نمودار یافت نشد';
            return;
        }

        // 🎯 بررسی که نمودار صفتی باشه
        if (!in_array($chart['chart_type'], self::ATTRIBUTE_TYPES, true)) {
            $this->redirect(CURRENT_MODULE_URL . '?controller=control_chart&action=show&id=' . $id);
        }

        $violations = $model->decodeViolations($chart);

        $datasetModel = new Dataset();
        $dataset = $datasetModel->find($chart['dataset_id']);

        $attrData = [];
        if ($dataset) {
            $attrData = $datasetModel->getAttributeData($chart['dataset_id']);
        }

        $this->renderSoftware('control-chart/show', [
            'title'       => 'جزئیات نمودار صفتی',
            'system'      => $system,
            'chart'       => $chart,
            'dataset'     => $dataset,
            'violations'  => $violations,
            'grouped'     => [],
            'attrData'    => $attrData,
            'isAttribute' => true,
        ], 'quality');
    }

    /**
     * محاسبه نمودار صفتی
     */
    public function compute($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $datasetId = (int) $id;

        $dataset = $this->findOwnedDataset($datasetId, $systemId);
        if (!$dataset) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'دیتاست یافت نشد']);
        }

        $chartType = $dataset['chart_type'];

        // 🎯 بررسی که نوع صفتی باشه
        if (!in_array($chartType, self::ATTRIBUTE_TYPES, true)) {
            http_response_code(400);
            $this->json([
                'success' => false,
                'error'   => 'این دیتاست از نوع صفتی نیست. فقط p, np, c, u پشتیبانی می‌شوند.',
            ]);
        }

        $datasetModel = new Dataset();
        $attrData = $datasetModel->getAttributeData($datasetId);

        if (empty($attrData)) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'داده‌ای برای این دیتاست وارد نشده']);
        }

        try {
            $service = new ControlChartService();
            $result  = $service->compute($chartType, $attrData);

            $seriesForRules = $result['series'] ?? [];
            $cl    = (float) ($result['center_line'] ?? 0);
            $sigma = (float) ($result['sigma_hat']   ?? 0);

            $nelsonService = new NelsonRulesService();
            $violations = [];
            if ($sigma > 0 && !empty($seriesForRules)) {
                $violations = $nelsonService->detect($seriesForRules, $cl, $sigma);
            }

            $inControl = empty($violations) ? 1 : 0;

            $chartModel = new ControlChart();
            $chartId = $chartModel->saveForDataset($systemId, $datasetId, [
                'chart_type'        => $chartType,
                'center_line'       => $result['center_line'] ?? null,
                'ucl'               => $result['ucl']         ?? null,
                'lcl'               => $result['lcl']         ?? null,
                'sigma_hat'         => $result['sigma_hat']   ?? null,
                'nelson_violations' => $violations,
                'we_violations'     => [],
                'in_control'        => $inControl,
            ]);

            $this->json([
                'success'    => true,
                'chart_id'   => $chartId,
                'chart_type' => $chartType,
                'result'     => $result,
                'violations' => $violations,
                'in_control' => $inControl,
                'redirect'   => CURRENT_MODULE_URL . '?controller=attribute_chart&action=show&id=' . $chartId,
            ]);

        } catch (\Throwable $e) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * حذف نمودار صفتی
     */
    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new ControlChart();
        $chart = $model->find($id);

        if (!$chart || (int) $chart['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'نمودار حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=attribute_chart');
    }
}