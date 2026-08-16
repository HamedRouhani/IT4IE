<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class AuditPlanController extends Controller
{
    public function __construct()
    {
        parent::__construct(); // فراخوانی سازنده پدر برای مقداردهی $this->db
    }

    /**
     * لیست برنامه‌های ممیزی
     */
    public function index()
    {
        $this->requireAuth();

        $plans = $this->db->query("
            SELECT ap.*, a.full_name as lead_auditor_name 
            FROM {$this->prefix}audit_plans ap
            LEFT JOIN {$this->prefix}auditors a ON ap.lead_auditor_id = a.id
            ORDER BY ap.created_at DESC
        ")->fetchAll();
        
        $this->view('audit-plans/index', [
            'pageTitle' => 'برنامه‌های ممیزی',
            'currentPage' => 'auditplans',
            'plans' => $plans
        ]);
    }

    /**
     * نمایش فرم ایجاد برنامه ممیزی جدید
     */
    public function create()
    {
        $programId = (int)($_GET['program_id'] ?? 0);
        
        // دریافت لیست برنامه‌های سالانه فعال
        $programModel = new \App\Software\Qms\Models\AuditProgramModel();
        $auditPrograms = $programModel->list(null, $_SESSION['user_id']);
        
        // دریافت واحدها و سرممیزها
        $departments = $this->departmentModel->list();
        $auditors = $this->auditorModel->list();
        
        $programs = $this->db->query("
            SELECT p.id, p.title, p.year,
                   MAX(CASE ra.risk_level 
                       WHEN 'critical' THEN 4 WHEN 'high' THEN 3 
                       WHEN 'medium' THEN 2 WHEN 'low' THEN 1 ELSE 0 END) AS max_risk
            FROM {$this->prefix}audit_programs p
            LEFT JOIN {$this->prefix}process_risk_assessment ra ON ra.program_id = p.id
            GROUP BY p.id
            ORDER BY p.year DESC, p.id DESC
        ")->fetchAll();

        $this->view('audit-plans/create', [
            'pageTitle'     => 'ایجاد برنامه ممیزی جدید',
            'currentPage'   => 'auditplans',
            'departments'   => $departments,
            'auditors'      => $auditors,
            'programs'      => $programs,
            'preProgramId'  => (int)($_GET['program_id'] ?? 0),
        ]);
    }

    /**
     * ذخیره برنامه ممیزی
     */
        public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditplans');
            return;
        }

        // دریافت مقادیر
        $title = trim($_POST['title'] ?? '');
        $startDateRaw = $_POST['start_date'] ?? '';
        $endDateRaw = $_POST['end_date'] ?? '';

        // تبدیل اعداد فارسی به انگلیسی و استانداردسازی تاریخ
        $startDate = $this->normalizeDate($startDateRaw);
        $endDate = $this->normalizeDate($endDateRaw);

        // دیباگ: لاگ مقادیر دریافتی
        error_log("QMS Audit Plan - Title: {$title}, Start: {$startDateRaw} -> {$startDate}, End: {$endDateRaw} -> {$endDate}");

        // اعتبارسنجی
        if (empty($title)) {
            $this->flashError('عنوان برنامه الزامی است.');
            $this->redirect('auditplans&action=create');
            return;
        }

        if (empty($startDate) || empty($endDate)) {
            $this->flashError('تاریخ شروع و پایان الزامی است. لطفاً از تقویم شمسی استفاده کنید.');
            $this->redirect('auditplans&action=create');
            return;
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            $this->flashError('تاریخ شروع نمی‌تواند بعد از تاریخ پایان باشد.');
            $this->redirect('auditplans&action=create');
            return;
        }

        // ذخیره در دیتابیس
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_plans 
            (user_id, audit_program_id, title, audit_type, scope, objectives, criteria, start_date, end_date, 
             status, priority, lead_auditor_id, departments, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $this->currentUserId,
            !empty($_POST['audit_program_id']) ? (int)$_POST['audit_program_id'] : null,
            $title,
            $_POST['audit_type'] ?? 'internal',
            trim($_POST['scope'] ?? ''),
            trim($_POST['objectives'] ?? ''),
            trim($_POST['criteria'] ?? ''),
            $startDate,
            $endDate,
            $_POST['status'] ?? 'draft',
            $_POST['priority'] ?? 'medium',
            !empty($_POST['lead_auditor_id']) ? (int)$_POST['lead_auditor_id'] : null,
            json_encode($_POST['departments'] ?? []),
            trim($_POST['notes'] ?? '')
        ]);

        if ($result) {
            $planId = $this->db->lastInsertId();
            $this->logActivity('create_audit_plan', 'audit_plan', $planId);
            $this->flashSuccess('برنامه ممیزی با موفقیت ایجاد شد.');
            $this->redirect('auditplans&action=show&id=' . $planId);
        } else {
            $this->flashError('خطا در ایجاد برنامه ممیزی.');
            $this->redirect('auditplans&action=create');
        }
    }

    /**
     * تبدیل تاریخ به فرمت استاندارد MySQL
     */
    private function normalizeDate($date)
    {
        if (empty($date)) return null;
        
        // تبدیل اعداد فارسی به انگلیسی
        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $english = ['0','1','2','3','4','5','6','7','8','9'];
        $date = str_replace($persian, $english, $date);
        
        // تبدیل / به -
        $date = str_replace('/', '-', $date);
        
        // اعتبارسنجی فرمت
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        return null;
    }

    /**
     * نمایش جزئیات یک برنامه ممیزی
     */
    public function show($id)
    {
        $this->requireAuth();

        // ۱) دریافت اطلاعات برنامه ممیزی + نام سرممیز
        $stmt = $this->db->prepare("
            SELECT p.*, a.full_name AS lead_auditor_name
            FROM {$this->prefix}audit_plans p
            LEFT JOIN {$this->prefix}auditors a ON p.lead_auditor_id = a.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        // ۲) دریافت جلسات ممیزی (بدون join اشتباه با plan_items)
        $stmt = $this->db->prepare("
            SELECT s.*, 
                d.name_fa AS department_name, 
                a.full_name AS auditor_name
            FROM {$this->prefix}audit_sessions s
            LEFT JOIN {$this->prefix}departments d ON s.department_id = d.id
            LEFT JOIN {$this->prefix}auditors   a ON s.assigned_auditor_id = a.id
            WHERE s.audit_plan_id = ?
            ORDER BY s.session_number ASC
        ");
        $stmt->execute([$id]);
        $sessions = $stmt->fetchAll();

        // ۳) آمار جلسات
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN overall_status = 'completed' THEN 1 ELSE 0 END) AS completed
            FROM {$this->prefix}audit_sessions
            WHERE audit_plan_id = ?
        ");
        $stmt->execute([$id]);
        $sesStats = $stmt->fetch();

        // ۴) آمار شواهد (عدم انطباق‌ها)
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN e.finding_type = 'minor_nc' THEN 1 ELSE 0 END) AS minor_nc,
                SUM(CASE WHEN e.finding_type = 'major_nc' THEN 1 ELSE 0 END) AS major_nc,
                SUM(CASE WHEN e.finding_type = 'conformity' THEN 1 ELSE 0 END) AS conformity,
                SUM(CASE WHEN e.finding_type = 'ofI' THEN 1 ELSE 0 END) AS ofi
            FROM {$this->prefix}audit_evidences e
            INNER JOIN {$this->prefix}audit_sessions s ON e.session_id = s.id
            WHERE s.audit_plan_id = ?
        ");
        $stmt->execute([$id]);
        $evStats = $stmt->fetch();

        $statistics = [
            'sessions'  => $sesStats,
            'evidences' => $evStats,
        ];

        // ۵) دریافت تیم ممیزی (جدول جدید audit_team که در قدم ۱ ساختیم)
        $team = [];
        try {
            $teamModel = new \App\Software\Qms\Models\AuditorModel();
            $team = $teamModel->getTeam($id);
        } catch (\Exception $e) {
            // اگر جدول هنوز ساخته نشده باشد، خطا را نادیده می‌گیریم
        }

        $this->view('audit-plans/view', [
            'pageTitle'   => $plan['title'],
            'currentPage' => 'auditplans',
            'plan'        => $plan,
            'sessions'    => $sessions,
            'statistics'  => $statistics,
            'team'        => $team,
        ]);
    }

    /**
     * نمایش فرم ویرایش برنامه ممیزی
     */
    public function edit($id)
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();
        $programs = $this->db->query("SELECT id, title, year FROM {$this->prefix}audit_programs ORDER BY year DESC, id DESC")->fetchAll();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        if ($_SESSION['user_role'] !== 'admin' && $plan['user_id'] != $this->currentUserId) {
            $this->flashError('شما مجوز ویرایش این برنامه را ندارید.');
            $this->redirect('auditplans');
            return;
        }

        $departments = $this->db->query("SELECT id, name_fa FROM {$this->prefix}departments WHERE is_active = 1 ORDER BY name_fa")->fetchAll();
        $auditors = $this->db->query("SELECT id, full_name FROM {$this->prefix}auditors WHERE is_active = 1 ORDER BY full_name")->fetchAll();

        $this->view('audit-plans/edit', [
            'pageTitle' => 'ویرایش برنامه ممیزی',
            'currentPage' => 'auditplans',
            'plan' => $plan,
            'programs' => $programs,
            'departments' => $departments,
            'auditors' => $auditors
        ]);
    }

    /**
     * به‌روزرسانی برنامه ممیزی
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditplans');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        if ($_SESSION['user_role'] !== 'admin' && $plan['user_id'] != $this->currentUserId) {
            $this->flashError('شما مجوز ویرایش این برنامه را ندارید.');
            $this->redirect('auditplans');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $this->flashError('عنوان برنامه ممیزی الزامی است.');
            $this->redirect('auditplans&action=edit&id=' . $id);
            return;
        }

        $startDate = $_POST['start_date'] ?? $plan['start_date'];
        $endDate = $_POST['end_date'] ?? $plan['end_date'];
        $departmentsJson = isset($_POST['departments']) && is_array($_POST['departments']) 
            ? json_encode($_POST['departments']) 
            : '[]';

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}audit_plans 
            SET audit_program_id = ?, title = ?, audit_type = ?, scope = ?, objectives = ?, criteria = ?, 
                start_date = ?, end_date = ?, lead_auditor_id = ?, departments = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            !empty($_POST['audit_program_id']) ? (int)$_POST['audit_program_id'] : null,
            $title,
            $_POST['audit_type'] ?? 'internal',
            $_POST['scope'] ?? '',
            $_POST['objectives'] ?? '',
            $_POST['criteria'] ?? 'ISO 9001:2015',
            $startDate,
            $endDate,
            $_POST['lead_auditor_id'] ?? null,
            $departmentsJson,
            $id
        ]);

        if ($result) {
            $this->logActivity('update_audit_plan', 'audit_plan', $id);
            $this->flashSuccess('برنامه ممیزی با موفقیت به‌روزرسانی شد.');
            $this->redirect('auditplans&action=show&id=' . $id);
        } else {
            $this->flashError('خطا در به‌روزرسانی برنامه ممیزی.');
            $this->redirect('auditplans&action=edit&id=' . $id);
        }
    }

    /**
     * حذف برنامه ممیزی
     */
    public function delete($id)
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        if ($_SESSION['user_role'] !== 'admin') {
            $this->flashError('فقط مدیران مجوز حذف برنامه ممیزی را دارند.');
            $this->redirect('auditplans');
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM {$this->prefix}audit_plans WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            $this->logActivity('delete_audit_plan', 'audit_plan', $id);
            $this->flashSuccess('برنامه ممیزی با موفقیت حذف شد.');
        } else {
            $this->flashError('خطا در حذف برنامه ممیزی.');
        }

        $this->redirect('auditplans');
    }

    /**
     * تغییر وضعیت برنامه ممیزی
     */
    public function changeStatus($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditplans');
            return;
        }

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['draft', 'scheduled', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            $this->flashError('وضعیت نامعتبر است.');
            $this->redirect('auditplans&action=show&id=' . $id);
            return;
        }

        $stmt = $this->db->prepare("UPDATE {$this->prefix}audit_plans SET status = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$newStatus, $id]);

        if ($result) {
            $this->logActivity('change_audit_plan_status', 'audit_plan', $id);
            $this->flashSuccess('وضعیت برنامه ممیزی تغییر کرد.');
        } else {
            $this->flashError('خطا در تغییر وضعیت.');
        }

        $this->redirect('auditplans&action=show&id=' . $id);
    }

    /** صفحه تیم ممیزی یک برنامه */
    public function team($id)
    {
        $this->requireAuth();
        $model = new \App\Software\Qms\Models\AuditorModel();

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        $this->view('audit-plans/team', [
            'pageTitle' => 'تیم ممیزی: ' . $plan['title'],
            'currentPage' => 'auditplans',
            'plan' => $plan,
            'team' => $model->getTeam($id),
            'auditors' => $model->activeAuditors()
        ]);
    }

    /** افزودن عضو به تیم ممیزی */
    public function addTeamMember($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditplans&action=team&id=' . $id);
            return;
        }

        $model = new \App\Software\Qms\Models\AuditorModel();
        $auditorId = (int)($_POST['auditor_id'] ?? 0);
        $role = $_POST['role'] ?? 'auditor';

        if (!$auditorId) {
            $this->flashError('لطفاً ممیز را انتخاب کنید.');
            $this->redirect('auditplans&action=team&id=' . $id);
            return;
        }

        // قاعده ISO 19011: فقط فرد دارای صلاحیت سرممیزی، سرممیز تیم می‌شود
        if ($role === 'lead_auditor') {
            $auditor = $model->find($auditorId);
            if (!$auditor || !$auditor['lead_auditor']) {
                $this->flashError('فقط ممیزانی که صلاحیت سرممیزی دارند می‌توانند سرممیز تیم شوند.');
                $this->redirect('auditplans&action=team&id=' . $id);
                return;
            }
            if ($model->hasLeadAuditor($id)) {
                $this->flashError('هر تیم ممیزی فقط یک سرممیز می‌تواند داشته باشد (ISO 19011).');
                $this->redirect('auditplans&action=team&id=' . $id);
                return;
            }
        }

        $model->addTeamMember($id, $auditorId, $role, trim($_POST['assigned_clauses'] ?? ''));
        $this->logActivity('assign_audit_team', 'audit_team', $id);
        $this->flashSuccess('عضو به تیم ممیزی اضافه شد.');
        $this->redirect('auditplans&action=team&id=' . $id);
    }

    /** حذف عضو از تیم ممیزی */
    public function removeTeamMember($id)
    {
        $this->requireAuth();
        $model = new \App\Software\Qms\Models\AuditorModel();

        $member = $model->findTeamMember($id);
        if ($member) {
            $model->removeTeamMember($id);
            $this->logActivity('remove_audit_team', 'audit_team', $member['audit_plan_id']);
            $this->flashSuccess('عضو از تیم ممیزی حذف شد.');
            $this->redirect('auditplans&action=team&id=' . $member['audit_plan_id']);
            return;
        }

        $this->flashError('رکورد یافت نشد.');
        $this->redirect('auditplans');
    }

    // ============================================
    // متدهای کمکی
    // ============================================

    private function getSessionsByPlan($planId)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, pi.department_id, pi.process_name, pi.audit_date,
                   d.name_fa as department_name, a.full_name as auditor_name
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            LEFT JOIN {$this->prefix}departments d ON pi.department_id = d.id
            LEFT JOIN {$this->prefix}auditors a ON pi.assigned_auditor_id = a.id
            WHERE pi.audit_plan_id = ?
            ORDER BY pi.item_number
        ");
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    private function getPlanStatistics($planId)
    {
        $stats = [];
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN overall_status = 'completed' THEN 1 ELSE 0 END) as completed,
                   SUM(CASE WHEN overall_status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
        ");
        $stmt->execute([$planId]);
        $stats['sessions'] = $stmt->fetch();
        
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN finding_type = 'conformity' THEN 1 ELSE 0 END) as conformities,
                   SUM(CASE WHEN finding_type = 'observation' THEN 1 ELSE 0 END) as observations,
                   SUM(CASE WHEN finding_type = 'minor_nc' THEN 1 ELSE 0 END) as minor_nc,
                   SUM(CASE WHEN finding_type = 'major_nc' THEN 1 ELSE 0 END) as major_nc
            FROM {$this->prefix}audit_evidences e
            JOIN {$this->prefix}audit_sessions s ON e.session_id = s.id
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            WHERE pi.audit_plan_id = ?
        ");
        $stmt->execute([$planId]);
        $stats['evidences'] = $stmt->fetch();
        
        return $stats;
    }
}