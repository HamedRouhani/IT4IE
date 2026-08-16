<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

/**
 * برنامه سالانه ممیزی - ISO 9001:2015 بند 9.2.2
 */
class AuditProgramController extends Controller
{
    /** لیست برنامه‌ها */
    public function index()
    {
        $this->requireAuth();

        $programs = $this->db->query("
            SELECT p.*,
                (SELECT COUNT(*) FROM {$this->prefix}process_risk_assessment ra WHERE ra.program_id = p.id) AS risk_count
            FROM {$this->prefix}audit_programs p
            ORDER BY p.year DESC, p.id DESC
        ")->fetchAll();

        $this->view('audit-programs/index', [
            'pageTitle'   => 'برنامه‌های سالانه ممیزی',
            'currentPage' => 'auditprograms',
            'programs'    => $programs,
        ]);
    }

    /** فرم ایجاد */
    public function create()
    {
        $this->requireAuth();
        $this->view('audit-programs/create', [
            'pageTitle'    => 'ایجاد برنامه سالانه',
            'currentPage'  => 'auditprograms',
            'currentJYear' => fa_current_jyear(),
            'jYears'       => range(fa_current_jyear() - 1, fa_current_jyear() + 3),
        ]);
    }

    /** ذخیره برنامه جدید */
    public function store()
    {
        $this->requireAuth();

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->flashError('عنوان برنامه الزامی است.');
            $this->redirect('auditprograms&action=create');
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}audit_programs
            (user_id, title, year, description, objectives, scope, criteria, frequency_method, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())
        ");
        $stmt->execute([
            $this->currentUserId,
            $title,
            (int)($_POST['year'] ?? fa_current_jyear()),
            trim($_POST['description'] ?? ''),
            trim($_POST['objectives'] ?? ''),
            trim($_POST['scope'] ?? ''),
            trim($_POST['criteria'] ?? '') ?: 'ISO 9001:2015',
            $_POST['frequency_method'] ?? 'risk_based',
        ]);

        $id = $this->db->lastInsertId();
        $this->logActivity('create_audit_program', 'audit_program', $id);
        $this->flashSuccess('برنامه ایجاد شد؛ اکنون ریسک واحدها را ارزیابی کنید.');
        $this->redirect('auditprograms&action=riskAssessment&id=' . $id);
    }

    /** جزئیات برنامه */
    public function show($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $program = $this->getProgram($id);

        if (!$program) { $this->flashError('برنامه یافت نشد.'); $this->redirect('auditprograms'); return; }

        $stmt = $this->db->prepare("
            SELECT ra.*, d.name_fa AS department_name
            FROM {$this->prefix}process_risk_assessment ra
            LEFT JOIN {$this->prefix}departments d ON d.id = ra.department_id
            WHERE ra.program_id = ? ORDER BY ra.risk_score DESC
        ");
        $stmt->execute([$id]);

        $this->view('audit-programs/view', [
            'pageTitle'       => $program['title'],
            'currentPage'     => 'auditprograms',
            'program'         => $program,
            'riskAssessments' => $stmt->fetchAll(),
        ]);
    }

    /** فرم ویرایش */
    public function edit($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $program = $this->getProgram($id);

        if (!$program) { $this->flashError('برنامه یافت نشد.'); $this->redirect('auditprograms'); return; }

        $this->view('audit-programs/edit', [
            'pageTitle'   => 'ویرایش برنامه',
            'currentPage' => 'auditprograms',
            'program'     => $program,
            'jYears'      => range(fa_current_jyear() - 2, fa_current_jyear() + 3),
        ]);
    }

    /** ذخیره ویرایش */
    public function update($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_POST['id'] ?? 0);

        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $this->flashError('عنوان برنامه الزامی است.');
            $this->redirect('auditprograms&action=edit&id=' . $id);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}audit_programs
            SET title=?, year=?, description=?, objectives=?, scope=?, criteria=?,
                frequency_method=?, status=?, updated_at=NOW()
            WHERE id=?
        ");
        $stmt->execute([
            $title,
            (int)($_POST['year'] ?? fa_current_jyear()),
            trim($_POST['description'] ?? ''),
            trim($_POST['objectives'] ?? ''),
            trim($_POST['scope'] ?? ''),
            trim($_POST['criteria'] ?? '') ?: 'ISO 9001:2015',
            $_POST['frequency_method'] ?? 'risk_based',
            $_POST['status'] ?? 'draft',
            $id,
        ]);

