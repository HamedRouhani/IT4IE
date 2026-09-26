<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\Dataset;
use App\Software\Quality\Models\Measurement;

class DatasetController extends Controller
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

    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new Dataset();
        $datasets = $model->findBySystem($systemId);

        $this->renderSoftware('dataset/index', [
            'title'    => 'دیتاست‌های کیفیت',
            'system'   => $system,
            'datasets' => $datasets,
        ], 'quality');
    }

    public function create(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $projectId = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

        $projectModel = new Project();
        $projects = $projectModel->listForSystem($systemId);

        $project = null;
        if ($projectId > 0) {
            $project = $projectModel->find($projectId);
            if (!$project || (int) $project['system_id'] !== $systemId) {
                $project = null;
            }
        }

        $this->renderSoftware('dataset/form', [
            'title'       => 'ایجاد دیتاست جدید',
            'system'      => $system,
            'projects'    => $projects,
            'project'     => $project,
            'dataset'     => null,
            'chart_types' => Dataset::CHART_TYPES,
            'action'      => 'store',
        ], 'quality');
    }

    public function store(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=create');
        }

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $chartType = $_POST['chart_type'] ?? 'xbar_r';

        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        if (!$project || (int) $project['system_id'] !== $systemId) {
            $_SESSION['flash_error'] = 'پروژه نامعتبر است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=create');
        }

        if ($name === '') {
            $_SESSION['flash_error'] = 'نام دیتاست الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=create&project_id=' . $projectId);
        }

        if (!array_key_exists($chartType, Dataset::CHART_TYPES)) {
            $chartType = 'xbar_r';
        }

        $specLsl    = ($_POST['spec_lsl']    ?? '') !== '' ? (float) $_POST['spec_lsl']    : null;
        $specUsl    = ($_POST['spec_usl']    ?? '') !== '' ? (float) $_POST['spec_usl']    : null;
        $specTarget = ($_POST['spec_target'] ?? '') !== '' ? (float) $_POST['spec_target'] : null;
        $subgroupSize = isset($_POST['subgroup_size']) && $_POST['subgroup_size'] !== ''
            ? (int) $_POST['subgroup_size'] : null;

        $model = new Dataset();
        $datasetId = $model->createForProject($systemId, $projectId, [
            'name'          => $name,
            'chart_type'    => $chartType,
            'subgroup_size' => $subgroupSize,
            'spec_lsl'      => $specLsl,
            'spec_usl'      => $specUsl,
            'spec_target'   => $specTarget,
            'notes'         => trim($_POST['notes'] ?? '') ?: null,
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success'    => true,
                'dataset_id' => $datasetId,
                'redirect'   => CURRENT_MODULE_URL . '?controller=dataset&action=data&id=' . $datasetId,
            ]);
        }

        $_SESSION['flash_success'] = 'دیتاست ایجاد شد. اکنون داده‌ها را وارد کنید.';
        $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=data&id=' . $datasetId);
    }

    public function edit($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            echo 'دیتاست یافت نشد';
            return;
        }

        $projectModel = new Project();
        $projects = $projectModel->listForSystem($systemId);

        $this->renderSoftware('dataset/form', [
            'title'       => 'ویرایش دیتاست',
            'system'      => $system,
            'projects'    => $projects,
            'project'     => null,
            'dataset'     => $dataset,
            'chart_types' => Dataset::CHART_TYPES,
            'action'      => 'update',
        ], 'quality');
    }

    public function update($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=edit&id=' . $id);
        }

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            echo 'دیتاست یافت نشد';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $_SESSION['flash_error'] = 'نام دیتاست الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=dataset&action=edit&id=' . $id);
        }

        $chartType = $_POST['chart_type'] ?? $dataset['chart_type'];
        if (!array_key_exists($chartType, Dataset::CHART_TYPES)) {
            $chartType = $dataset['chart_type'];
        }

        $specLsl    = ($_POST['spec_lsl']    ?? '') !== '' ? (float) $_POST['spec_lsl']    : null;
        $specUsl    = ($_POST['spec_usl']    ?? '') !== '' ? (float) $_POST['spec_usl']    : null;
        $specTarget = ($_POST['spec_target'] ?? '') !== '' ? (float) $_POST['spec_target'] : null;
        $subgroupSize = isset($_POST['subgroup_size']) && $_POST['subgroup_size'] !== ''
            ? (int) $_POST['subgroup_size'] : null;

        $model = new Dataset();
        $model->update($id, [
            'name'          => $name,
            'chart_type'    => $chartType,
            'subgroup_size' => $subgroupSize,
            'spec_lsl'      => $specLsl,
            'spec_usl'      => $specUsl,
            'spec_target'   => $specTarget,
            'notes'         => trim($_POST['notes'] ?? '') ?: null,
        ]);

        $_SESSION['flash_success'] = 'دیتاست به‌روزرسانی شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=dataset');
    }

    public function data($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            echo 'دیتاست یافت نشد';
            return;
        }

        $model = new Dataset();
        $measurements = $model->getMeasurements($id);
        $grouped      = $model->getGroupedData($id);
        $attrData     = $model->getAttributeData($id);

        $this->renderSoftware('dataset/data', [
            'title'        => 'داده‌های دیتاست',
            'system'       => $system,
            'dataset'      => $dataset,
            'measurements' => $measurements,
            'grouped'      => $grouped,
            'attrData'     => $attrData,
            'isVariable'   => $model->isVariableChart($dataset['chart_type']),
            'isAttribute'  => $model->isAttributeChart($dataset['chart_type']),
        ], 'quality');
    }

    public function saveData($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'دیتاست یافت نشد']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            $this->json(['success' => false, 'error' => 'متد نامعتبر']);
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!$input) {
            $input = $_POST;
        }

        $rows = $input['rows'] ?? [];

        if (empty($rows)) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'داده‌ای ارسال نشد']);
        }

        $cleanRows = [];
        foreach ($rows as $r) {
            $cleanRows[] = [
                'subgroup_no'  => (int)   ($r['subgroup_no']  ?? 1),
                'sample_no'    => (int)   ($r['sample_no']    ?? 1),
                'value'        => (float) ($r['value']        ?? 0),
                'is_defective' => (int)   ($r['is_defective'] ?? 0),
                'defect_count' => (int)   ($r['defect_count'] ?? 0),
                'measured_at'  => $r['measured_at'] ?? null,
            ];
        }

        $model = new Dataset();
        $inserted = $model->bulkInsertMeasurements($id, $systemId, $cleanRows);

        $this->json([
            'success'  => true,
            'inserted' => $inserted,
        ]);
    }

    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'دیتاست یافت نشد']);
        }

        $model = new Dataset();
        $model->deleteWithDependencies($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'دیتاست حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=dataset');
    }

    public function stats($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $dataset = $this->findOwnedDataset($id, $systemId);
        if (!$dataset) {
            http_response_code(404);
            $this->json(['success' => false, 'error' => 'دیتاست یافت نشد']);
        }

        $measurementModel = new Measurement();
        $stats = $measurementModel->getQuickStats($id);

        $this->json([
            'success' => true,
            'stats'   => $stats,
        ]);
    }
}