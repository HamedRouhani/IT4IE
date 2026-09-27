<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\ParetoAnalysis;
use App\Software\Quality\Models\ParetoItem;
use App\Software\Quality\Models\CapaAction;
use App\Software\Quality\Services\ParetoService;

class ParetoController extends Controller
{
    protected $systemModel;
    protected $projectModel;
    protected $analysisModel;
    protected $itemModel;
    protected $capaModel;
    protected $service;

    public function __construct()
    {
        $this->systemModel   = new System();
        $this->projectModel  = new Project();
        $this->analysisModel = new ParetoAnalysis();
        $this->itemModel     = new ParetoItem();
        $this->capaModel     = new CapaAction();
        $this->service       = new ParetoService();
    }

    // ═══════════════════════════════════════════════════════
    // ابزارهای کمکی
    // ═══════════════════════════════════════════════════════

    protected function requireSystem()
    {
        $user = $this->currentUser();
        
        if (!$user || !isset($user['id']) || (int)$user['id'] <= 0) {
            $this->setError('لطفاً دوباره وارد شوید');
            $this->redirect(CURRENT_MODULE_URL . '?controller=auth&action=login');
            exit;
        }
        
        $system = $this->systemModel->findActiveByUser((int)$user['id']);
        if (!$system) {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
            exit;
        }
        return $system;
    }

    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function findOwnedAnalysis($id, $system)
    {
        $analysis = $this->analysisModel->find($id);
        if (!$analysis || (int)$analysis['system_id'] !== (int)$system['id']) {
            return null;
        }
        return $analysis;
    }

