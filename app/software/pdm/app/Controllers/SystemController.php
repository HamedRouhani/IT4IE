<?php
namespace App\Software\Pdm\Controllers;

use App\Core\Controller;
use App\Software\Pdm\Models\System;

/**
 * ============================================================
 * SystemController - مدیریت اطلاعات شرکت (One-to-One با کاربر)
 * ============================================================
 * مسیر: app/software/pdm/app/Controllers/SystemController.php
 * 
 * نکته: هر کاربر سایت فقط یک شرکت دارد.
 * - اگر ندارد: فرم ایجاد نمایش داده می‌شود.
 * - اگر دارد: فرم ویرایش نمایش داده می‌شود.
 * ============================================================
 */
class SystemController extends Controller
{
    /**
     * صفحه اصلی - نمایش یا ویرایش سیستم شرکت
     */
    public function index()
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if ($system) {
            // اگر سیستم دارد، به صفحه ویرایش هدایت شود
            $this->redirect(pdm_url('system', 'edit'));
            return;
        }

        // اگر ندارد، فرم ایجاد
        $this->renderSoftware('system/create', [
            'pageTitle'  => 'ایجاد سیستم شرکت',
            'softwareName' => 'PdM Analyzer',
        ], 'pdm');
    }

    /**
     * فرم ایجاد سیستم جدید
     */
    public function create()
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if ($system) {
            // اگر از قبل دارد، به ویرایش هدایت شود
            $this->redirect(pdm_url('system', 'edit'));
            return;
        }

        $this->renderSoftware('system/create', [
            'pageTitle'    => 'ایجاد سیستم شرکت',
            'softwareName' => 'PdM Analyzer',
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * ذخیره سیستم جدید
     */
    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('system'));
            return;
        }

        $userId = (int) $_SESSION['user_id'];

        // بررسی CSRF
        $this->verifyCsrf();

        $companyName = trim($_POST['company_name'] ?? '');
        $industry    = trim($_POST['industry'] ?? 'general');
        $description = trim($_POST['description'] ?? '');

        if (empty($companyName)) {
            pdm_flash_set('danger', 'نام شرکت الزامی است.');
            $this->redirect(pdm_url('system', 'create'));
            return;
        }

        $systemModel = new System();

        // بررسی مجدد که کاربر قبلاً سیستم نداشته باشد
        if ($systemModel->findByUser($userId)) {
            pdm_flash_set('warning', 'شما از قبل یک شرکت ثبت کرده‌اید.');
            $this->redirect(pdm_url('system', 'edit'));
            return;
        }

        $newId = $systemModel->createForUser($userId, [
            'company_name' => $companyName,
            'industry'     => $industry,
            'description'  => $description,
            'status'       => 'active',
        ]);

        $_SESSION['pdm_active_system'] = $newId;
        pdm_flash_set('success', 'سیستم شرکت با موفقیت ایجاد شد.');

        $this->redirect(pdm_url('dashboard'));
    }

    /**
     * فرم ویرایش سیستم
     */
    public function edit($id = null)
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $system = $systemModel->findByUser($userId);

        if (!$system) {
            // اگر سیستم ندارد، به ایجاد هدایت شود
            $this->redirect(pdm_url('system', 'create'));
            return;
        }

        $this->renderSoftware('system/edit', [
            'pageTitle'    => 'ویرایش اطلاعات شرکت',
            'softwareName' => 'PdM Analyzer',
            'system'       => $system,
            'flash'        => pdm_flash_get(),
        ], 'pdm');
    }

    /**
     * به‌روزرسانی اطلاعات سیستم
     */
    public function update($id = null)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(pdm_url('system'));
            return;
        }

        $userId = (int) $_SESSION['user_id'];

        // بررسی CSRF
        $this->verifyCsrf();

        $companyName = trim($_POST['company_name'] ?? '');
        $industry    = trim($_POST['industry'] ?? 'general');
        $description = trim($_POST['description'] ?? '');

        if (empty($companyName)) {
            pdm_flash_set('danger', 'نام شرکت الزامی است.');
            $this->redirect(pdm_url('system', 'edit'));
            return;
        }

        $systemModel = new System();
        $systemModel->updateForUser($userId, [
            'company_name' => $companyName,
            'industry'     => $industry,
            'description'  => $description,
        ]);

        pdm_flash_set('success', 'اطلاعات شرکت با موفقیت به‌روزرسانی شد.');
        $this->redirect(pdm_url('system', 'edit'));
    }

    /**
     * حذف سیستم (غیرفعال‌سازی)
     */
    public function delete($id = null)
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        $systemModel = new System();
        $systemModel->updateForUser($userId, ['status' => 'inactive']);

        if (isset($_SESSION['pdm_active_system'])) {
            unset($_SESSION['pdm_active_system']);
        }

        pdm_flash_set('success', 'سیستم شرکت غیرفعال شد.');
        $this->redirect('/software');
    }
}