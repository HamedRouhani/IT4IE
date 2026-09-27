<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\CapaAction;
use App\Software\Quality\Models\ParetoAnalysis;

class CapaController extends Controller
{
    protected $systemModel;
    protected $projectModel;
    protected $capaModel;
    protected $paretoModel;

    public function __construct()
    {
        $this->systemModel  = new System();
        $this->projectModel = new Project();
        $this->capaModel    = new CapaAction();
        $this->paretoModel  = new ParetoAnalysis();
    }

    // ═══════════════════════════════════════════════════════
    protected function requireSystem()
    {
        $system = $this->systemModel->findActiveByUser($this->currentUser()['id']);
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

    protected function findOwnedCapa($id, $system)
    {
        $capa = $this->capaModel->find($id);
        if (!$capa || (int)$capa['system_id'] !== (int)$system['id']) {
            return null;
        }
        return $capa;
    }

    // ═══════════════════════════════════════════════════════
    // 1) index — لیست CAPA ها
    // ═══════════════════════════════════════════════════════
    public function index()
    {
        $system = $this->requireSystem();

        $filters = [
            'status'       => $_GET['status'] ?? '',
            'priority'     => $_GET['priority'] ?? '',
            'source_type'  => $_GET['source_type'] ?? '',
            'project_id'   => $_GET['project_id'] ?? '',
            'search'       => trim($_GET['search'] ?? ''),
            'view'         => $_GET['view'] ?? 'all', // all | overdue | active | critical
        ];

        // انتخاب کوئری بر اساس view
        switch ($filters['view']) {
            case 'overdue':
                $capas = $this->capaModel->findOverdue($system['id']);
                break;
            case 'active':
                $capas = $this->capaModel->findActive($system['id']);
                break;
            case 'critical':
                $capas = $this->capaModel->findBySystem($system['id']);
                $capas = array_filter($capas, fn($c) => $c['priority'] === 'critical');
                break;
            default:
                $capas = $this->capaModel->findBySystem($system['id']);
        }

        // فیلترهای اضافی
        if ($filters['status']) {
            $capas = array_filter($capas, fn($c) => $c['status'] === $filters['status']);
        }
        if ($filters['priority']) {
            $capas = array_filter($capas, fn($c) => $c['priority'] === $filters['priority']);
        }
        if ($filters['source_type']) {
            $capas = array_filter($capas, fn($c) => $c['source_type'] === $filters['source_type']);
        }
        if ($filters['project_id']) {
            $capas = array_filter($capas, fn($c) => (int)$c['project_id'] === (int)$filters['project_id']);
        }
        if ($filters['search']) {
            $s = mb_strtolower($filters['search']);
            $capas = array_filter($capas, function ($c) use ($s) {
                return mb_strpos(mb_strtolower($c['title']), $s) !== false
                    || mb_strpos(mb_strtolower($c['problem_description'] ?? ''), $s) !== false;
            });
        }

        $stats = $this->capaModel->getDashboardStats($system['id']);
        $projects = $this->projectModel->findBySystem($system['id']);

        return $this->renderSoftware('capa/index', [
            'system'   => $system,
            'capas'    => array_values($capas),
            'stats'    => $stats,
            'projects' => $projects,
            'filters'  => $filters,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 2) create — فرم ایجاد CAPA دستی
    // ═══════════════════════════════════════════════════════
    public function create()
    {
        $system   = $this->requireSystem();
        $projects = $this->projectModel->findBySystem($system['id']);

        $sourceType = $_GET['source_type'] ?? null;
        $sourceId   = isset($_GET['source_id']) ? (int)$_GET['source_id'] : null;

        return $this->renderSoftware('capa/form', [
            'system'     => $system,
            'projects'   => $projects,
            'capa'       => null,
            'sourceType' => $sourceType,
            'sourceId'   => $sourceId,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 3) store
    // ═══════════════════════════════════════════════════════
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setError('عنوان الزامی است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=create');
        }

        $id = $this->capaModel->create([
            'system_id'            => $system['id'],
            'project_id'           => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'source_type'          => $_POST['source_type'] ?? 'other',
            'source_id'            => !empty($_POST['source_id']) ? (int)$_POST['source_id'] : null,
            'action_type'          => $_POST['action_type'] ?? 'corrective',
            'title'                => $title,
            'problem_description'  => trim($_POST['problem_description'] ?? '') ?: null,
            'root_cause'           => trim($_POST['root_cause'] ?? '') ?: null,
            'root_cause_method'    => $_POST['root_cause_method'] ?: null,
            'action_plan'          => trim($_POST['action_plan'] ?? '') ?: null,
            'responsible_person'   => trim($_POST['responsible_person'] ?? '') ?: null,
            'department'           => trim($_POST['department'] ?? '') ?: null,
            'priority'             => $_POST['priority'] ?? 'medium',
            'due_date'             => $_POST['due_date'] ?: null,
            'status'               => $_POST['status'] ?? 'open',
            'before_value'         => $_POST['before_value'] !== '' ? (float)$_POST['before_value'] : null,
            'estimated_cost'       => $_POST['estimated_cost'] !== '' ? (float)$_POST['estimated_cost'] : null,
            'created_by'           => $this->currentUser()['id'],
        ]);

        if ($id) {
            $this->setSuccess('اقدام اصلاحی با موفقیت ثبت شد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=show&id=' . $id);
        } else {
            $this->setError('خطا در ثبت');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=create');
        }
    }

    // ═══════════════════════════════════════════════════════
    // 4) show
    // ═══════════════════════════════════════════════════════
    public function show()
    {
        $system = $this->requireSystem();
        $id = (int)($_GET['id'] ?? 0);

        $capa = $this->findOwnedCapa($id, $system);
        if (!$capa) {
            $this->setError('اقدام یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }

        // اطلاعات منبع (اگه Pareto باشه)
        $sourceInfo = null;
        if ($capa['source_type'] === 'pareto' && $capa['source_id']) {
            $sourceInfo = $this->paretoModel->find($capa['source_id']);
        }

        return $this->renderSoftware('capa/show', [
            'system'     => $system,
            'capa'       => $capa,
            'sourceInfo' => $sourceInfo,
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 5) edit
    // ═══════════════════════════════════════════════════════
    public function edit()
    {
        $system = $this->requireSystem();
        $id = (int)($_GET['id'] ?? 0);

        $capa = $this->findOwnedCapa($id, $system);
        if (!$capa) {
            $this->setError('اقدام یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }

        $projects = $this->projectModel->findBySystem($system['id']);

        return $this->renderSoftware('capa/form', [
            'system'     => $system,
            'projects'   => $projects,
            'capa'       => $capa,
            'sourceType' => $capa['source_type'],
            'sourceId'   => $capa['source_id'],
        ], 'quality');
    }

    // ═══════════════════════════════════════════════════════
    // 6) update
    // ═══════════════════════════════════════════════════════
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $capa = $this->findOwnedCapa($id, $system);
        if (!$capa) {
            $this->setError('اقدام یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->setError('عنوان الزامی است');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=edit&id=' . $id);
        }

        $data = [
            'project_id'           => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'source_type'          => $_POST['source_type'] ?? $capa['source_type'],
            'source_id'            => !empty($_POST['source_id']) ? (int)$_POST['source_id'] : null,
            'action_type'          => $_POST['action_type'] ?? $capa['action_type'],
            'title'                => $title,
            'problem_description'  => trim($_POST['problem_description'] ?? '') ?: null,
            'root_cause'           => trim($_POST['root_cause'] ?? '') ?: null,
            'root_cause_method'    => $_POST['root_cause_method'] ?: null,
            'action_plan'          => trim($_POST['action_plan'] ?? '') ?: null,
            'responsible_person'   => trim($_POST['responsible_person'] ?? '') ?: null,
            'department'           => trim($_POST['department'] ?? '') ?: null,
            'priority'             => $_POST['priority'] ?? $capa['priority'],
            'due_date'             => $_POST['due_date'] ?: null,
            'status'               => $_POST['status'] ?? $capa['status'],
            'before_value'         => $_POST['before_value'] !== '' ? (float)$_POST['before_value'] : null,
            'after_value'          => $_POST['after_value'] !== '' ? (float)$_POST['after_value'] : null,
            'estimated_cost'       => $_POST['estimated_cost'] !== '' ? (float)$_POST['estimated_cost'] : null,
            'actual_cost'          => $_POST['actual_cost'] !== '' ? (float)$_POST['actual_cost'] : null,
            'effectiveness_notes'  => trim($_POST['effectiveness_notes'] ?? '') ?: null,
        ];

        $ok = $this->capaModel->update($id, $data);

        // اگه after_value داده شده، اثربخشی رو محاسبه کن
        if ($ok && !empty($data['after_value'])) {
            $this->capaModel->calculateEffectiveness($id);
        }

        if ($ok) {
            $this->setSuccess('اقدام با موفقیت به‌روزرسانی شد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=show&id=' . $id);
        } else {
            $this->setError('خطا در به‌روزرسانی');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa&action=edit&id=' . $id);
        }
    }

    // ═══════════════════════════════════════════════════════
    // 7) quickStatus — تغییر سریع وضعیت (AJAX)
    // ═══════════════════════════════════════════════════════
    public function quickStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method not allowed']);
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $capa = $this->capaModel->find($id);
        if (!$capa || (int)$capa['system_id'] !== (int)$system['id']) {
            return $this->json(['success' => false, 'error' => 'CAPA یافت نشد']);
        }

        $valid = ['open', 'in_progress', 'implemented', 'verified', 'closed', 'cancelled'];
        if (!in_array($status, $valid)) {
            return $this->json(['success' => false, 'error' => 'وضعیت نامعتبر']);
        }

        // ✅ منطق گردش کار: تاریخ‌ها رو خودکار پر کن
        $updateData = ['status' => $status];
        
        if ($status === 'implemented' && empty($capa['implemented_date'])) {
            $updateData['implemented_date'] = date('Y-m-d');
        }
        if ($status === 'verified' && empty($capa['verification_date'])) {
            $updateData['verification_date'] = date('Y-m-d');
        }
        
        $this->capaModel->update($id, $updateData);
        
        return $this->json(['success' => true, 'message' => 'وضعیت به‌روزرسانی شد']);
    }

    // ═══════════════════════════════════════════════════════
    // 8) delete
    // ═══════════════════════════════════════════════════════
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }
        $this->verifyCsrf();
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $capa = $this->findOwnedCapa($id, $system);
        if (!$capa) {
            if ($this->isAjax()) {
                return $this->json(['success' => false, 'error' => 'اقدام یافت نشد']);
            }
            $this->setError('اقدام یافت نشد');
            $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
        }

        $ok = $this->capaModel->delete($id);

        if ($this->isAjax()) {
            return $this->json([
                'success' => $ok,
                'message' => $ok ? 'اقدام حذف شد' : 'خطا',
            ]);
        }

        $ok ? $this->setSuccess('اقدام حذف شد') : $this->setError('خطا در حذف');
        $this->redirect(CURRENT_MODULE_URL . '?controller=capa');
    }

    // ═══════════════════════════════════════════════════════
    // 9) calculateEffectiveness — محاسبه اثربخشی (AJAX)
    // ═══════════════════════════════════════════════════════
    public function calculateEffectiveness()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['success' => false, 'error' => 'Method not allowed']);
        }
        $system = $this->requireSystem();

        $id = (int)($_POST['id'] ?? 0);
        $capa = $this->findOwnedCapa($id, $system);
        if (!$capa) {
            return $this->json(['success' => false, 'error' => 'اقدام یافت نشد']);
        }

        $result = $this->capaModel->calculateEffectiveness($id);
        if (!$result) {
            return $this->json([
                'success' => false,
                'error'   => 'برای محاسبه اثربخشی، قبل و بعد از اقدام لازم است',
            ]);
        }

        return $this->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}