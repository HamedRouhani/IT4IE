<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Services\InventoryPolicyService;

final class AnalysisController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $model = new InventoryItem();
        $items = $model->all((int) $workspace['id']);
        $estimates = $model->demandEstimates((int) $workspace['id']);
        foreach ($items as &$item) $item['_demand_estimate'] = $estimates[(int) $item['id']] ?? null;
        unset($item);
        $items = InventoryPolicyService::classifyAbc($items);
        $this->renderPage('analysis/index', [
            'pageTitle' => 'تحلیل و سفارش موجودی',
            'items' => $items,
            'serviceLevels' => InventoryPolicyService::serviceLevels(),
        ]);
    }

    public function estimate(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $model = new InventoryItem();
            if (!$model->find((int) $workspace['id'], $id)) throw new \InvalidArgumentException('قلم موجودی پیدا نشد یا بایگانی شده است.');
            $estimate = $model->demandEstimates((int) $workspace['id'])[$id] ?? null;
            if (!$estimate) throw new \InvalidArgumentException('برای برآورد، دست‌کم ۳۰ روز سابقه و خروج ثبت‌شده لازم است.');
            if (!$model->updateDemandParameters((int) $workspace['id'], $id, $estimate['annual_demand'], $estimate['daily_demand_std'])) {
                throw new \RuntimeException('ذخیرهٔ پارامترهای تقاضا انجام نشد.');
            }
            inv_flash_set('success', 'تقاضای سالانه و انحراف معیار بر اساس گردش خروج ثبت‌شده به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            inv_flash_set('warning', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Inventory demand estimate failed: ' . $e->getMessage());
            inv_flash_set('danger', 'برآورد تقاضا انجام نشد.');
        }
        $this->redirect(inv_url('analysis'));
    }
}