    // ═══════════════════════════════════════════════════════
    // 1) index — لیست تحلیل‌ها
    // ═══════════════════════════════════════════════════════
    public function index()
    {
        $system = $this->requireSystem();
        $filters = [
            'status'     => $_GET['status'] ?? '',
            'project_id' => $_GET['project_id'] ?? '',
            'search'     => trim($_GET['search'] ?? ''),
        ];

        $analyses = $this->analysisModel->findBySystem($system['id']);

        // فیلتر سمت PHP (چون تعداد کمه، بهینه‌تر از کوئری‌های پیچیده)
        if ($filters['status']) {
            $analyses = array_filter($analyses, fn($a) => $a['status'] === $filters['status']);
        }
        if ($filters['project_id']) {
            $analyses = array_filter($analyses, fn($a) => (int)$a['project_id'] === (int)$filters['project_id']);
        }
        if ($filters['search']) {
            $s = mb_strtolower($filters['search']);
            $analyses = array_filter($analyses, function ($a) use ($s) {
                return mb_strpos(mb_strtolower($a['title']), $s) !== false
                    || mb_strpos(mb_strtolower($a['problem_statement'] ?? ''), $s) !== false;
            });
        }

        $stats = $this->analysisModel->getStatsBySystem($system['id']);
        $projects = $this->projectModel->findBySystem($system['id']);

        return $this->renderSoftware('pareto/index', [
            'system'   => $system,
            'analyses' => array_values($analyses),
            'stats'    => $stats,
            'projects' => $projects,
            'filters'  => $filters,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 2) create — فرم ایجاد تحلیل جدید
    // ═══════════════════════════════════════════════════════
    public function create()
    {
        $system   = $this->requireSystem();
        $projects = $this->projectModel->findBySystem($system['id']);

        return $this->renderSoftware('pareto/form', [
            'system'   => $system,
            'projects' => $projects,
            'analysis' => null,
            'items'    => [],
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 3) store — ذخیره‌ی تحلیل جدید
    // ═══════════════════════════════════════════════════════
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        // ── اعتبارسنجی پایه
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setError('عنوان تحلیل الزامی است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=create');
        }

        // ── استخراج آیتم‌ها از فرم
        $rawItems = $this->extractItemsFromPost();

        if (empty($rawItems)) {
            $this->setError('حداقل یک دسته‌بندی با مقدار معتبر لازم است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=create');
        }

        // ── داده‌ی Analysis
        $data = [
            'system_id'         => $system['id'],
            'project_id'        => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'title'             => $title,
            'problem_statement' => trim($_POST['problem_statement'] ?? '') ?: null,
            'category_type'     => trim($_POST['category_type'] ?? '') ?: null,
            'unit'              => trim($_POST['unit'] ?? '') ?: null,
            'analysis_date'     => $_POST['analysis_date'] ?? date('Y-m-d'),
            'period_from'       => $_POST['period_from'] ?: null,
            'period_to'         => $_POST['period_to'] ?: null,
            'status'            => $_POST['status'] ?? 'draft',
            'notes'             => trim($_POST['notes'] ?? '') ?: null,
            'created_by'        => $this->currentUser()['id'],
            'threshold'         => (float)($_POST['threshold'] ?? 80),
        ];

        // ── ذخیره
        $result = $this->service->createAnalysis($data, $rawItems);

        if ($result['success']) {
            $this->setSuccess('تحلیل Pareto با موفقیت ثبت شد');

            // اگه کاربر خواسته CAPA هم تولید بشه
            if (!empty($_POST['generate_capa'])) {
                $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=suggestCapa&id=' . $result['analysis_id']);
            }

            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=show&id=' . $result['analysis_id']);
        } else {
            $this->setError('خطا در ثبت تحلیل: ' . ($result['error'] ?? ''));
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=create');
        }
    }

    // ═══════════════════════════════════════════════════════
    // 4) show — نمایش جزئیات تحلیل
    // ═══════════════════════════════════════════════════════
    public function show()
    {
        $system = $this->requireSystem();
        $id = (int)($_GET['id'] ?? 0);

        $analysis = $this->findOwnedAnalysis($id, $system);
        if (!$analysis) {
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $data = $this->service->getAnalysisWithItems($id);
        if (!$data) {
            $this->setError('خطا در بارگذاری تحلیل');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        // CAPA های مرتبط
        $capaActions = $this->capaModel->findBySource('pareto', $id);

        return $this->renderSoftware('pareto/show', [
            'system'      => $system,
            'analysis'    => $data['analysis'],
            'items'       => $data['items'],
            'chart'       => $data['chart'],
            'capaActions' => $capaActions,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 5) edit — فرم ویرایش
    // ═══════════════════════════════════════════════════════
    public function edit()
    {
        $system = $this->requireSystem();
        $id = (int)($_GET['id'] ?? 0);

        $analysis = $this->findOwnedAnalysis($id, $system);
        if (!$analysis) {
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $items    = $this->itemModel->findByAnalysis($id);
        $projects = $this->projectModel->findBySystem($system['id']);

        return $this->renderSoftware('pareto/form', [
            'system'   => $system,
            'projects' => $projects,
            'analysis' => $analysis,
            'items'    => $items,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 6) update — ذخیره‌ی ویرایش
    // ═══════════════════════════════════════════════════════
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $analysis = $this->findOwnedAnalysis($id, $system);
        if (!$analysis) {
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setError('عنوان الزامی است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=edit&id=' . $id);
        }

        $rawItems = $this->extractItemsFromPost();
        if (empty($rawItems)) {
            $this->setError('حداقل یک دسته‌بندی معتبر لازم است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=edit&id=' . $id);
        }

        $data = [
            'title'             => $title,
            'problem_statement' => trim($_POST['problem_statement'] ?? '') ?: null,
            'category_type'     => trim($_POST['category_type'] ?? '') ?: null,
            'unit'              => trim($_POST['unit'] ?? '') ?: null,
            'analysis_date'     => $_POST['analysis_date'] ?? null,
            'period_from'       => $_POST['period_from'] ?: null,
            'period_to'         => $_POST['period_to'] ?: null,
            'status'            => $_POST['status'] ?? $analysis['status'],
            'notes'             => trim($_POST['notes'] ?? '') ?: null,
            'threshold'         => (float)($_POST['threshold'] ?? 80),
        ];

        $result = $this->service->updateAnalysis($id, $data, $rawItems);

        if ($result['success']) {
            $this->setSuccess('تحلیل با موفقیت به‌روزرسانی شد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=show&id=' . $id);
        } else {
            $this->setError('خطا در به‌روزرسانی: ' . ($result['error'] ?? ''));
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=edit&id=' . $id);
        }
    }

    // ═══════════════════════════════════════════════════════
    // 7) delete — حذف تحلیل
    // ═══════════════════════════════════════════════════════
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $analysis = $this->findOwnedAnalysis($id, $system);
        if (!$analysis) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'تحلیل یافت نشد']);
            }
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $ok = $this->analysisModel->deleteWithItems($id);

        if ($this->isAjax()) {
            return $this->json([
                'success' => $ok,
                'message' => $ok ? 'تحلیل حذف شد' : 'خطا در حذف',
            ]);
        }

        $ok ? $this->setSuccess('تحلیل حذف شد') : $this->setError('خطا در حذف');
        $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
    }

    // ═══════════════════════════════════════════════════════
    // 8) preview — پیش‌نمایش زنده (AJAX)
    // ═══════════════════════════════════════════════════════
    public function preview()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method not allowed']);
        }
        $this->requireSystem();

        // AJAX با JSON input
        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $rawItems  = $payload['items'] ?? [];
        $threshold = (float)($payload['threshold'] ?? 80);

        if (empty($rawItems)) {
            return $this->json([
                'success' => false,
                'error'   => 'آیتمی برای محاسبه وجود ندارد',
            ]);
        }

        $result = $this->service->previewCalculation($rawItems, $threshold);

        return $this->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    // ═══════════════════════════════════════════════════════
    // 9) suggestCapa — پیشنهاد CAPA برای Vital Few
    // ═══════════════════════════════════════════════════════
    public function suggestCapa()
    {
        $system = $this->requireSystem();
        $id = (int)($_GET['id'] ?? 0);

        $analysis = $this->findOwnedAnalysis($id, $system);
        if (!$analysis) {
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $data = $this->service->getAnalysisWithItems($id);
        if (!$data) {
            $this->setError('خطا در بارگذاری');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        $suggestions = $this->service->suggestCapaActions($id);

        return $this->renderSoftware('pareto/suggest_capa', [
            'system'      => $system,
            'analysis'    => $data['analysis'],
            'items'       => $data['items'],
            'suggestions' => $suggestions,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 10) storeCapaFromPareto — ذخیره‌ی CAPA های تأییدشده
    // ═══════════════════════════════════════════════════════
    public function storeCapaFromPareto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $analysisId = (int)($_POST['analysis_id'] ?? 0);
        $analysis = $this->findOwnedAnalysis($analysisId, $system);
        if (!$analysis) {
            $this->setError('تحلیل یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto');
        }

        // CAPA های انتخابی کاربر
        $selected = $_POST['selected'] ?? [];
        $titles   = $_POST['title'] ?? [];
        $priorities = $_POST['priority'] ?? [];
        $dueDates = $_POST['due_date'] ?? [];
        $responsibles = $_POST['responsible_person'] ?? [];

        if (empty($selected)) {
            $this->setError('حداقل یک CAPA را انتخاب کنید');
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=suggestCapa&id=' . $analysisId);
        }

        $createdCount = 0;
        $this->capaModel->beginTransaction();
        try {
            foreach ($selected as $idx) {
                $idx = (int)$idx;
                if (!isset($titles[$idx])) {
                    continue;
                }
                $this->capaModel->create([
                    'system_id'            => $system['id'],
                    'project_id'           => $analysis['project_id'],
                    'source_type'          => 'pareto',
                    'source_id'            => $analysisId,
                    'action_type'          => 'corrective',
                    'title'                => trim($titles[$idx]),
                    'problem_description'  => 'برخاسته از تحلیل Pareto: ' . $analysis['title'],
                    'priority'             => $priorities[$idx] ?? 'high',
                    'due_date'             => $dueDates[$idx] ?? null,
                    'responsible_person'   => $responsibles[$idx] ?? null,
                    'status'               => 'open',
                    'effectiveness'        => 'pending',
                    'created_by'           => $this->currentUser()['id'],
                ]);
                $createdCount++;
            }
            $this->capaModel->commit();
        } catch (\Exception $e) {
            $this->capaModel->rollback();
            $this->setError('خطا در ثبت CAPA ها: ' . $e->getMessage());
            $this->redirect(CURRENT_MODULE_URL . '?controller=pareto&action=suggestCapa&id=' . $analysisId);
        }

        $this->setSuccess("$createdCount اقدام اصلاحی با موفقیت ثبت شد");
        $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
    }

    // ═══════════════════════════════════════════════════════
    // ابزار: استخراج آیتم‌ها از POST
    // ═══════════════════════════════════════════════════════
    protected function extractItemsFromPost()
    {
        $names = $_POST['item_name'] ?? [];
        $values = $_POST['item_value'] ?? [];
        $freqs = $_POST['item_frequency'] ?? [];
        $costs = $_POST['item_cost'] ?? [];

        $items = [];
        foreach ($names as $i => $name) {
            $name = trim($name);
            if ($name === '') continue;

            $value = isset($values[$i]) ? (float)$values[$i] : 0;
            $freq  = isset($freqs[$i]) && trim($freqs[$i]) !== '' ? (int)$freqs[$i] : null;
            $cost  = isset($costs[$i]) && trim($costs[$i]) !== '' ? (float)$costs[$i] : null;

            // ✅ فقط وقتی هر دو freq و cost واقعاً وارد شدن و value صفره
            if ($value <= 0 && $freq !== null && $freq > 0 && $cost !== null && $cost > 0) {
                $value = $freq * $cost;
            }

            // ✅ اگه value هنوز صفره، این آیتم رو رد کن (چون احتمالاً کاربر اشتباه کرده)
            if ($value <= 0) {
                continue;
            }

            $items[] = [
                'category_name' => $name,
                'value'         => $value,
                'frequency'     => $freq,
                'cost_per_unit' => $cost,
            ];
        }
        return $items;
    }
}