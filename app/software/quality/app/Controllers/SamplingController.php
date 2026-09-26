<?php
/**
 * ============================================================
 * Quality Analyzer — SamplingController
 * ============================================================
 * مسیر: app/software/quality/app/Controllers/SamplingController.php
 * ============================================================
 */

namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\SamplingPlan;
use App\Software\Quality\Services\SamplingService;

class SamplingController extends Controller
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

    /**
     * لیست طرح‌های نمونه‌گیری
     */
    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new SamplingPlan();
        $plans = $model->findBySystem($systemId);

        $this->renderSoftware('sampling/index', [
            'title'  => 'طرح‌های نمونه‌گیری',
            'system' => $system,
            'plans'  => $plans,
        ], 'quality');
    }

    /**
     * فرم ایجاد طرح
     */
    public function create(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $projectModel = new Project();
        $projects = $projectModel->listForSystem($systemId);

        $this->renderSoftware('sampling/form', [
            'title'     => 'ایجاد طرح نمونه‌گیری',
            'system'    => $system,
            'projects'  => $projects,
            'plan'      => null,
            'planTypes' => SamplingPlan::PLAN_TYPES,
            'action'    => 'store',
        ], 'quality');
    }

    /**
     * ذخیره طرح + محاسبه
     */
    public function store(): void
    {
        $system = $this->requireSystem();
        $userId = (int) $_SESSION['user_id'];
        $systemId = (int) $system['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=sampling&action=create');
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        $name         = trim($input['name'] ?? '');
        $projectId    = (int) ($input['project_id'] ?? 0) ?: null;
        $planType     = $input['plan_type'] ?? 'single';
        $aql          = (float) ($input['aql'] ?? 0);
        $ltpd         = (float) ($input['ltpd'] ?? 0);
        $alpha        = (float) ($input['alpha'] ?? 0.05);
        $beta         = (float) ($input['beta'] ?? 0.10);
        $lotSize      = (int) ($input['lot_size'] ?? 0);
        $sampleSize   = (int) ($input['sample_size'] ?? 0);
        $acceptNumber = isset($input['accept_number']) ? (int) $input['accept_number'] : null;
        $description  = trim($input['description'] ?? '');
        $notes        = trim($input['notes'] ?? '');

        // اعتبارسنجی
        if ($name === '') {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'نام طرح الزامی است']);
        }
        if ($lotSize <= 0) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'حجم دسته باید بزرگ‌تر از صفر باشد']);
        }
        if ($aql <= 0 || $ltpd <= 0) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'AQL و LTPD باید بزرگ‌تر از صفر باشند']);
        }
        if ($ltpd <= $aql) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'LTPD باید بزرگ‌تر از AQL باشد']);
        }
        if (!array_key_exists($planType, SamplingPlan::PLAN_TYPES)) {
            $planType = 'single';
        }

        try {
            $service = new SamplingService();

            // 🎯 حالت ۱: n و c توسط کاربر داده شده
            if ($sampleSize > 0 && $acceptNumber !== null) {
                $n = $sampleSize;
                $c = $acceptNumber;
            } else {
                // 🎯 حالت ۲: محاسبه‌ی خودکار
                $found = $service->findPlan($aql, $ltpd, $alpha, $beta);
                if (isset($found['error'])) {
                    http_response_code(400);
                    $this->json(['success' => false, 'error' => $found['error']]);
                }
                $n = $found['sample_size'];
                $c = $found['accept_number'];
            }

            // محاسبات
            $risks  = $service->computeRisks($n, $c, $aql, $ltpd);
            $ocCurve = $service->computeOcCurve($n, $c, 51);
            $aoql   = $service->computeAoql($n, $c, $lotSize);
            $ati    = $service->computeAti($n, $c, $lotSize, $aql / 100);

            // ذخیره
            $model = new SamplingPlan();
            $planId = $model->createForSystem($systemId, $userId, [
                'project_id'     => $projectId,
                'name'           => $name,
                'description'    => $description ?: null,
                'plan_type'      => $planType,
                'aql'            => $aql,
                'ltpd'           => $ltpd,
                'alpha'          => $alpha,
                'beta'           => $beta,
                'lot_size'       => $lotSize,
                'sample_size'    => $n,
                'accept_number'  => $c,
                'reject_number'  => $c + 1,
                'oc_curve'       => $ocCurve,
                'producer_risk'  => $risks['producer_risk'],
                'consumer_risk'  => $risks['consumer_risk'],
                'aoql'           => $aoql,
                'at_i'           => $ati,
                'notes'          => $notes ?: null,
            ]);

            $this->json([
                'success'  => true,
                'plan_id'  => $planId,
                'result'   => [
                    'sample_size'   => $n,
                    'accept_number' => $c,
                    'reject_number' => $c + 1,
                    'producer_risk' => $risks['producer_risk'],
                    'consumer_risk' => $risks['consumer_risk'],
                    'aoql'          => $aoql,
                    'ati'           => $ati,
                ],
                'redirect' => CURRENT_MODULE_URL . '?controller=sampling&action=show&id=' . $planId,
            ]);

        } catch (\Throwable $e) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * نمایش یک طرح
     */
    public function show($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new SamplingPlan();
        $plan = $model->find($id);

        if (!$plan || (int) $plan['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'طرح یافت نشد';
            return;
        }

        $ocCurve = $model->decodeOcCurve($plan);

        $this->renderSoftware('sampling/show', [
            'title'   => 'جزئیات طرح نمونه‌گیری',
            'system'  => $system,
            'plan'    => $plan,
            'ocCurve' => $ocCurve,
        ], 'quality');
    }

    /**
     * حذف طرح
     */
    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new SamplingPlan();
        $plan = $model->find($id);

        if (!$plan || (int) $plan['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'طرح حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=sampling');
    }

    /**
     * پیشنهاد طرح خودکار (AJAX)
     */
    public function suggest(): void
    {
        $system = $this->requireSystem();

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?: $_POST;

        $aql     = (float) ($input['aql'] ?? 0);
        $ltpd    = (float) ($input['ltpd'] ?? 0);
        $alpha   = (float) ($input['alpha'] ?? 0.05);
        $beta    = (float) ($input['beta'] ?? 0.10);

        if ($aql <= 0 || $ltpd <= 0 || $ltpd <= $aql) {
            http_response_code(400);
            $this->json(['success' => false, 'error' => 'AQL و LTPD نامعتبر']);
        }

        $service = new SamplingService();
        $result = $service->findPlan($aql, $ltpd, $alpha, $beta);

        $this->json([
            'success' => !isset($result['error']),
            'result'  => $result,
        ]);
    }
}