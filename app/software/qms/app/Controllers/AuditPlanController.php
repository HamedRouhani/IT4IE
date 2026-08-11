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
        $this->requireAuth();

        $departments = $this->db->query("SELECT id, name_fa FROM {$this->prefix}departments WHERE is_active = 1 ORDER BY name_fa")->fetchAll();
        $auditors = $this->db->query("SELECT id, full_name FROM {$this->prefix}auditors WHERE is_active = 1 ORDER BY full_name")->fetchAll();

        $this->view('audit-plans/create', [
            'pageTitle' => 'ایجاد برنامه ممیزی جدید',
            'currentPage' => 'auditplans',
            'departments' => $departments,
            'auditors' => $auditors
        ]);
    }

    /**
     * ذخیره برنامه ممیزی جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditplans');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            $this->flashError('عنوان برنامه ممیزی الزامی است.');
            $this->redirect('auditplans&action=create');
            return;
        }

        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        
        if (strtotime($endDate) < strtotime($startDate)) {
            $this->flashError('تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.');
            $this->redirect('auditplans&action=create');
            return;
        }

        $leadAuditorId = $_POST['lead_auditor_id'] ?? null;
        if (empty($leadAuditorId)) {
            $this->flashError('انتخاب سرممیز الزامی است.');
            $this->redirect('auditplans&action=create');
            return;
        }

        $departmentsJson = isset($_POST['departments']) && is_array($_POST['departments']) 
            ? json_encode($_POST['departments']) 
            : '[]';

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_plans 
            (user_id, title, audit_type, scope, objectives, criteria, start_date, end_date, lead_auditor_id, departments, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())
        ");
        
        $stmt->execute([
            $this->currentUserId,
            $title,
            $_POST['audit_type'] ?? 'internal',
            $_POST['scope'] ?? '',
            $_POST['objectives'] ?? '',
            $_POST['criteria'] ?? 'ISO 9001:2015',
            $startDate,
            $endDate,
            $leadAuditorId,
            $departmentsJson
        ]);

        $newId = $this->db->lastInsertId();
        $this->logActivity('create_audit_plan', 'audit_plan', $newId);
        
        $this->flashSuccess('برنامه ممیزی با موفقیت ایجاد شد.');
        $this->redirect('auditplans&action=show&id=' . $newId);
    }

    /**
     * مشاهده جزئیات برنامه ممیزی
     * ✅ نام متد از view به show تغییر یافت تا با متد render پدر تداخل نداشته باشد
     */
    public function show($id)
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("
            SELECT ap.*, a.full_name as lead_auditor_name 
            FROM {$this->prefix}audit_plans ap
            LEFT JOIN {$this->prefix}auditors a ON ap.lead_auditor_id = a.id
            WHERE ap.id = ?
        ");
        $stmt->execute([$id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }

        // بررسی دسترسی (فقط مدیر یا کاربر مرتبط)
        if ($_SESSION['user_role'] !== 'admin' && $plan['user_id'] != $this->currentUserId) {
            $this->flashError('شما مجوز دسترسی به این برنامه را ندارید.');
            $this->redirect('auditplans');
            return;
        }

        $sessions = $this->getSessionsByPlan($id);
        $statistics = $this->getPlanStatistics($id);

        // نام فایل ویو همان view.php باقی می‌ماند، فقط نام اکشن show است
        $this->view('audit-plans/view', [
            'pageTitle' => $plan['title'],
            'currentPage' => 'auditplans',
            'plan' => $plan,
            'sessions' => $sessions,
            'statistics' => $statistics
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
            SET title = ?, audit_type = ?, scope = ?, objectives = ?, criteria = ?, 
                start_date = ?, end_date = ?, lead_auditor_id = ?, departments = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
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