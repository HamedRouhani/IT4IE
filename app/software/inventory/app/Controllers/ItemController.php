<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Services\InventoryPolicyService;

final class ItemController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $items = (new InventoryItem())->all((int) $workspace['id']);
        $this->renderPage('item/index', [
            'pageTitle' => 'اقلام موجودی',
            'items' => InventoryPolicyService::classifyAbc($items),
        ]);
    }

    public function create(): void
    {
        $this->workspace();
        $this->renderPage('item/form', ['pageTitle' => 'افزودن قلم موجودی', 'item' => null]);
    }

    public function store(): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $model = new InventoryItem();
            $data = $this->itemPayload();
            if ($model->skuExists((int) $workspace['id'], $data['sku'])) throw new \InvalidArgumentException('این کد در همین فضای کاری قبلاً ثبت شده است.');
            $model->create((int) $workspace['id'], $data, (int) $_SESSION['user_id']);
            inv_flash_set('success', 'قلم موجودی ثبت شد و موجودی اولیه در دفتر گردش ثبت گردید.');
            $this->redirect(inv_url('item'));
        } catch (\InvalidArgumentException $e) {
            inv_flash_set('danger', $e->getMessage());
            $this->redirect(inv_url('item', 'create'));
        } catch (\Throwable $e) {
            error_log('Inventory item create failed: ' . $e->getMessage());
            inv_flash_set('danger', 'ثبت قلم انجام نشد؛ دوباره تلاش کنید.');
            $this->redirect(inv_url('item', 'create'));
        }
    }

    public function edit(int $id): void
    {
        $workspace = $this->workspace();
        $item = (new InventoryItem())->find((int) $workspace['id'], $id);
        if (!$item) {
            http_response_code(404);
            inv_flash_set('danger', 'قلم موجودی پیدا نشد.');
            $this->redirect(inv_url('item'));
        }
        $this->renderPage('item/form', ['pageTitle' => 'ویرایش قلم موجودی', 'item' => $item]);
    }

    public function update(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $model = new InventoryItem();
            $item = $model->find((int) $workspace['id'], $id);
            if (!$item) throw new \InvalidArgumentException('قلم موجودی پیدا نشد.');
            $data = $this->itemPayload();
            if ($model->skuExists((int) $workspace['id'], $data['sku'], $id)) throw new \InvalidArgumentException('این کد برای قلم دیگری ثبت شده است.');
            $model->update((int) $workspace['id'], $id, $data);
            inv_flash_set('success', 'مشخصات قلم موجودی به‌روزرسانی شد.');
            $this->redirect(inv_url('item'));
        } catch (\InvalidArgumentException $e) {
            inv_flash_set('danger', $e->getMessage());
            $this->redirect(inv_url('item', 'edit', ['id' => $id]));
        } catch (\Throwable $e) {
            error_log('Inventory item update failed: ' . $e->getMessage());
            inv_flash_set('danger', 'به‌روزرسانی قلم انجام نشد؛ دوباره تلاش کنید.');
            $this->redirect(inv_url('item', 'edit', ['id' => $id]));
        }
    }

    public function delete(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        $archived = (new InventoryItem())->archive((int) $workspace['id'], $id);
        inv_flash_set($archived ? 'success' : 'danger', $archived ? 'قلم از فهرست فعال خارج شد؛ سابقهٔ گردش آن حفظ شده است.' : 'قلم پیدا نشد.');
        $this->redirect(inv_url('item'));
    }
}
