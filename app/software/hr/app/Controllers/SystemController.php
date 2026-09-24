<?php
namespace App\Software\Hr\Controllers;

use App\Core\Controller;
use App\Software\Hr\Models\System;

/**
 * ============================================================
 * SystemController - مدیریت اطلاعات شرکت HR
 * ============================================================
 * مسیر: app/software/hr/app/Controllers/SystemController.php
 * 
 * نکته: هر کاربر سایت فقط یک شرکت دارد (One-to-One).
 * ============================================================
 */
class SystemController extends Controller
{
    /**
     * صفحه اصلی - نمایش یا ویرایش
     */
    public function index()
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if (!$system) {
            $this->redirect(hr_url('system', 'create'));
            return;
        }

        $this->redirect(hr_url('system', 'edit'));
    }

    /**
     * فرم ایجاد
     */
    public function create()
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if ($system) {
            $this->redirect(hr_url('system', 'edit'));
            return;
        }

        $this->renderSoftware('system/create', [
            'pageTitle'    => 'ایجاد سیستم شرکت',
            'softwareName' => 'HR Analyzer',
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * ذخیره
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('system'));
            return;
        }

        $this->verifyCsrf();
        $userId = (int) $_SESSION['user_id'];

        $companyName = trim($_POST['company_name'] ?? '');

        if (empty($companyName)) {
            hr_flash_set('danger', 'نام شرکت الزامی است.');
            $this->redirect(hr_url('system', 'create'));
            return;
        }

        $systemModel = new System();

        if ($systemModel->findByUser($userId)) {
            hr_flash_set('warning', 'شما از قبل یک شرکت ثبت کرده‌اید.');
            $this->redirect(hr_url('system', 'edit'));
            return;
        }

        $newId = $systemModel->createForUser($userId, [
            'company_name'      => $companyName,
            'industry'          => trim($_POST['industry'] ?? 'general'),
            'company_size'      => trim($_POST['company_size'] ?? 'medium'),
            'fiscal_year_start' => trim($_POST['fiscal_year_start'] ?? '01/01'),
            'address'           => trim($_POST['address'] ?? '') ?: null,
            'phone'             => trim($_POST['phone'] ?? '') ?: null,
            'email'             => trim($_POST['email'] ?? '') ?: null,
            'website'           => trim($_POST['website'] ?? '') ?: null,
            'description'       => trim($_POST['description'] ?? '') ?: null,
            'status'            => 'active',
        ]);

        $_SESSION['hr_active_system'] = $newId;
        hr_flash_set('success', 'سیستم شرکت با موفقیت ایجاد شد.');

        $this->redirect(hr_url('dashboard'));
    }

    /**
     * فرم ویرایش
     */
    public function edit($id = null)
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if (!$system) {
            $this->redirect(hr_url('system', 'create'));
            return;
        }

        $this->renderSoftware('system/edit', [
            'pageTitle'    => 'ویرایش اطلاعات شرکت',
            'softwareName' => 'HR Analyzer',
            'system'       => $system,
            'flash'        => hr_flash_get(),
        ], 'hr');
    }

    /**
     * به‌روزرسانی
     */
    public function update($id = null)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(hr_url('system'));
            return;
        }

        $this->verifyCsrf();
        $userId = (int) $_SESSION['user_id'];

        $companyName = trim($_POST['company_name'] ?? '');

        if (empty($companyName)) {
            hr_flash_set('danger', 'نام شرکت الزامی است.');
            $this->redirect(hr_url('system', 'edit'));
            return;
        }

        $systemModel = new System();
        $systemModel->updateForUser($userId, [
            'company_name'      => $companyName,
            'industry'          => trim($_POST['industry'] ?? 'general'),
            'company_size'      => trim($_POST['company_size'] ?? 'medium'),
            'fiscal_year_start' => trim($_POST['fiscal_year_start'] ?? '01/01'),
            'address'           => trim($_POST['address'] ?? '') ?: null,
            'phone'             => trim($_POST['phone'] ?? '') ?: null,
            'email'             => trim($_POST['email'] ?? '') ?: null,
            'website'           => trim($_POST['website'] ?? '') ?: null,
            'description'       => trim($_POST['description'] ?? '') ?: null,
        ]);

        hr_flash_set('success', 'اطلاعات شرکت با موفقیت به‌روزرسانی شد.');
        $this->redirect(hr_url('system', 'edit'));
    }
}