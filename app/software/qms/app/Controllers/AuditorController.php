<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class AuditorController extends Controller
{
    /**
     * نمایش فرم ایجاد ممیز جدید
     */
    public function create()
    {
        $this->requireAuth();
        $this->view('auditors/create', [
            'pageTitle' => 'ثبت ممیز جدید',
            'currentPage' => 'auditors'
        ]);
    }

    /**
     * ذخیره ممیز جدید در دیتابیس
     */
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
            (user_id, full_name, email, phone, qualification, lead_auditor, iso_9001_certified, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $result = $stmt->execute([
            $this->currentUserId,
            $fullName,
            trim($_POST['email'] ?? ''),
            trim($_POST['phone'] ?? ''),
            trim($_POST['qualification'] ?? ''),
            isset($_POST['lead_auditor']) ? 1 : 0,
            isset($_POST['iso_9001_certified']) ? 1 : 0
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

    /**
     * لیست ممیزان
     */
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
}