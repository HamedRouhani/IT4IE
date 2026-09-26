<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;

class SystemController extends Controller
{
    /**
     * بررسی درخواست AJAX
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * دریافت id سیستم فعال کاربر
     */
    protected function currentSystemId(): ?int
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $model = new System();
        $system = $model->findActiveByUser($userId);
        return $system ? (int) $system['id'] : null;
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $model = new System();
        $systems = $model->findByUser($userId);

        $this->renderSoftware('system/index', [
            'title'   => 'سیستم‌های کیفیت',
            'systems' => $systems,
        ], 'quality');
    }

    public function create(): void
    {
        $this->requireAuth();

        $this->renderSoftware('system/form', [
            'title'  => 'ایجاد سیستم کیفیت',
            'system' => null,
            'action' => 'store',
        ], 'quality');
    }

    public function store(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
        }

        $companyName = trim($_POST['company_name'] ?? '');
        $industry    = trim($_POST['industry']     ?? '');
        $companySize = $_POST['company_size']      ?? 'medium';
        $description = trim($_POST['description']  ?? '');

        if ($companyName === '') {
            $_SESSION['flash_error'] = 'نام شرکت الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
        }

        $allowedSizes = ['micro', 'small', 'medium', 'large', 'enterprise'];
        if (!in_array($companySize, $allowedSizes, true)) {
            $companySize = 'medium';
        }

        $model = new System();
        $systemId = $model->createForUser($userId, [
            'company_name' => $companyName,
            'industry'     => $industry !== '' ? $industry : null,
            'company_size' => $companySize,
            'description'  => $description !== '' ? $description : null,
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success'   => true,
                'system_id' => $systemId,
                'redirect'  => CURRENT_MODULE_URL,
            ]);
        }

        $_SESSION['flash_success'] = 'سیستم کیفیت با موفقیت ایجاد شد';
        $this->redirect(CURRENT_MODULE_URL);
    }

    public function edit($id): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $id = (int) $id;

        $model  = new System();
        $system = $model->find($id);

        if (!$system || (int) $system['user_id'] !== $userId) {
            http_response_code(404);
            echo 'سیستم یافت نشد';
            return;
        }

        $this->renderSoftware('system/form', [
            'title'  => 'ویرایش سیستم کیفیت',
            'system' => $system,
            'action' => 'update',
        ], 'quality');
    }

    public function update($id): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $id = (int) $id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=edit&id=' . $id);
        }

        $model  = new System();
        $system = $model->find($id);

        if (!$system || (int) $system['user_id'] !== $userId) {
            http_response_code(404);
            echo 'سیستم یافت نشد';
            return;
        }

        $companyName = trim($_POST['company_name'] ?? '');
        if ($companyName === '') {
            $_SESSION['flash_error'] = 'نام شرکت الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=edit&id=' . $id);
        }

        $companySize = $_POST['company_size'] ?? 'medium';
        $allowedSizes = ['micro', 'small', 'medium', 'large', 'enterprise'];
        if (!in_array($companySize, $allowedSizes, true)) {
            $companySize = 'medium';
        }

        $model->update($id, [
            'company_name' => $companyName,
            'industry'     => trim($_POST['industry'] ?? '') !== '' ? trim($_POST['industry']) : null,
            'company_size' => $companySize,
            'description'  => trim($_POST['description'] ?? '') !== '' ? trim($_POST['description']) : null,
        ]);

        $_SESSION['flash_success'] = 'سیستم با موفقیت به‌روزرسانی شد';
        $this->redirect(CURRENT_MODULE_URL);
    }

    public function archive($id): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $id = (int) $id;

        $model  = new System();
        $system = $model->find($id);

        if (!$system || (int) $system['user_id'] !== $userId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->archive($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'سیستم آرشیو شد';
        $this->redirect(CURRENT_MODULE_URL);
    }
}