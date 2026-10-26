<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Services\InventoryPolicyService;

final class AnalysisController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $items = (new InventoryItem())->all((int) $workspace['id']);
        $items = InventoryPolicyService::classifyAbc($items);
        $this->renderPage('analysis/index', [
            'pageTitle' => 'تحلیل و سفارش موجودی',
            'items' => $items,
            'serviceLevels' => InventoryPolicyService::serviceLevels(),
        ]);
    }
}
