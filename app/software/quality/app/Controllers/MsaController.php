<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\MsaStudy;
use App\Software\Quality\Services\MsaService;

class MsaController extends Controller
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

    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new MsaStudy();
        $studies = $model->findBySystem($systemId);
        $avgGrr = $model->averagePctGrr($systemId);

        $this->renderSoftware('msa/index', [
            'title'   => 'مطالعات MSA (Gage R&R)',
            'system'  => $system,
            'studies' => $studies,
            'avgGrr'  => $avgGrr,
        ], 'quality');
    }

    public function create(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $projectModel = new Project();
        $projects = $projectModel->listForSystem($systemId);

        $this->renderSoftware('msa/form', [
            'title'    => 'ایجاد مطالعه MSA',
            'system'   => $system,
            'projects' => $projects,
            'study'    => null,
            'methods'  => MsaStudy::METHODS,
        ], 'quality');
    }

    public function store(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=msa&action=create');
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        $projectId    = (int)   ($input['project_id']    ?? 0);
        $name         = trim(   $input['name']           ?? '');
        $method       = $input['method']                 ?? 'anova';
        $numParts     = (int)   ($input['num_parts']     ?? 0);
        $numOperators = (int)   ($input['num_operators'] ?? 0);
        $numTrials    = (int)   ($input['num_trials']    ?? 0);
        $values       = $input['values']                 ?? [];

        $projectModel = new Project();
        $project = $projectModel->find($projectId);
        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'پروژه نامعتبر است']);
        }
        if ($name === '') {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'نام مطالعه الزامی است']);
        }
        if ($numParts < 2 || $numOperators < 2 || $numTrials < 2) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'حداقل ۲ قطعه، ۲ اپراتور و ۲ تکرار لازم است']);
        }
        if (empty($values)) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'داده‌ای ارسال نشد']);
        }

        $allowedMethods = ['average_range', 'anova'];
        if (!in_array($method, $allowedMethods, true)) {
            $method = 'anova';
        }

        try {
            $service = new MsaService();
            $result  = $service->computeAnova([
                'parts'     => $numParts,
                'operators' => $numOperators,
                'trials'    => $numTrials,
                'values'    => $values,
            ]);

            $model = new MsaStudy();
            $studyId = $model->createForProject($systemId, $projectId, [
                'name'          => $name,
                'method'        => $method,
                'num_parts'     => $numParts,
                'num_operators' => $numOperators,
                'num_trials'    => $numTrials,
                'data_json'     => $values,
                'ev'            => $result['ev'],
                'av'            => $result['av'],
                'grr'           => $result['grr'],
                'pv'            => $result['pv'],
                'tv'            => $result['tv'],
                'pct_ev'        => $result['pct_ev'],
                'pct_av'        => $result['pct_av'],
                'pct_grr'       => $result['pct_grr'],
                'pct_pv'        => $result['pct_pv'],
                'ndc'           => $result['ndc'],
                'verdict'       => $result['verdict'],
            ]);

            $this->json([
                'success'  => true,
                'study_id' => $studyId,
                'result'   => $result,
                'redirect' => CURRENT_MODULE_URL . '?controller=msa&action=show&id=' . $studyId,
            ]);

        } catch (\Throwable $e) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new MsaStudy();
        $study = $model->findWithProject($id);

        if (!$study || (int) $study['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'مطالعه یافت نشد';
            return;
        }

        $data = $model->decodeData($study);

        $this->renderSoftware('msa/show', [
            'title'  => 'جزئیات مطالعه MSA',
            'system' => $system,
            'study'  => $study,
            'data'   => $data,
        ], 'quality');
    }

    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new MsaStudy();
        $study = $model->find($id);

        if (!$study || (int) $study['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'مطالعه MSA حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=msa');
    }
}