        $this->logActivity('update_audit_program', 'audit_program', $id);
        $this->flashSuccess('برنامه به‌روزرسانی شد.');
        $this->redirect('auditprograms&action=show&id=' . $id);
    }

    /** حذف برنامه */
    public function delete($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_POST['id'] ?? $_GET['id'] ?? 0);

        $this->db->prepare("DELETE FROM {$this->prefix}process_risk_assessment WHERE program_id = ?")->execute([$id]);
        $this->db->prepare("DELETE FROM {$this->prefix}audit_programs WHERE id = ?")->execute([$id]);

        $this->logActivity('delete_audit_program', 'audit_program', $id);
        $this->flashSuccess('برنامه حذف شد.');
        $this->redirect('auditprograms');
    }

    /** صفحه ارزیابی ریسک */
    public function riskAssessment($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $program = $this->getProgram($id);

        if (!$program) { $this->flashError('برنامه یافت نشد.'); $this->redirect('auditprograms'); return; }

        $departments = $this->db->query("
            SELECT id, name_fa FROM {$this->prefix}departments
            WHERE is_active = 1 ORDER BY sort_order, name_fa
        ")->fetchAll();

        $stmt = $this->db->prepare("
            SELECT ra.*, d.name_fa AS department_name
            FROM {$this->prefix}process_risk_assessment ra
            LEFT JOIN {$this->prefix}departments d ON d.id = ra.department_id
            WHERE ra.program_id = ? ORDER BY ra.risk_score DESC
        ");
        $stmt->execute([$id]);

        $this->view('audit-programs/risk-assessment', [
            'pageTitle'       => 'ارزیابی ریسک - ' . $program['title'],
            'currentPage'     => 'auditprograms',
            'program'         => $program,
            'departments'     => $departments,
            'riskAssessments' => $stmt->fetchAll(),
        ]);
    }

    /** ذخیره ارزیابی ریسک */
    public function saveRiskAssessment()
    {
        $this->requireAuth();

        $programId    = (int)($_POST['program_id'] ?? 0);
        $departmentId = (int)($_POST['department_id'] ?? 0);

        if (!$programId || !$departmentId) {
            $this->flashError('لطفاً واحد سازمانی را انتخاب کنید.');
            $this->redirect('auditprograms&action=riskAssessment&id=' . $programId);
            return;
        }

        // تاریخ شمسی ممیزی قبلی
        $prevDate = toMysqlDate($_POST['previous_audit_date'] ?? '');

        // محاسبه امتیاز و سطح ریسک
        $prob = ['very_low'=>1,'low'=>2,'medium'=>3,'high'=>4,'very_high'=>5];
        $imp  = ['very_low'=>1,'low'=>2,'medium'=>3,'high'=>4,'very_high'=>5];
        $mult = ['low'=>0.8,'medium'=>1.0,'high'=>1.2,'critical'=>1.5];
        $bonus= ['conformity'=>-2,'ofI'=>-1,'observation'=>0,'minor_nc'=>2,'major_nc'=>4];

        $score = (int)max(1, min(25, round(
            ($prob[$_POST['risk_probability'] ?? 'medium'] ?? 3) *
            ($imp[$_POST['risk_impact'] ?? 'medium'] ?? 3) *
            ($mult[$_POST['importance'] ?? 'medium'] ?? 1.0) +
            ($bonus[$_POST['previous_audit_result'] ?? ''] ?? 0)
        )));

        $level = $score <= 4 ? 'low' : ($score <= 9 ? 'medium' : ($score <= 16 ? 'high' : 'critical'));
        $freq  = ['critical'=>'quarterly','high'=>'semi_annual','medium'=>'annual','low'=>'biennial'][$level];

        $chk = $this->db->prepare("SELECT id FROM {$this->prefix}process_risk_assessment WHERE program_id=? AND department_id=?");
        $chk->execute([$programId, $departmentId]);
        $existing = $chk->fetch();

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE {$this->prefix}process_risk_assessment SET
                    process_name=?, importance=?, previous_audit_date=?, previous_audit_result=?,
                    changes_since_last_audit=?, risk_probability=?, risk_impact=?, risk_score=?,
                    risk_level=?, recommended_frequency=?, recommended_priority=?, notes=?,
                    assessed_by=?, assessed_at=NOW(), updated_at=NOW()
                WHERE id=?
            ");
            $stmt->execute([
                trim($_POST['process_name'] ?? ''), $_POST['importance'] ?? 'medium', $prevDate,
                !empty($_POST['previous_audit_result']) ? $_POST['previous_audit_result'] : null,
                trim($_POST['changes_since_last_audit'] ?? ''),
                $_POST['risk_probability'] ?? 'medium', $_POST['risk_impact'] ?? 'medium',
                $score, $level, $freq, $level, trim($_POST['notes'] ?? ''),
                $this->currentUserId, $existing['id'],
            ]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->prefix}process_risk_assessment
                (program_id, department_id, process_name, importance, previous_audit_date, previous_audit_result,
                 changes_since_last_audit, risk_probability, risk_impact, risk_score, risk_level,
                 recommended_frequency, recommended_priority, notes, assessed_by, assessed_at, created_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
            ");
            $stmt->execute([
                $programId, $departmentId,
                trim($_POST['process_name'] ?? ''), $_POST['importance'] ?? 'medium', $prevDate,
                !empty($_POST['previous_audit_result']) ? $_POST['previous_audit_result'] : null,
                trim($_POST['changes_since_last_audit'] ?? ''),
                $_POST['risk_probability'] ?? 'medium', $_POST['risk_impact'] ?? 'medium',
                $score, $level, $freq, $level, trim($_POST['notes'] ?? ''),
                $this->currentUserId, date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('save_risk_assessment', 'process_risk_assessment', $programId);
        $this->flashSuccess('ارزیابی ریسک ذخیره شد.');
        $this->redirect('auditprograms&action=riskAssessment&id=' . $programId);
    }

    /** حذف یک ارزیابی */
    public function deleteRiskAssessment($id = null)
    {
        $this->requireAuth();
        $id = (int)($id ?? $_GET['id'] ?? 0);

        $chk = $this->db->prepare("SELECT program_id FROM {$this->prefix}process_risk_assessment WHERE id=?");
        $chk->execute([$id]);
        $row = $chk->fetch();

        $this->db->prepare("DELETE FROM {$this->prefix}process_risk_assessment WHERE id=?")->execute([$id]);

        $this->flashSuccess('ارزیابی حذف شد.');
        $this->redirect('auditprograms&action=riskAssessment&id=' . ($row['program_id'] ?? 0));
    }

    /** متد کمکی */
    private function getProgram($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}audit_programs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}