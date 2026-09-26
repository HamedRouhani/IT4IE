<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Dataset;
use App\Software\Quality\Models\CapabilityStudy;
use App\Software\Quality\Services\CapabilityService;

class CapabilityController extends Controller
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

        $model = new CapabilityStudy();
        $studies = $model->findBySystemWithStats($systemId);
        $avgCpk  = $model->averageCpk($systemId);

        $this->renderSoftware('capability/index', [
            'title'   => 'تحلیل قابلیت فرآیند',
            'system'  => $system,
            'studies' => $studies,
            'avgCpk'  => $avgCpk,
        ], 'quality');
    }

    public function show($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new CapabilityStudy();
        $study = $model->find($id);

        if (!$study || (int) $study['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'مطالعه یافت نشد';
            return;
        }

        $datasetModel = new Dataset();
        $dataset = $datasetModel->find($study['dataset_id']);

        $ppm = null;
        if ($study['mean'] !== null && $study['std_overall'] !== null
            && $study['lsl'] !== null && $study['usl'] !== null) {
            $service = new CapabilityService();
            $ppm = $service->estimatePpm(
                (float) $study['mean'],
                (float) $study['std_overall'],
                (float) $study['lsl'],
                (float) $study['usl']
            );
        }

        $this->renderSoftware('capability/show', [
            'title'   => 'جزئیات قابلیت فرآیند',
            'system'  => $system,
            'study'   => $study,
            'dataset' => $dataset,
            'ppm'     => $ppm,
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

        $datasetModel = new Dataset();
        if (!$datasetModel->isVariableChart($dataset['chart_type'])) {
            http_response_code(400);
            $this->json([
                'success' => false,
                'error'   => 'تحلیل قابلیت فقط برای نمودارهای متغیر (X̄-R, X̄-S, I-MR) معنی‌دار است.',
            ]);
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        $lsl = $input['lsl'] ?? $dataset['spec_lsl'];
        $usl = $input['usl'] ?? $dataset['spec_usl'];
        $target = $input['target'] ?? $dataset['spec_target'];

        if ($lsl === null || $lsl === '' || $usl === null || $usl === '') {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'LSL و USL الزامی هستند']);
        }

        $lsl = (float) $lsl;
        $usl = (float) $usl;
        $target = ($target !== null && $target !== '') ? (float) $target : null;

        if ($usl <= $lsl) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'USL باید بزرگ‌تر از LSL باشد']);
        }

        try {
            $subgroups = $datasetModel->getGroupedData($datasetId);
            if (empty($subgroups)) {
                http_response_code(400);
                $this->json(['success' => false, 'error' => 'داده‌ای وارد نشده']);
            }

            $service = new CapabilityService();
            $result = $service->compute($subgroups, $lsl, $usl, $target);

            $ppm = $service->estimatePpm(
                (float) $result['mean'],
                (float) $result['std_overall'],
                $lsl,
                $usl
            );

            $capModel = new CapabilityStudy();
            $studyId = $capModel->saveForDataset($systemId, $datasetId, [
                'lsl'         => $result['lsl'],
                'usl'         => $result['usl'],
                'target'      => $result['target'],
                'mean'        => $result['mean'],
                'std_within'  => $result['std_within'],
                'std_overall' => $result['std_overall'],
                'cp'          => $result['cp'],
                'cpk'         => $result['cpk'],
                'pp'          => $result['pp'],
                'ppk'         => $result['ppk'],
                'cpm'         => $result['cpm'],
                'cpu'         => $result['cpu'],
                'cpl'         => $result['cpl'],
            ]);

            $this->json([
                'success'  => true,
                'study_id' => $studyId,
                'result'   => $result,
                'ppm'      => $ppm,
                'verdict'  => CapabilityStudy::verdict($result['cpk']),
                'redirect' => CURRENT_MODULE_URL . '?controller=capability&action=show&id=' . $studyId,
            ]);

        } catch (\Throwable $e) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new CapabilityStudy();
        $study = $model->find($id);

        if (!$study || (int) $study['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'مطالعه حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=capability');
    }
}