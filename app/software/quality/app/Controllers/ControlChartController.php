<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Dataset;
use App\Software\Quality\Models\ControlChart;
use App\Software\Quality\Services\ControlChartService;
use App\Software\Quality\Services\NelsonRulesService;

class ControlChartController extends Controller
{
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
     * لیست نمودارهای متغیر (X̄-R, X̄-S, I-MR)
     */
    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new ControlChart();
        $allCharts = $model->findBySystemWithStats($systemId);

        // 🎯 فقط نمودارهای متغیر
        $variableTypes = ['xbar_r', 'xbar_s', 'i_mr'];
        $charts = array_values(array_filter($allCharts, function ($c) use ($variableTypes) {
            return in_array($c['chart_type'], $variableTypes, true);
        }));

        $this->renderSoftware('control-chart/index', [
            'title'       => 'نمودارهای کنترل (متغیر)',
            'system'      => $system,
            'charts'      => $charts,
            'isAttribute' => false,
        ], 'quality');
    }

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

        $violations = $model->decodeViolations($chart);

        $datasetModel = new Dataset();
        $dataset = $datasetModel->find($chart['dataset_id']);

        $grouped = [];
        $attrData = [];
        if ($dataset) {
            if ($datasetModel->isVariableChart($dataset['chart_type'])) {
                $grouped = $datasetModel->getGroupedData($chart['dataset_id']);
            } else {
                $attrData = $datasetModel->getAttributeData($chart['dataset_id']);
            }
        }

        $this->renderSoftware('control-chart/show', [
            'title'      => 'جزئیات نمودار کنترل',
            'system'     => $system,
            'chart'      => $chart,
            'dataset'    => $dataset,
            'violations' => $violations,
            'grouped'    => $grouped,
            'attrData'   => $attrData,
        ], 'quality');
    }

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
        $datasetModel = new Dataset();

        try {
            if ($datasetModel->isVariableChart($chartType)) {
                $subgroups = $datasetModel->getGroupedData($datasetId);
                if (empty($subgroups)) {
                    http_response_code(400);
                    $this->json(['success' => false, 'error' => 'داده‌ای برای این دیتاست وارد نشده']);
                }
                $data = $subgroups;
            } else {
                $attrData = $datasetModel->getAttributeData($datasetId);
                if (empty($attrData)) {
                    http_response_code(400);
                    $this->json(['success' => false, 'error' => 'داده‌ای برای این دیتاست وارد نشده']);
                }
                $data = $attrData;
            }

            $service = new ControlChartService();
            $result  = $service->compute($chartType, $data);

            $seriesForRules = $this->pickSeriesForRules($result);
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
                'r_bar'             => $result['r_bar']       ?? null,
                's_bar'             => $result['s_bar']       ?? null,
                'mr_bar'            => $result['mr_bar']      ?? null,
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
                'redirect'   => CURRENT_MODULE_URL . '?controller=control_chart&action=show&id=' . $chartId,
            ]);

        } catch (\Throwable $e) {
            http_response_code(400);
            $this->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    protected function pickSeriesForRules(array $result): array
    {
        if (!empty($result['xbar_series'])) return $result['xbar_series'];
        if (!empty($result['series']))      return $result['series'];
        return [];
    }

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
        $this->redirect(CURRENT_MODULE_URL . '?controller=control_chart');
    }
}