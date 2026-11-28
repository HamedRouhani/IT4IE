<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ProductRepository;
use App\Software\FactoryLayoutAnalyzer\Models\ProjectRepository;

final class ProductController extends Controller
{
    private const PERIODS = ['day' => 'روز', 'week' => 'هفته', 'month' => 'ماه', 'year' => 'سال'];

    public function create(int $projectId): void
    {
        $project = (new ProjectRepository())->findForUser($this->userId(), $projectId);
        if (!$project) {
            http_response_code(404);
            fl_flash_set('danger', 'پروژه پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($project, null);
    }

    public function store(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
        }
        try {
            (new ProductRepository())->create($userId, $projectId, $this->payload());
            fl_flash_set('success', 'محصول و مقدار تولید آن ثبت شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_product'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('product', 'create', ['project_id' => $projectId]));
        } catch (\Throwable $e) {
            error_log('Factory Layout product create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت محصول انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_product'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('product', 'create', ['project_id' => $projectId]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
    }

    public function edit(int $id): void
    {
        $product = (new ProductRepository())->findForUser($this->userId(), $id);
        if (!$product) {
            http_response_code(404);
            fl_flash_set('danger', 'محصول پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $project = (new ProjectRepository())->findForUser($this->userId(), (int) $product['project_id']);
        if (!$project) {
            http_response_code(404);
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($project, $product);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        $repository = new ProductRepository();
        $product = $repository->findForUser($userId, $id);
        if (!$product) {
            http_response_code(404);
            fl_flash_set('danger', 'محصول پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $projectId = (int) $product['project_id'];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('product', 'edit', ['id' => $id]));
        }
        try {
            $repository->update($userId, $id, $this->payload());
            fl_flash_set('success', 'مشخصات محصول به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_product'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('product', 'edit', ['id' => $id]));
        } catch (\Throwable $e) {
            error_log('Factory Layout product update failed: ' . $e->getMessage());
            fl_flash_set('danger', 'به‌روزرسانی محصول انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_product'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('product', 'edit', ['id' => $id]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        $repository = new ProductRepository();
        $product = $repository->findForUser($userId, $id);
        if (!$product) {
            http_response_code(404);
            fl_flash_set('danger', 'محصول پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $projectId = (int) $product['project_id'];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
        }
        try {
            $repository->delete($userId, $id);
            fl_flash_set('success', 'محصول از پروژه حذف شد.');
        } catch (\Throwable $e) {
            error_log('Factory Layout product delete failed: ' . $e->getMessage());
            fl_flash_set('danger', 'حذف محصول انجام نشد.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
    }

    /** @param array<string,mixed>|null $product */
    private function renderForm(array $project, ?array $product): void
    {
        $oldState = $_SESSION['fl_old_product'] ?? null;
        unset($_SESSION['fl_old_product']);
        $old = is_array($oldState) && (time() - (int) ($oldState['at'] ?? 0)) < 900 && is_array($oldState['data'] ?? null)
            ? $oldState['data']
            : [];
        $source = array_merge($product ?? [], $old);
        $this->renderSoftware('product/form', [
            'pageTitle' => $product ? 'ویرایش محصول' : 'ثبت محصول',
            'title' => ($product ? 'ویرایش محصول' : 'ثبت محصول') . ' - چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'project' => $project,
            'product' => $product,
            'source' => $source,
            'periodLabel' => self::PERIODS[$project['planning_period']] ?? 'نامشخص',
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    /** @return array{product_code:string,name:string,production_quantity:string,production_unit:string,notes:string} */
    private function payload(): array
    {
        $code = $this->text('product_code', 40, false);
        $name = $this->text('name', 180);
        $unit = $this->text('production_unit', 40);
        $notes = $this->text('notes', 1000, false);
        $quantity = fl_latin_digits(trim((string) ($_POST['production_quantity'] ?? '')));
        if (!preg_match('/^\d{1,10}(?:\.\d{1,4})?$/', $quantity)) {
            throw new \InvalidArgumentException('مقدار تولید باید عددی مثبت با حداکثر ۴ رقم اعشار باشد.');
        }
        [$whole, $fraction] = array_pad(explode('.', $quantity, 2), 2, '');
        if (trim($whole . $fraction, '0') === '') throw new \InvalidArgumentException('مقدار تولید باید بیشتر از صفر باشد.');
        $normalized = ltrim($whole, '0');
        $normalized = ($normalized === '' ? '0' : $normalized) . '.' . str_pad($fraction, 4, '0');
        return [
            'product_code' => $code,
            'name' => $name,
            'production_quantity' => $normalized,
            'production_unit' => $unit,
            'notes' => $notes,
        ];
    }

    private function text(string $field, int $maxLength, bool $required = true): string
    {
        $value = trim((string) ($_POST[$field] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > $maxLength) throw new \InvalidArgumentException('طول یکی از ورودی‌های متنی بیش از حد مجاز است.');
        if ($required && $value === '') throw new \InvalidArgumentException('نام محصول و واحد تولید الزامی هستند.');
        return $value;
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
