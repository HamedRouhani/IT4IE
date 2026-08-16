<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class AuditorController extends Controller
{
    /** نمایش فرم ایجاد ممیز جدید */
    public function create()
    {
        $this->requireAuth();
        $this->view('auditors/create', [
            'pageTitle' => 'ثبت ممیز جدید',
            'currentPage' => 'auditors'
        ]);
    }

    /** ذخیره ممیز جدید */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditors');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if (empty($fullName)) {
            $this->flashError('نام و نام خانوادگی ممیز الزامی است.');
            $this->redirect('auditors&action=create');
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}auditors 
            (user_id, full_name, email, phone, qualification, lead_auditor, 
             iso_9001_certified, iso_19011_certified, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $result = $stmt->execute([
            $this->currentUserId,
            $fullName,
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['qualification'] ?? ''),
            isset($_POST['lead_auditor']) ? 1 : 0,
            isset($_POST['iso_9001_certified']) ? 1 : 0,
            isset($_POST['iso_19011_certified']) ? 1 : 0
        ]);

        if ($result) {
            $this->logActivity('create_auditor', 'auditor', $this->db->lastInsertId());
            $this->flashSuccess('ممیز با موفقیت ثبت شد.');
            $this->redirect('auditors');
        } else {
            $this->flashError('خطا در ثبت اطلاعات ممیز.');
            $this->redirect('auditors&action=create');
        }
    }

    /** لیست ممیزان */
    public function index()
    {
        $this->requireAuth();
        
        $auditors = $this->db->query("
            SELECT a.*, u.name as user_name,
                   (SELECT COUNT(*) FROM {$this->prefix}audit_plans WHERE lead_auditor_id = a.id) as lead_count,
                   (SELECT COUNT(*) FROM {$this->prefix}audit_plan_items WHERE assigned_auditor_id = a.id) as item_count
            FROM {$this->prefix}auditors a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.is_active = 1
            ORDER BY a.lead_auditor DESC, a.full_name
        ")->fetchAll();

        $this->view('auditors/index', [
            'pageTitle' => 'ممیزان',
            'currentPage' => 'auditors',
            'auditors' => $auditors
        ]);
    }

    /** فرم ویرایش پروفایل ممیز / سرممیز */
    public function edit($id)
    {
        $this->requireAuth();

        $stmt = $this->db->prepare("SELECT * FROM {$this->prefix}auditors WHERE id = ?");
        $stmt->execute([$id]);
        $auditor = $stmt->fetch();

        if (!$auditor) {
            $this->flashError('ممیز یافت نشد.');
            $this->redirect('auditors');
            return;
        }

        $this->view('auditors/edit', [
            'pageTitle' => 'ویرایش پروفایل ممیز',
            'currentPage' => 'auditors',
            'auditor' => $auditor
        ]);
    }

    /** به‌روزرسانی پروفایل ممیز / سرممیز */
    public function update($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditors');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if (empty($fullName)) {
            $this->flashError('نام و نام خانوادگی ممیز الزامی است.');
            $this->redirect('auditors&action=edit&id=' . $id);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}auditors SET
                full_name = ?, email = ?, phone = ?, qualification = ?,
                lead_auditor = ?, iso_9001_certified = ?, iso_19011_certified = ?,
                other_certifications = ?, experience_years = ?, audit_count = ?,
                specialization = ?, is_active = ?, notes = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $result = $stmt->execute([
            $fullName,
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['qualification'] ?? ''),
            isset($_POST['lead_auditor']) ? 1 : 0,
            isset($_POST['iso_9001_certified']) ? 1 : 0,
            isset($_POST['iso_19011_certified']) ? 1 : 0,
            trim($_POST['other_certifications'] ?? ''),
            (int)($_POST['experience_years'] ?? 0),
            (int)($_POST['audit_count'] ?? 0),
            trim($_POST['specialization'] ?? ''),
            isset($_POST['is_active']) ? 1 : 0,
            trim($_POST['notes'] ?? ''),
            $id
        ]);

        if ($result) {
            $this->logActivity('update_auditor', 'auditor', $id);
            $this->flashSuccess('پروفایل ممیز با موفقیت به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی اطلاعات ممیز.');
        }

        $this->redirect('auditors');
    }
}