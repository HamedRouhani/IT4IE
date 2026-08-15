<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class AuditSessionController extends Controller
{
    /**
     * لیست جلسات ممیزی
     */
    public function index()
    {
        $this->requireAuth();
        
        $sessions = $this->db->query("
            SELECT s.*, pi.audit_plan_id, pi.department_id, pi.process_name, pi.audit_date,
                   ap.title as plan_title, d.name_fa as department_name, 
                   a.full_name as auditor_name
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            JOIN {$this->prefix}audit_plans ap ON pi.audit_plan_id = ap.id
            LEFT JOIN {$this->prefix}departments d ON pi.department_id = d.id
            LEFT JOIN {$this->prefix}auditors a ON pi.assigned_auditor_id = a.id
            ORDER BY s.actual_date DESC
        ")->fetchAll();

        $this->view('audit-sessions/index', [
            'pageTitle' => 'جلسات ممیزی',
            'currentPage' => 'auditsessions',
            'sessions' => $sessions
        ]);
    }

    /**
     * مشاهده جزئیات جلسه ممیزی
     */
    public function show($id)
    {
        $this->requireAuth();
        
        $session = $this->db->prepare("
            SELECT s.*, pi.audit_plan_id, pi.department_id, pi.process_name, pi.audit_date,
                   pi.start_time, pi.end_time, pi.assigned_auditor_id,
                   ap.title as plan_title, d.name_fa as department_name, 
                   a.full_name as auditor_name
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            JOIN {$this->prefix}audit_plans ap ON pi.audit_plan_id = ap.id
            LEFT JOIN {$this->prefix}departments d ON pi.department_id = d.id
            LEFT JOIN {$this->prefix}auditors a ON pi.assigned_auditor_id = a.id
            WHERE s.id = ?
        ");
        $session->execute([$id]);
        $session = $session->fetch();

        if (!$session) {
            $this->flashError('جلسه ممیزی یافت نشد.');
            $this->redirect('auditsessions');
            return;
        }

        // دریافت شواهد
        $evidences = $this->db->prepare("
            SELECT e.*, c.clause_number, c.title_fa as clause_title
            FROM {$this->prefix}audit_evidences e
            LEFT JOIN {$this->prefix}iso_clauses c ON e.clause_id = c.id
            WHERE e.session_id = ?
            ORDER BY e.created_at DESC
        ");
        $evidences->execute([$id]);
        $evidences = $evidences->fetchAll();

        // دریافت ممیزی‌شوندگان
        $auditees = $this->db->prepare("
            SELECT aa.*, u.name as user_name
            FROM {$this->prefix}audit_auditees aa
            LEFT JOIN users u ON aa.user_id = u.id
            WHERE aa.plan_item_id = ?
        ");
        $auditees->execute([$session['plan_item_id']]);
        $auditees = $auditees->fetchAll();

        // دریافت تمام بندهای استاندارد برای انتخاب در فرم ثبت شاهد
        $clauses = $this->db->query("
            SELECT id, clause_number, title_fa, level 
            FROM {$this->prefix}iso_clauses 
            WHERE is_active = 1 AND clause_type = 'requirement'
            ORDER BY sort_order
        ")->fetchAll();

        // آمار جلسه
        $stats = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN finding_type = 'conformity' THEN 1 ELSE 0 END) as conformities,
                SUM(CASE WHEN finding_type = 'observation' THEN 1 ELSE 0 END) as observations,
                SUM(CASE WHEN finding_type = 'minor_nc' THEN 1 ELSE 0 END) as minor_nc,
                SUM(CASE WHEN finding_type = 'major_nc' THEN 1 ELSE 0 END) as major_nc,
                SUM(CASE WHEN finding_type = 'ofI' THEN 1 ELSE 0 END) as opportunities
            FROM {$this->prefix}audit_evidences WHERE session_id = ?
        ");
        $stats->execute([$id]);
        $stats = $stats->fetch();

        $this->view('audit-sessions/show', [
            'pageTitle' => 'جلسه ممیزی: ' . ($session['department_name'] ?? ''),
            'currentPage' => 'auditsessions',
            'session' => $session,
            'evidences' => $evidences,
            'auditees' => $auditees,
            'clauses' => $clauses,
            'stats' => $stats
        ]);
    }

    /**
     * ثبت شاهد جدید
     */
    public function addEvidence()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditsessions');
            return;
        }

        $sessionId = $_POST['session_id'] ?? null;
        
        if (!$sessionId) {
            $this->flashError('شناسه جلسه نامعتبر است.');
            $this->redirect('auditsessions');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $clauseId = $_POST['clause_id'] ?? null;

        if (empty($title) || empty($description) || empty($clauseId)) {
            $this->flashError('عنوان، توضیحات و انتخاب بند استاندارد الزامی است.');
            $this->redirect('auditsessions&action=show&id=' . $sessionId);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_evidences 
            (session_id, clause_id, evidence_type, title, description, finding_type, 
             severity, is_confidential, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $sessionId,
            $clauseId,
            $_POST['evidence_type'] ?? 'observation',
            $title,
            $description,
            $_POST['finding_type'] ?? 'conformity',
            $_POST['severity'] ?? 'low',
            isset($_POST['is_confidential']) ? 1 : 0,
            $_POST['notes'] ?? ''
        ]);

        if ($result) {
            $evidenceId = $this->db->lastInsertId();
            $this->logActivity('add_evidence', 'audit_evidence', $evidenceId);
            $this->flashSuccess('شاهد با موفقیت ثبت شد.');
        } else {
            $this->flashError('خطا در ثبت شاهد.');
        }

        $this->redirect('auditsessions&action=show&id=' . $sessionId);
    }

    /**
     * افزودن ممیزی‌شونده به جلسه
     */
    public function addAuditee()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditsessions');
            return;
        }

        $planItemId = $_POST['plan_item_id'] ?? null;
        $fullName = trim($_POST['full_name'] ?? '');

        if (!$planItemId || empty($fullName)) {
            $this->flashError('اطلاعات ناقص است.');
            $this->redirect('auditsessions');
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_auditees 
            (plan_item_id, full_name, position, department_id, contact_info, is_key_contact, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $planItemId,
            $fullName,
            $_POST['position'] ?? null,
            $_POST['department_id'] ?? null,
            $_POST['contact_info'] ?? null,
            isset($_POST['is_key_contact']) ? 1 : 0
        ]);

        if ($result) {
            $this->flashSuccess('ممیزی‌شونده با موفقیت اضافه شد.');
        } else {
            $this->flashError('خطا در افزودن ممیزی‌شونده.');
        }

        $this->redirect('auditsessions&action=show&id=' . ($_POST['session_id'] ?? 0));
    }

    /**
     * به‌روزرسانی وضعیت جلسه
     */
    public function updateStatus()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditsessions');
            return;
        }

        $sessionId = $_POST['session_id'] ?? null;
        $newStatus = $_POST['overall_status'] ?? null;

        if (!$sessionId || !$newStatus) {
            $this->flashError('اطلاعات نامعتبر است.');
            $this->redirect('auditsessions');
            return;
        }

        $validStatuses = ['not_started', 'in_progress', 'completed', 'postponed'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->flashError('وضعیت نامعتبر است.');
            $this->redirect('auditsessions');
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}audit_sessions 
            SET overall_status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$newStatus, $sessionId]);

        if ($result) {
            $this->logActivity('update_session_status', 'audit_session', $sessionId);
            $this->flashSuccess('وضعیت جلسه به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی وضعیت.');
        }

        $this->redirect('auditsessions&action=show&id=' . $sessionId);
    }

    /**
     * نمایش فرم ایجاد جلسه جدید
     */
    public function create()
    {
        $this->requireAuth();
        
        $planId = $_GET['plan_id'] ?? null;
        
        if (!$planId) {
            $this->flashError('شناسه برنامه ممیزی مشخص نشده است.');
            $this->redirect('auditplans');
            return;
        }
        
        // دریافت اطلاعات برنامه
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_plans WHERE id = ?");
        $stmt->execute([$planId]);
        $plan = $stmt->fetch();
        
        if (!$plan) {
            $this->flashError('برنامه ممیزی یافت نشد.');
            $this->redirect('auditplans');
            return;
        }
        
        // دریافت واحدهای انتخاب شده در برنامه
        $departments = json_decode($plan['departments'] ?? '[]', true);
        $deptList = [];
        if (!empty($departments)) {
            $placeholders = implode(',', array_fill(0, count($departments), '?'));
            $stmt = $this->db->prepare("SELECT id, name_fa FROM {$this->prefix}departments WHERE id IN ($placeholders)");
            $stmt->execute($departments);
            $deptList = $stmt->fetchAll();
        }
        
        // دریافت ممیزان
        $auditors = $this->db->query("SELECT id, full_name FROM {$this->prefix}auditors WHERE is_active = 1 ORDER BY full_name")->fetchAll();
        
        $this->view('audit-sessions/create', [
            'pageTitle' => 'ایجاد جلسه ممیزی',
            'currentPage' => 'auditsessions',
            'plan' => $plan,
            'departments' => $deptList,
            'auditors' => $auditors
        ]);
    }

    /**
     * ذخیره جلسه جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditsessions');
            return;
        }

        $planId = $_POST['audit_plan_id'] ?? null;
        $departmentId = $_POST['department_id'] ?? null;
        $auditDate = $_POST['audit_date'] ?? null;
        
        if (!$planId || !$departmentId || !$auditDate) {
            $this->flashError('اطلاعات لازم کامل نیست.');
            $this->redirect('auditsessions&action=create&plan_id=' . $planId);
            return;
        }

        // تبدیل اعداد فارسی به انگلیسی
        $auditDate = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $auditDate);
        $auditDate = str_replace('/', '-', $auditDate);

        try {
            $this->db->beginTransaction();
            
            // دریافت شماره آیتم
            $stmtNum = $this->db->prepare("SELECT COUNT(*) as cnt FROM {$this->prefix}audit_plan_items WHERE audit_plan_id = ?");
            $stmtNum->execute([$planId]);
            $itemNumber = ($stmtNum->fetch()['cnt'] ?? 0) + 1;
            
            // ایجاد Plan Item
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}audit_plan_items 
                (audit_plan_id, item_number, department_id, audit_date, assigned_auditor_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([
                $planId,
                $itemNumber,
                $departmentId,
                $auditDate,
                $_POST['assigned_auditor_id'] ?? null
            ]);
            $planItemId = $this->db->lastInsertId();
            
            // ایجاد Session
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}audit_sessions 
                (plan_item_id, session_number, actual_date, overall_status, created_at)
                VALUES (?, ?, ?, 'not_started', NOW())
            ");
            $stmt->execute([$planItemId, $itemNumber, $auditDate]);
            $sessionId = $this->db->lastInsertId();
            
            $this->db->commit();
            
            $this->logActivity('create_audit_session', 'audit_session', $sessionId);
            $this->flashSuccess('جلسه ممیزی با موفقیت ایجاد شد.');
            $this->redirect('auditsessions&action=show&id=' . $sessionId);
            
        } catch (\Exception $e) {
            $this->db->rollback();
            $this->flashError('خطا در ایجاد جلسه: ' . $e->getMessage());
            $this->redirect('auditsessions&action=create&plan_id=' . $planId);
        }
    }

    /**
     * نمایش فرم ویرایش جلسه
     */
    public function edit($id)
    {
        $this->requireAuth();
        
        $stmt = $this->db->prepare("
            SELECT s.*, pi.audit_plan_id, pi.department_id, pi.assigned_auditor_id, pi.audit_date,
                   ap.title as plan_title, d.name_fa as department_name, a.full_name as auditor_name
            FROM {$this->prefix}audit_sessions s
            JOIN {$this->prefix}audit_plan_items pi ON s.plan_item_id = pi.id
            JOIN {$this->prefix}audit_plans ap ON pi.audit_plan_id = ap.id
            LEFT JOIN {$this->prefix}departments d ON pi.department_id = d.id
            LEFT JOIN {$this->prefix}auditors a ON pi.assigned_auditor_id = a.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        
        if (!$session) {
            $this->flashError('جلسه یافت نشد.');
            $this->redirect('auditsessions');
            return;
        }
        
        $auditors = $this->db->query("SELECT id, full_name FROM {$this->prefix}auditors WHERE is_active = 1 ORDER BY full_name")->fetchAll();
        
        $this->view('audit-sessions/edit', [
            'pageTitle' => 'ویرایش جلسه ممیزی',
            'currentPage' => 'auditsessions',
            'session' => $session,
            'auditors' => $auditors
        ]);
    }

    /**
     * به‌روزرسانی جلسه
     */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditsessions');
            return;
        }

        $auditDate = $_POST['audit_date'] ?? null;
        $actualDate = $_POST['actual_date'] ?? null;
        
        if (!$auditDate) {
            $this->flashError('تاریخ ممیزی الزامی است.');
            $this->redirect('auditsessions&action=edit&id=' . $id);
            return;
        }

        // تبدیل اعداد فارسی به انگلیسی
        $auditDate = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $auditDate);
        $auditDate = str_replace('/', '-', $auditDate);
        
        if ($actualDate) {
            $actualDate = str_replace(['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $actualDate);
            $actualDate = str_replace('/', '-', $actualDate);
        }

        try {
            // به‌روزرسانی session
            $stmt = $this->db->prepare("
                UPDATE {$this->prefix}audit_sessions 
                SET actual_date = ?, overall_status = ?, auditor_notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $actualDate ?: null,
                $_POST['overall_status'] ?? 'not_started',
                $_POST['auditor_notes'] ?? '',
                $id
            ]);
            
            // به‌روزرسانی plan item
            $stmt = $this->db->prepare("
                UPDATE {$this->prefix}audit_plan_items 
                SET audit_date = ?, assigned_auditor_id = ?, updated_at = NOW()
                WHERE id = (SELECT plan_item_id FROM {$this->prefix}audit_sessions WHERE id = ?)
            ");
            $stmt->execute([
                $auditDate,
                $_POST['assigned_auditor_id'] ?? null,
                $id
            ]);
            
            $this->logActivity('update_audit_session', 'audit_session', $id);
            $this->flashSuccess('جلسه با موفقیت به‌روزرسانی شد.');
            $this->redirect('auditsessions&action=show&id=' . $id);
            
        } catch (\Exception $e) {
            $this->flashError('خطا در به‌روزرسانی جلسه: ' . $e->getMessage());
            $this->redirect('auditsessions&action=edit&id=' . $id);
        }
    }

    /**
     * حذف جلسه
     */
    public function delete($id)
    {
        $this->requireAuth();
        
        if ($_SESSION['user_role'] !== 'admin') {
            $this->flashError('فقط مدیران می‌توانند جلسه حذف کنند.');
            $this->redirect('auditsessions');
            return;
        }
        
        $stmt = $this->db->prepare("SELECT plan_item_id FROM {$this->prefix}audit_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        
        if ($session) {
            // حذف session
            $stmt = $this->db->prepare("DELETE FROM {$this->prefix}audit_sessions WHERE id = ?");
            $stmt->execute([$id]);
            
            // حذف plan item
            $stmt = $this->db->prepare("DELETE FROM {$this->prefix}audit_plan_items WHERE id = ?");
            $stmt->execute([$session['plan_item_id']]);
            
            $this->logActivity('delete_audit_session', 'audit_session', $id);
            $this->flashSuccess('جلسه با موفقیت حذف شد.');
        } else {
            $this->flashError('جلسه یافت نشد.');
        }
        
        $this->redirect('auditsessions');
    }
}