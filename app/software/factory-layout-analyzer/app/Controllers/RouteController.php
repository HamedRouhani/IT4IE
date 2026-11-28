<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ProductRepository;
use App\Software\FactoryLayoutAnalyzer\Models\RouteStepRepository;

final class RouteController extends Controller
{
    private const TYPES = [
        'operation' => 'عملیات',
        'inspection' => 'بازرسی',
        'transport' => 'حمل‌ونقل',
        'storage' => 'انبارش',
        'delay' => 'تأخیر',
    ];

    public function create(int $productId): void
    {
        $product = (new ProductRepository())->findForUser($this->userId(), $productId);
        if (!$product) {
            http_response_code(404);
            fl_flash_set('danger', 'محصول پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($product, null);
    }

    public function store(int $productId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $this->projectIdForProduct($userId, $productId), 'step' => 'routes']));
        }
        try {
            (new RouteStepRepository())->create($userId, $productId, $this->payload());
            fl_flash_set('success', 'گام مسیر به انتهای توالی محصول افزوده شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_route'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('route', 'create', ['product_id' => $productId]));
        } catch (\Throwable $e) {
            error_log('Factory Layout route step create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت گام مسیر انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_route'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('route', 'create', ['product_id' => $productId]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => $this->projectIdForProduct($userId, $productId), 'step' => 'routes']));
    }

    public function edit(int $id): void
    {
        $userId = $this->userId();
        $step = (new RouteStepRepository())->findForUser($userId, $id);
        if (!$step) {
            http_response_code(404);
            fl_flash_set('danger', 'گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $product = (new ProductRepository())->findForUser($userId, (int) $step['product_id']);
        if (!$product) {
            http_response_code(404);
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($product, $step);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        $repository = new RouteStepRepository();
        $step = $repository->findForUser($userId, $id);
        if (!$step) {
            http_response_code(404);
            fl_flash_set('danger', 'گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('route', 'edit', ['id' => $id]));
        }
        try {
            $repository->update($userId, $id, $this->payload());
            fl_flash_set('success', 'مشخصات گام مسیر به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_route'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('route', 'edit', ['id' => $id]));
        } catch (\Throwable $e) {
            error_log('Factory Layout route step update failed: ' . $e->getMessage());
            fl_flash_set('danger', 'به‌روزرسانی گام مسیر انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_route'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('route', 'edit', ['id' => $id]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => (int) $step['project_id'], 'step' => 'routes']));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        $repository = new RouteStepRepository();
        $step = $repository->findForUser($userId, $id);
        if (!$step) {
            http_response_code(404);
            fl_flash_set('danger', 'گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => (int) $step['project_id'], 'step' => 'routes']));
        }
        try {
            $projectId = $repository->delete($userId, $id);
            fl_flash_set('success', 'گام مسیر حذف و شمارهٔ گام‌های بعدی بازتنظیم شد.');
            $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'routes']));
        } catch (\Throwable $e) {
            error_log('Factory Layout route step delete failed: ' . $e->getMessage());
            fl_flash_set('danger', 'حذف گام مسیر انجام نشد.');
            $this->redirect(fl_url('project', 'show', ['id' => (int) $step['project_id'], 'step' => 'routes']));
        }
    }

    public function move(int $id): void
    {
        $userId = $this->userId();
        $repository = new RouteStepRepository();
        $step = $repository->findForUser($userId, $id);
        if (!$step) {
            http_response_code(404);
            fl_flash_set('danger', 'گام مسیر پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => (int) $step['project_id'], 'step' => 'routes']));
        }
        try {
            $projectId = $repository->move($userId, $id, (string) ($_POST['direction'] ?? ''));
            fl_flash_set('success', 'ترتیب مسیر به‌روزرسانی شد.');
            $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'routes']));
        } catch (\Throwable $e) {
            error_log('Factory Layout route step reorder failed: ' . $e->getMessage());
            fl_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'تغییر ترتیب مسیر انجام نشد.');
            $this->redirect(fl_url('project', 'show', ['id' => (int) $step['project_id'], 'step' => 'routes']));
        }
    }

    /** @param array<string,mixed>|null $step */
    private function renderForm(array $product, ?array $step): void
    {
        $oldState = $_SESSION['fl_old_route'] ?? null;
        unset($_SESSION['fl_old_route']);
        $old = is_array($oldState) && (time() - (int) ($oldState['at'] ?? 0)) < 900 && is_array($oldState['data'] ?? null)
            ? $oldState['data']
            : [];
        $source = array_merge($step ?? [], $old);
        $this->renderSoftware('route/form', [
            'pageTitle' => $step ? 'ویرایش گام مسیر' : 'افزودن گام مسیر',
            'title' => ($step ? 'ویرایش گام مسیر' : 'افزودن گام مسیر') . ' - چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'product' => $product,
            'step' => $step,
            'source' => $source,
            'types' => self::TYPES,
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    /** @return array{area_name:string,activity_type:string,description:string,notes:string} */
    private function payload(): array
    {
        $area = $this->text('area_name', 180);
        $type = (string) ($_POST['activity_type'] ?? '');
        if (!isset(self::TYPES[$type])) throw new \InvalidArgumentException('نوع فعالیت انتخاب‌شده معتبر نیست.');
        return [
            'area_name' => $area,
            'activity_type' => $type,
            'description' => $this->text('description', 500, false),
            'notes' => $this->text('notes', 1000, false),
        ];
    }

    private function text(string $field, int $maxLength, bool $required = true): string
    {
        $value = trim((string) ($_POST[$field] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > $maxLength) throw new \InvalidArgumentException('طول یکی از ورودی‌های متنی بیش از حد مجاز است.');
        if ($required && $value === '') throw new \InvalidArgumentException('نام بخش یا ایستگاه الزامی است.');
        return $value;
    }

    private function projectIdForProduct(int $userId, int $productId): int
    {
        $product = (new ProductRepository())->findForUser($userId, $productId);
        if (!$product) throw new \InvalidArgumentException('محصول پیدا نشد یا دسترسی به آن ندارید.');
        return (int) $product['project_id'];
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
