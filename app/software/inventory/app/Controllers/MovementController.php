<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Models\InventoryMovement;

final class MovementController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $this->renderPage('movement/index', [
            'pageTitle' => 'گردش موجودی',
            'items' => (new InventoryItem())->all((int) $workspace['id']),
            'movements' => (new InventoryMovement())->latest((int) $workspace['id'], 50),
        ]);
    }

    public function store(): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);
            $type = (string) ($_POST['movement_type'] ?? '');
            $raw = trim((string) ($_POST['quantity'] ?? ''));
            $raw = str_replace([',', '٬', '٫'], ['', '', '.'], $raw);
            if (!$itemId || $raw === '' || !is_numeric($raw)) throw new \InvalidArgumentException('قلم و مقدار گردش را به‌درستی وارد کنید.');
            $quantity = (float) $raw;
            (new InventoryMovement())->record(
                (int) $workspace['id'], (int) $itemId, (int) $_SESSION['user_id'], $type, $quantity,
                trim((string) ($_POST['note'] ?? '')) ?: null,
                (string) ($_POST['adjustment_direction'] ?? 'increase')
            );
            inv_flash_set('success', 'گردش موجودی ثبت شد و مانده به‌روزرسانی شد.');
            $this->redirect(inv_url('movement'));
        } catch (\InvalidArgumentException $e) {
            inv_flash_set('danger', $e->getMessage());
            $this->redirect(inv_url('movement'));
        } catch (\Throwable $e) {
            error_log('Inventory movement failed: ' . $e->getMessage());
            inv_flash_set('danger', 'گردش ثبت نشد؛ قلم را بررسی کنید و دوباره تلاش کنید.');
            $this->redirect(inv_url('movement'));
        }
    }
}
